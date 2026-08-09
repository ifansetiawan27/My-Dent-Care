# Authentication Flowcharts

This derived artifact visualizes the Accepted Authentication architecture. It introduces no endpoint, business rule, lifecycle transition, security policy, or governance decision.

Authorities:

- `DD-AUTH-003` for the self-service Session and Device boundary.
- `DD-AUTH-007` and `ADR-005` for lifecycle, audit, retention, archive, Legal Hold, cleanup, and Secret destruction.
- `DD-AUTH-010` for deterministic Login History ordering.
- `DD-AUTH-017` for field classification, exposure, nullability, and field governance; it supersedes DD-AUTH-005.
- `DD-AUTH-018` for the credential-change projection exception.
- `ADR-006` for canonical Audit Event and Login History projection authority; it supersedes ADR-004.
- `API.md`, OpenAPI, `Flow.md`, and `SequenceDiagram.md` for the existing Authentication contract and flow.

Login History is an Operational History Projection. It is never canonical Audit Evidence. Canonical Audit Events remain separate, `Append Only`, and `Immutable`.

## Shared Lifecycle Boundary

Every state-changing flow below uses this boundary:

```mermaid
flowchart TD
    BT[Business Transaction] --> MT[Minimum Lifecycle Transition]
    MT --> AE[Emit Immutable Audit Event]
    AE --> EM[Record cleanup eligibility metadata]
    EM --> CM[Commit Business Transaction]
    CM -. asynchronous processing .-> BG[Asynchronous Background Lifecycle Processing]
    BG --> RT[Evaluate Retention Policy]
    RT --> LH{Legal Hold applies?}
    LH -- Yes --> DF[Defer destructive processing and preserve evidence]
    LH -- No --> AR{Archive required?}
    AR -- Yes --> AC[Archive eligible non-Secret evidence]
    AR -- No --> CL[Continue cleanup eligibility]
    AC --> CL
    CL --> SD[Destroy eligible Secret material irreversibly]
    SD --> PE[Preserve detached Immutable Audit Evidence]
```

Background retention, archive, cleanup, purge, Secret destruction, and Legal Hold evaluation are asynchronous, idempotent, retry-safe, resumable, and bounded. Normal business transactions do not wait for them.

## Login, Failure, And Lockout

```mermaid
flowchart TD
    L0[POST auth login] --> L1[Validate approved request]
    L1 --> L2[Check distributed lockout state]
    L2 -->|Locked| LF0[Reject with generic locked response]
    L2 -->|Eligible| L3[Resolve User and verify credential]
    L3 -->|Invalid| LF1[Increment distributed failure count atomically]
    LF1 --> LF2{Approved threshold reached and not exempt?}
    LF2 -->|Yes| LF3[Apply temporary distributed lockout]
    LF2 -->|No| LF4[Retain current lockout state]
    LF3 --> LFP[Create failed Login History projection]
    LF4 --> LFP
    LF0 --> LFP
    LFP --> LFA[Emit immutable failure or lockout Audit Event]
    LFA --> LFC[Commit failure transaction]
    LFC -. asynchronous .-> BG1[Background lifecycle processing]

    L3 -->|Valid| L4[Resolve active Organization, Branch, membership, and Device]
    L4 -->|Invalid context| LF1
    L4 -->|Valid context| LS[Create active User Session]
    LS --> LAT[Issue Access Token]
    LAT --> LRT[Issue Refresh Token in Session family]
    LRT --> LHP[Create successful Login History projection]
    LHP --> LCL[Clear distributed failure state]
    LCL --> LAE[Emit immutable login Audit Event]
    LAE --> LC[Commit login transaction]
    LC --> LR[Return approved token contract]
    LC -. asynchronous .-> BG2[Background lifecycle processing]
```

## Refresh Rotation And Reuse Detection

