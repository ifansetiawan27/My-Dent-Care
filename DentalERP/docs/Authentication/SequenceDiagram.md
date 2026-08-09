# Authentication Lifecycle Sequence Diagrams

These diagrams synchronize the accepted Authentication behavior with `DD-AUTH-007`, `ADR-005`, `ADR-006`, ADR-001 through ADR-003, and Accepted `DD-AUTH-017`/`DD-AUTH-018`. ADR-004 and DD-AUTH-005 remain superseded historical evidence.

Every Session and Device sequence is self-service, including for Super Admin. Cross-user administration is outside Authentication under Accepted `DD-AUTH-003`.

## Login And Token Issuance

```mermaid
sequenceDiagram
    participant C as Client
    participant A as Authentication Service
    participant D as Device Lifecycle
    participant S as Session Lifecycle
    participant T as Token Lifecycle
    participant H as Login History Projection
    participant U as Audit Platform

    C->>A: Submit approved authentication request
    A->>D: Resolve or register Device state
    A->>S: Create active Session
    A->>T: Issue Access and Refresh Token pair
    A->>H: Append login projection record
    A->>U: Append immutable login evidence
    A->>A: Commit login transaction
    A-->>C: Return approved stable contract
```

## Login Failure And Distributed Lockout

```mermaid
sequenceDiagram
    participant C as Client
    participant A as Authentication Service
    participant L as Distributed Lockout
    participant H as Login History Projection
    participant U as Audit Platform

    C->>A: Submit invalid credential or tenant context
    A->>L: Increment failed attempt atomically
    alt Approved threshold reached and User not exempt
        L->>L: Apply temporary lockout
    else Threshold not reached
        L->>L: Preserve current counter and TTL
    end
    A->>H: Append failed Login History projection
    A->>U: Append immutable failure or lockout evidence
    A->>A: Commit failure transaction
    A-->>C: Return generic approved error contract
```

## Refresh Rotation

```mermaid
sequenceDiagram
    participant C as Client
    participant A as Authentication Service
    participant S as Session Lifecycle
    participant T as Token Lifecycle
    participant U as Audit Platform

    C->>A: Present Refresh Token
    A->>S: Validate active Session boundary
    A->>T: Validate token family and reuse state
    alt Valid active Refresh Token
        T->>T: Revoke predecessor Refresh Token and previous Access Token atomically
        T->>T: Issue one replacement pair in same family
        A->>U: Append immutable rotation evidence
        A->>A: Commit rotation transaction
        A-->>C: Return replacement pair
    else Rotated token reuse detected
        T->>T: Revoke entire Refresh Token family
        T->>S: Revoke owning Session and descendant Access Token
        A->>U: Append immutable reuse and revocation evidence
        A->>A: Commit reuse-revocation transaction
        A-->>C: Return approved generic failure without replacement
    end
```

## Session And Device Revocation

```mermaid
sequenceDiagram
    participant C as Authorized Actor
    participant A as Authentication Service
    participant D as Device Lifecycle
    participant S as Session Lifecycle
    participant T as Token Lifecycle
    participant H as Login History Projection
    participant U as Audit Platform

    C->>A: Request approved revocation scope
    alt Current Session
        A->>S: Revoke current Session
    else User-wide scope
        A->>S: Revoke all targeted User Sessions
    else Device scope
        A->>D: Revoke Device
        D->>S: Revoke descendant Sessions
    end
    S->>T: Revoke descendant token authority
    A->>H: Apply logout_at controlled mutation when applicable
    A->>U: Append immutable revocation evidence
    A->>A: Commit revocation transaction
    A-->>C: Return approved stable contract
```

## Credential Change And Recovery

```mermaid
sequenceDiagram
    participant C as Authorized Actor
    participant A as Authentication Service
    participant S as Session Lifecycle
    participant T as Token Lifecycle
    participant U as Audit Platform

    alt Approved credential change under DD-AUTH-004
        C->>A: Change credential
        A->>S: Preserve current Session
        A->>T: Preserve current token family
        A->>S: Revoke every other Session
        S->>T: Revoke descendants of other Sessions
        Note over A,S: DD-AUTH-018 preserves Login History and logout_at unchanged
        A->>U: Append immutable PASSWORD_CHANGED evidence
        A->>U: Append applicable immutable SESSION_REVOKED evidence
        A->>A: Commit credential-change transaction
    else Approved credential recovery under ADR-003
        C->>A: Complete recovery
        A->>S: Revoke all Sessions
        S->>T: Revoke all descendant token families
        A->>U: Append immutable credential-recovery evidence
        A->>A: Commit credential-recovery transaction
    end
    A-->>C: Return approved stable contract
```

## Forgot Password Initiation

```mermaid
sequenceDiagram
    participant C as Client
    participant A as Authentication Service
    participant P as Password Broker
    participant N as Notification Platform
    participant U as Audit Platform

    C->>A: Submit email
    A->>P: Request approved reset lifecycle
    alt Account exists
        P->>P: Create hashed single-use reset material
        P->>N: Queue reset notification
    else Account unresolved
        P-->>A: Preserve generic outcome
    end
    A->>U: Append immutable redacted operational evidence
    A->>A: Commit forgot-password transaction
    A-->>C: Return identical generic accepted response
```

## Profile Operations

```mermaid
sequenceDiagram
    participant C as Authenticated Client
    participant A as Authentication Service
    participant F as FileStorage Platform
    participant U as Audit Platform

    alt Get Profile
        C->>A: Request own Profile
        A->>A: Resolve active tenant and approved projection
        A-->>C: Return stable Profile response
    else Update Profile
        C->>A: Submit approved self-service fields
        A->>A: Validate ownership and allowed fields
        opt Binary Photo supplied
            A->>F: Validate and store Photo
            F-->>A: Return persisted Photo reference
        end
        A->>U: Append immutable Profile update evidence
        A->>A: Commit Profile update transaction
        A-->>C: Return updated Profile response
    end
```

## Login History And Device Lists

```mermaid
sequenceDiagram
    participant C as Authenticated Client
    participant A as Authentication Service
    participant H as Login History Projection
    participant D as Device Projection

    alt Login History
        C->>A: Request paginated filtered history
        A->>H: Scope to current User and active tenant
        H->>H: Order by login_at DESC, id DESC
        H-->>A: Return stable nullable projection
        A-->>C: Return paginated response
    else Device List
        C->>A: Request bounded Device list
        A->>D: Scope to current User and apply approved filters
        D->>D: Derive is_active from revoked_at IS NULL
        D-->>A: Return projection without Sensitive persistence fields
        A-->>C: Return paginated Device response
    end
```

## Asynchronous Retention And Cleanup

```mermaid
sequenceDiagram
    participant B as Business Transaction
    participant L as Background Lifecycle Process
    participant P as Retention Policy
    participant G as Legal Hold Authority
    participant R as Persistent State
    participant U as Audit Platform

    B->>R: Apply minimum lifecycle transition
    B->>U: Append immutable evidence
    B->>R: Record cleanup eligibility metadata
    B->>R: Commit business transaction
    B-->>L: Start asynchronous lifecycle processing
    Note over B,L: Business transaction does not wait
    L->>P: Evaluate effective retention policy
    L->>G: Evaluate Legal Hold and ownership scope
    alt Eligible
        L->>R: Archive, purge, or destroy within authority
        L->>U: Append immutable completion evidence
    else Not eligible or held
        L->>U: Preserve deferred outcome evidence
    end
```

Background lifecycle operations are idempotent, retry-safe, resumable, bounded, tenant-aware, and auditable. Secret values and hashes never enter Audit Events or archives.