```mermaid
flowchart TD
    R0[POST auth refresh] --> R1[Validate Refresh Token input]
    R1 --> R2[Resolve token family and active Session boundary]
    R2 --> R3{Token valid, unexpired, and not revoked?}
    R3 -->|Yes| R4[Atomically revoke predecessor Refresh Token and previous Access Token]
    R4 --> R5[Set replacement lineage once]
    R5 --> R6[Issue replacement Access and Refresh Token pair]
    R6 --> R7[Emit immutable rotation Audit Event]
    R7 --> R8[Commit rotation transaction]
    R8 --> R9[Return replacement pair]
    R8 -. asynchronous .-> RBG[Background lifecycle processing]

    R3 -->|Rotated token reused| RU1[Revoke entire Refresh Token family]
    RU1 --> RU2[Revoke owning Session and descendant Access Token]
    RU2 --> RU3[Emit immutable reuse and revocation Audit Event]
    RU3 --> RU4[Commit revocation transaction]
    RU4 --> RU5[Return approved generic failure]
    RU4 -. asynchronous .-> RUBG[Background lifecycle processing]

    R3 -->|Expired or revoked| RX1[Reject without issuing authority]
    RX1 --> RX2[Emit immutable failed refresh evidence when required]
    RX2 --> RX3[Commit failure transaction]
    RX3 -. asynchronous .-> RXBG[Background lifecycle processing]
```

## Logout Current And Logout All

```mermaid
flowchart TD
    O0{Approved self-service operation}
    O0 -->|POST auth logout| O1[Revoke current Session]
    O1 --> O2[Revoke descendant Access Token and Refresh Token family]
    O2 --> O3[Set linked Login History logout_at once when applicable]
    O3 --> O4[Emit immutable logout Audit Event]
    O4 --> O5[Commit current-session logout]
    O5 --> O6[Return success]
    O5 -. asynchronous .-> OBG[Background lifecycle processing]

    O0 -->|POST auth logout-all| OA1[Revoke every active Session owned by current User]
    OA1 --> OA2[Revoke every descendant Access Token and Refresh Token family]
    OA2 --> OA3[Retain Login History projections without deletion]
    OA3 --> OA4[Emit immutable logout-all Audit Event]
    OA4 --> OA5[Commit User-wide revocation]
    OA5 --> OA6[Return success]
    OA5 -. asynchronous .-> OABG[Background lifecycle processing]
```

## Password Flows

```mermaid
flowchart TD
    P0{Password operation}

    P0 -->|POST auth forgot-password| F1[Accept email without account enumeration]
    F1 --> F2[Create approved single-use hashed reset material when account exists]
    F2 --> F3[Emit immutable operational evidence without Secret data]
    F3 --> F4[Commit forgot-password transaction]
    F4 --> F5[Return generic accepted response]
    F4 -. asynchronous .-> FBG[Background lifecycle processing]

    P0 -->|POST auth reset-password| RP1[Validate email, reset token, and password policy]
    RP1 --> RP2[Consume valid single-use reset token]
    RP2 --> RP3[Update credential]
    RP3 --> RP4[Revoke all User Sessions and descendant token families]
    RP4 --> RP5[Retain registered Devices]
    RP5 --> RP6[Emit immutable credential-recovery Audit Event]
    RP6 --> RP7[Commit reset transaction]
    RP7 --> RP8[Require new login]
    RP7 -. asynchronous .-> RPBG[Background lifecycle processing]

    P0 -->|POST auth change-password| CP1[Validate current credential and new password policy]
    CP1 --> CP2[Update credential]
    CP2 --> CP3[Preserve current Session, Access Token, and Refresh Token family]
    CP3 --> CP4[Revoke every other Session and descendant token family]
    CP4 --> CP5[Do not mutate Login History]
    CP5 --> CP6[Emit immutable PASSWORD_CHANGED Audit Event]
    CP6 --> CP6B[Emit applicable immutable SESSION_REVOKED Audit Events]
    CP6B --> CP7[Commit change transaction]
    CP7 --> CP8[Return success]
    CP7 -. asynchronous .-> CPBG[Background lifecycle processing]
```

## Device Listing And Revocation

All Device operations are self-service, including for Super Admin. Cross-user administration is outside Authentication under DD-AUTH-003.

```mermaid
flowchart TD
    D0{Device operation}
    D0 -->|GET auth devices| DL1[Validate bounded pagination, sort, and filters]
    DL1 --> DL2[Scope Devices to authenticated User]
    DL2 --> DL3[Derive is_active from revoked_at IS NULL]
    DL3 --> DL4[Exclude Persistence Only and Sensitive Device fields]
    DL4 --> DL5[Return stable Device projection]

    D0 -->|DELETE auth devices deviceId| DR1[Validate Device identifier]
    DR1 --> DR2[Verify authenticated User ownership]
    DR2 --> DR3{Current Device?}
    DR3 -->|Yes| DR4[Reject and require current-session logout flow]
    DR3 -->|No| DR5[Revoke Device once]
    DR5 --> DR6[Revoke descendant Sessions]
    DR6 --> DR7[Revoke descendant Access and Refresh Token authority]
    DR7 --> DR8[Retain Login History projections and canonical Audit Events]
    DR8 --> DR9[Emit immutable DEVICE_REVOKED Audit Event]
    DR9 --> DR10[Commit Device revocation]
    DR10 --> DR11[Return success]
    DR10 -. asynchronous .-> DBG[Background lifecycle processing]
```

## Profile Retrieval And Update

```mermaid
flowchart TD
    P0{Profile operation}
    P0 -->|GET auth profile| PG1[Resolve authenticated User and active tenant context]
    PG1 --> PG2[Load approved User, Organization, Branch, role, and permission projection]
    PG2 --> PG3[Exclude non-public and Secret fields]
    PG3 --> PG4[Return stable Profile response]

    P0 -->|PUT auth profile| PU1[Validate Name, Phone, and optional binary Photo]
    PU1 --> PU2[Verify self-service ownership]
    PU2 --> PU3{Photo supplied?}
    PU3 -->|Yes| PU4[Validate and store Photo through FileStorage authority]
    PU3 -->|No| PU5[Preserve existing Photo]
    PU4 --> PU6[Apply approved Profile field updates]
    PU5 --> PU6
    PU6 --> PU7[Emit immutable Profile update Audit Event]
    PU7 --> PU8[Commit Profile transaction]
    PU8 --> PU9[Return updated Profile response]
    PU8 -. asynchronous .-> PUB[Background lifecycle processing]
```

## Login History Projection And Audit Separation

```mermaid
flowchart TD
    H0{Login History activity}
    H0 -->|Authentication attempt| H1[Create Operational History Projection]
    H1 --> H2[Persist immutable projection fields]
    H2 --> H3[Emit separate Append Only and Immutable Audit Event]
    H3 --> H4[Commit business transaction]
    H4 -. asynchronous .-> HBG[Background lifecycle processing]

    H0 -->|Approved logout or Session revocation except credential change| H5{logout_at is NULL?}
    H5 -->|Yes| H6[Controlled One-Time Mutation: set logout_at]
    H5 -->|No| H7[Preserve existing logout_at]
    H6 --> H8[Emit separate immutable lifecycle Audit Event]
    H7 --> H8
    H8 --> H9[Commit lifecycle transaction]
    H9 -. asynchronous .-> HBG2[Background lifecycle processing]

    H0 -->|GET auth login-history| H10[Validate pagination and filters]
    H10 --> H11[Scope to current User and active tenant]
    H11 --> H12[Order by login_at DESC, id DESC]
    H12 --> H13[Apply Sensitive self-service IP boundary]
    H13 --> H14[Return stable nullable projection]

    H0 -->|Credential-change revocation under DD-AUTH-018| H15[Preserve Login History and logout_at unchanged]
    H15 --> H16[Emit immutable PASSWORD_CHANGED and SESSION_REVOKED evidence]

    H1 -. not canonical evidence .-> SEP[Operational History Projection]
    H3 -. canonical evidence .-> AUD[Append Only and Immutable Audit Events]
    SEP -. remains separate from .-> AUD
```

## Background Retention, Archive, Cleanup, And Destruction

```mermaid
flowchart TD
    B0[Committed business transaction records lifecycle eligibility] -. asynchronous .-> B1[Start bounded background lifecycle processing]
    B1 --> B2[Load effective retention policy and ownership scope]
    B2 --> B3{Legal Hold applies to existing evidence?}
    B3 -->|Yes| B4[Preserve held non-Secret evidence and defer purge]
    B4 --> B5[Emit immutable deferred-outcome evidence]
    B3 -->|No| B6{Archive required?}
    B6 -->|Yes| B7[Archive eligible non-Secret evidence]
    B6 -->|No| B8[Continue cleanup evaluation]
    B7 --> B8
    B8 --> B9{Hard Deletable and retention eligible?}
    B9 -->|No| B10[Retain according to policy]
    B9 -->|Yes| B11[Irreversibly purge or destroy eligible Secret material]
    B11 --> B12[Preserve detached immutable evidence without Secret material]
    B12 --> B13[Emit immutable completion evidence]
    B10 --> B14[Complete or reschedule safely]
    B5 --> B14
    B13 --> B14
```

Legal Hold preserves existing evidence; it never restores destroyed Secret material. Canonical Audit Events remain authoritative and are never replaced by Login History projections or archive-derived operational copies.
