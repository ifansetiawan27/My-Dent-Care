# DD-AUTH-017

## Title

Authentication Field Classification and Nullability Strategy

## Status

Accepted — Architecture, Security, Data, API Contract, and Audit/Compliance Reviews PASS; Final Quality Gate and Governance Acceptance PASS

## Supersedes

`DD-AUTH-005` (supersession completed).

Reason: The original decision did not classify every persistent and exposed Authentication field, did not distinguish Persistence Only from Public API exposure, and did not fully define lifecycle mutations or ownership-resolution exceptions.

## Problem

Authentication needs one complete and globally consistent field policy for persistence, ERD, API, OpenAPI, lifecycle mutation, ownership resolution, audit, and tests.

The existing DD-AUTH-005 correctly introduced nullable enrichment and lifecycle fields, but omitted several persistent fields and left ambiguity about:

- Fields stored only in PostgreSQL.
- Sensitive identifiers and tenant ownership fields.
- Allowed one-time or repeatable mutations.
- Nullable ownership before identity/tenant resolution.
- Derived fields and formulas.
- The relationship between Login History projection updates and immutable Audit events.

## Current State

- DD-AUTH-005 is Superseded and retained only as historical decision evidence; DD-AUTH-017 is the active field-policy authority.
- Database Design contains persistent fields not classified by DD-AUTH-005.
- API/OpenAPI expose a selected stable response projection rather than every persistence field.
- ADR-004 requires immutable Audit events.
- Login History currently contains `logout_at`, which implies a controlled lifecycle update.
- DD-AUTH-007 is Accepted and supplies the Authentication-specific lifecycle and audit authority required by this proposal.
- ADR-005 is Accepted and supplies the synchronized platform lifecycle, audit, retention, archive, Legal Hold, cleanup, ownership, and exposure governance required by this proposal.
- All required reviews, Final Quality Gate, and Governance Acceptance are complete.

## Options

### Option A — Keep DD-AUTH-005 Unchanged

Classify only the fields currently exposed through Login History and Device APIs.

Rejected for proposal because persistent-only fields, ownership exceptions, and lifecycle mutation remain undefined.

### Option B — Complete Authentication-Specific Classification

Extend the accepted policy using the global Architecture Standards, classify every persistent field, define exposure and lifecycle, and supersede DD-AUTH-005 with a new unique Decision ID.

Recommended.

### Option C — Expose Every Persistent Field

Mirror database fields directly in public API responses.

Rejected for proposal because tenant IDs, identifiers, user agents, IP addresses, revocation metadata, and timestamps may be sensitive or Persistence Only.

## Decision

Select Option B.

Adopt the canonical global standards in `docs/Architecture/Standards/` and classify all Authentication fields below. Public APIs expose only explicitly approved fields. Persistence Only fields remain traceable with an exclusion reason. Derived fields include formulas.

This Accepted Decision is binding within its field-governance scope. DD-AUTH-007 remains the Authentication lifecycle authority and ADR-005 remains the platform lifecycle authority.

## Field Classification

Every field uses three independent dimensions:

1. Exactly one Primary Field Classification from `FieldClassification.md`.
2. An optional Secondary Sensitivity Label using the canonical `Sensitive` label.
3. Exactly one Exposure Classification from `ExposureClassification.md`.

The self-service access boundary is a Policy constraint, not an Exposure Classification.

### Login History

Object Category: `Operational History Projection` under Accepted DD-AUTH-007 and ADR-005. It is not the canonical Audit Event source.

| Field | Primary Field Classification | Secondary Sensitivity Label | Exposure Classification | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|---|
| `id` | Core Identity | Not Applicable | Public API | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `user_id` | Tenant Ownership | Not Applicable | Persistence Only | Nullable | Immutable | Resolved, Partially Resolved, or Unresolved | Not Applicable |
| `organization_id` | Tenant Ownership | Not Applicable | Persistence Only | Nullable | Immutable | Resolved, Partially Resolved, or Unresolved | Not Applicable |
| `branch_id` | Tenant Ownership | Not Applicable | Persistence Only | Nullable | Immutable | Resolved, Partially Resolved, or Unresolved | Not Applicable |
| `device_id` | Tenant Ownership | Not Applicable | Persistence Only | Nullable | Immutable | Resolved, Partially Resolved, or Unresolved | Not Applicable |
| `identifier` | Sensitive | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved, Partially Resolved, or Unresolved | Not Applicable |
| `login_status` | Business Data | Not Applicable | Public API | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `failure_reason` | Lifecycle Generated | Not Applicable | Public API | Nullable | Immutable | Not Applicable | Not Applicable |
| `ip_address` | Enrichment Metadata | Sensitive | Sensitive | Nullable | Immutable | Resolved, Partially Resolved, or Unresolved | Not Applicable |
| `browser` | Enrichment Metadata | Not Applicable | Public API | Nullable | Immutable | Not Applicable | Not Applicable |
| `operating_system` | Enrichment Metadata | Not Applicable | Public API | Nullable | Immutable | Not Applicable | Not Applicable |
| `device_name` | Enrichment Metadata | Not Applicable | Public API | Nullable | Immutable | Not Applicable | Not Applicable |
| `country` | Enrichment Metadata | Not Applicable | Public API | Nullable | Immutable | Not Applicable | Not Applicable |
| `city` | Enrichment Metadata | Not Applicable | Public API | Nullable | Immutable | Not Applicable | Not Applicable |
| `login_at` | Lifecycle Generated | Not Applicable | Public API | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `logout_at` | Lifecycle Generated | Not Applicable | Public API | Nullable | Controlled One-Time Mutation | Not Applicable | Not Applicable |

### User Device

| Field | Primary Field Classification | Secondary Sensitivity Label | Exposure Classification | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|---|
| `id` | Core Identity | Not Applicable | Public API | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `user_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `organization_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `branch_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `device_uuid` | Core Identity | Not Applicable | Public API | NOT NULL | Immutable | Resolved | Not Applicable |
| `device_name` | Enrichment Metadata | Not Applicable | Public API | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `device_type` | Business Data | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `platform` | Enrichment Metadata | Not Applicable | Public API | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `user_agent` | Sensitive | Not Applicable | Persistence Only | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `browser` | Enrichment Metadata | Not Applicable | Public API | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `operating_system` | Enrichment Metadata | Not Applicable | Public API | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `ip_address` | Sensitive | Not Applicable | Persistence Only | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `last_login_at` | Lifecycle Generated | Not Applicable | Public API | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `last_activity_at` | Lifecycle Generated | Not Applicable | Public API | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `is_trusted` | Business Data | Not Applicable | Public API | NOT NULL, default false | Mutable Operational State | Not Applicable | Not Applicable |
| `revoked_at` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Controlled One-Time Mutation | Not Applicable | Not Applicable |
| `created_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable per current schema | Immutable | Not Applicable | Not Applicable |
| `updated_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable per current schema | Mutable Operational State | Not Applicable | Not Applicable |
| `is_active` | Derived | Not Applicable | Derived Public | NOT NULL | Immutable | Not Applicable | `revoked_at IS NULL` |

### User Session

| Field | Primary Field Classification | Secondary Sensitivity Label | Exposure Classification | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|---|
| `id` | Core Identity | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `user_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `organization_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `branch_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `user_device_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `login_history_id` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Immutable | Resolved | Not Applicable |
| `started_at` | Lifecycle Generated | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `expires_at` | Lifecycle Generated | Not Applicable | Persistence Only | NOT NULL | Expiring | Not Applicable | Not Applicable |
| `revoked_at` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Controlled One-Time Mutation | Not Applicable | Not Applicable |
| `revoke_reason` | Lifecycle Generated | Sensitive | Persistence Only | Nullable | Controlled One-Time Mutation | Not Applicable | Not Applicable |
| `created_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable per current schema | Immutable | Not Applicable | Not Applicable |
| `updated_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable per current schema | Mutable Operational State | Not Applicable | Not Applicable |
| `is_active` | Derived | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | `revoked_at IS NULL AND expires_at > CURRENT_TIMESTAMP` |

### Access Token

| Field | Primary Field Classification | Secondary Sensitivity Label | Exposure Classification | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|---|
| `id` | Core Identity | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `tokenable_type` | Business Data | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `tokenable_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `session_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `name` | Business Data | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `token` | Secret | Not Applicable | Secret | NOT NULL | Revocable | Resolved | Not Applicable |
| `abilities` | Business Data | Sensitive | Persistence Only | Nullable | Immutable | Not Applicable | Not Applicable |
| `last_used_at` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `expires_at` | Lifecycle Generated | Not Applicable | Persistence Only | NOT NULL | Expiring | Not Applicable | Not Applicable |
| `created_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable per authority contract | Immutable | Not Applicable | Not Applicable |
| `updated_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable per authority contract | Mutable Operational State | Not Applicable | Not Applicable |

### Refresh Token

| Field | Primary Field Classification | Secondary Sensitivity Label | Exposure Classification | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|---|
| `id` | Core Identity | Not Applicable | Persistence Only | NOT NULL | Immutable | Not Applicable | Not Applicable |
| `session_id` | Tenant Ownership | Not Applicable | Persistence Only | NOT NULL | Immutable | Resolved | Not Applicable |
| `token_hash` | Secret | Not Applicable | Secret | NOT NULL | Revocable | Resolved | Not Applicable |
| `expires_at` | Lifecycle Generated | Not Applicable | Persistence Only | NOT NULL | Expiring | Not Applicable | Not Applicable |
| `last_used_at` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Mutable Operational State | Not Applicable | Not Applicable |
| `revoked_at` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Controlled One-Time Mutation | Not Applicable | Not Applicable |
| `replaced_by_id` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable | Controlled One-Time Mutation | Not Applicable | Not Applicable |
| `created_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable | Immutable | Not Applicable | Not Applicable |
| `updated_at` | Audit Metadata | Not Applicable | Persistence Only | Nullable | Mutable Operational State | Not Applicable | Not Applicable |

### Password Reset Token

| Field | Primary Field Classification | Secondary Sensitivity Label | Exposure Classification | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|---|
| `email` | Sensitive | Not Applicable | Persistence Only | NOT NULL | Immutable | Unresolved, Partially Resolved, or Resolved | Not Applicable |
| `token` | Secret | Not Applicable | Secret | NOT NULL | Expiring | Unresolved, Partially Resolved, or Resolved | Not Applicable |
| `created_at` | Lifecycle Generated | Not Applicable | Persistence Only | Nullable per authority contract | Immutable | Not Applicable | Not Applicable |

## Exposure Classification

### Public API

- Login History: `id`, `login_at`, `logout_at`, `browser`, `operating_system`, `device_name`, `country`, `city`, `login_status`, `failure_reason`.
- Device: `id`, `device_uuid`, `device_name`, `platform`, `browser`, `operating_system`, `last_login_at`, `last_activity_at`, `is_trusted`.

### Derived Public

- Device `is_active = revoked_at IS NULL`.

### Persistence Only

- Login History: `user_id`, `organization_id`, `branch_id`, `device_id`, `identifier`.
- Device: `user_id`, `organization_id`, `branch_id`, `device_type`, `revoked_at`, `created_at`, `updated_at`.
- Session: every persisted and derived field in this inventory.
- Access Token: every field except the token verifier, which is `Secret`.
- Refresh Token: every field except `token_hash`, which is `Secret`.
- Password Reset Token: `email`, `created_at`.

### Sensitive

- Login History `ip_address` is available only through the explicitly approved self-service Login History contract with current-User and active-tenant scope, purpose limitation, data minimization, and Policy authorization. This policy boundary does not change its `Sensitive` Exposure Classification.
- Device `ip_address` and `user_agent` use primary `Sensitive` Field Classification with `Persistence Only` exposure and are excluded from ordinary public Device responses, matching Accepted DD-AUTH-007.
- Login History `identifier`, Session `revoke_reason`, Access Token `abilities`, and Password Reset Token `email` carry a `Sensitive` primary classification or secondary label with non-public exposure.
- All Sensitive fields require minimization, access control, and redaction in logs/audit payloads.

### Secret

- Access Token `token`, Refresh Token `token_hash`, and Password Reset Token `token` are `Secret` and never exposed, logged, audited, archived, or returned after approved issuance/input handling.

### Audit Only

Not Applicable as field exposure for these entities. Authentication lifecycle actions emit separate immutable Audit Events under ADR-006, ADR-005, and DD-AUTH-007. ADR-004 remains superseded historical evidence.

### Excluded

- Persistence Only fields are intentionally excluded from public Authentication responses to prevent tenant metadata leakage, credential-identifier exposure, and raw client fingerprint disclosure.
- Sensitive Device `ip_address` and `user_agent` are excluded from ordinary public Device responses.
- `device_type` remains Persistence Only because the approved Device response contract exposes platform/browser/OS and does not currently require the raw registration category.

## Lifecycle Semantics

### Login History

Entity default: `Immutable`, except the explicitly allowed `Controlled One-Time Mutation` below.

| Field | Initial State | Allowed Mutation | Trigger | Final State | Repeatable | Audit Event |
|---|---|---|---|---|---:|---|
| `logout_at` | `NULL` | Set timestamp | Explicit Logout or approved Session revocation | Timestamp | No | Session/logout audit event |

All other Login History fields are `Immutable`. Expiry without explicit logout/revocation does not populate `logout_at` unless a future superseding decision approves it. Canonical Audit Events remain separate, `Append Only`, and `Immutable`.

### User Device

Entity default: identity/ownership fields immutable; approved enrichment and lifecycle fields mutable as listed.

| Field | Initial State | Allowed Mutation | Trigger | Final State | Repeatable | Audit Event |
|---|---|---|---|---|---:|---|
| `device_name` | nullable | Replace metadata | Approved Device metadata update | nullable/string | Yes | When policy requires |
| `platform` | nullable | Replace enrichment | Successful login | nullable/string | Yes | No secret data |
| `user_agent` | nullable | Replace enrichment | Login/activity | nullable/text | Yes | Redacted from ordinary logs |
| `browser` | nullable | Replace enrichment | Login/activity parser | nullable/string | Yes | No |
| `operating_system` | nullable | Replace enrichment | Login/activity parser | nullable/string | Yes | No |
| `ip_address` | nullable | Replace latest value | Login/activity | nullable/inet | Yes | Sensitive handling |
| `last_login_at` | nullable | Replace timestamp | Successful Device login | timestamp | Yes | Login audit exists separately |
| `last_activity_at` | nullable | Replace timestamp | Tracked authenticated activity | timestamp | Yes | No |
| `is_trusted` | false | Toggle via verified flow | Trusted-Device capability | boolean | Yes | Trust-change audit |
| `revoked_at` | `NULL` | Set timestamp | Device Revocation | Timestamp | No | `DEVICE_REVOKED` |
| `updated_at` | nullable | System update | Any approved repeatable mutation | Timestamp | Yes | Follows triggering event |

All unlisted Device fields are immutable.

### User Session

The authoritative allowed-mutation matrix is defined by Accepted DD-AUTH-007.

This Decision classifies fields only and does not redefine lifecycle transitions.

Applicable canonical lifecycle semantics:

- `Immutable`.
- `Mutable Operational State`.
- `Controlled One-Time Mutation`.
- `Revocable`.
- `Expiring`.
- `Hard Deletable`.

### Access Token

The authoritative allowed-mutation matrix is defined by Accepted DD-AUTH-007.

This Decision classifies fields only and does not redefine lifecycle transitions.

Applicable canonical lifecycle semantics:

- `Immutable`.
- `Mutable Operational State`.
- `Revocable`.
- `Expiring`.
- `Hard Deletable`.

### Refresh Token

The authoritative allowed-mutation matrix is defined by Accepted DD-AUTH-007.

This Decision classifies fields only and does not redefine lifecycle transitions.

Applicable canonical lifecycle semantics:

- `Immutable`.
- `Mutable Operational State`.
- `Controlled One-Time Mutation`.
- `Revocable`.
- `Expiring`.
- `Hard Deletable`.

### Password Reset Token

The authoritative allowed-mutation matrix is defined by Accepted DD-AUTH-007.

This Decision classifies fields only and does not redefine lifecycle transitions.

Applicable canonical lifecycle semantics:

- `Immutable`.
- `Expiring`.
- `Hard Deletable`.

## Ownership Exceptions

### Login History

- `user_id`, `organization_id`, `branch_id`, and `device_id` normally resolve for successful authentication.
- They may be `NULL` when a failed authentication occurs before User, tenant, Branch, or Device resolution.
- Ownership Resolution State is `Resolved`, `Partially Resolved`, or `Unresolved` as defined by `docs/Architecture/Standards/OwnershipResolution.md`.
- Unresolved ownership never grants authorization or data access.
- `identifier` and available request evidence are retained as Sensitive/Persistence Only evidence without fabricating ownership IDs.

### User Device

- Ownership must be `Resolved` before a Device record is created.
- `user_id`, `organization_id`, and `branch_id` are `NOT NULL` and immutable.
- No Partially Resolved or Unresolved Device record is permitted.

## Consequences

- DD-AUTH-005 remains historical and is Superseded by this Accepted Decision.
- Database Design, ERD, API.md, OpenAPI, Resources, and tests must use this complete field inventory.
- Public response shapes remain stable; nullable public fields are present with `null` values.
- Persistence Only fields require explicit exclusion mappings in Traceability Matrix.
- Login History allows exactly one controlled `logout_at` mutation; immutable Audit Events remain append-only under ADR-006 and ADR-005.
- Accepted DD-AUTH-007 and ADR-005 provide the audit/deletion/lifecycle authority; this Decision remains consistent with both.
- Migrations must enforce approved nullability, constraints, and ownership requirements.
- Models and Resources must preserve nullable timestamps and enrichment values without fake defaults.
- Feature Tests must cover Resolved, Partially Resolved, and Unresolved Login History ownership; nullable enrichment; stable response presence; and derived `is_active`.

## Policy Consistency

1. Login History is an `Operational History Projection`, not a canonical Audit Event source.
2. Login History fields are `Immutable` by default; `logout_at` alone uses `Controlled One-Time Mutation` under DD-AUTH-017 field governance and DD-AUTH-007 lifecycle authority. DD-AUTH-005 remains superseded historical evidence.
3. Canonical Audit Events remain `Append Only` and `Immutable` under ADR-006, DD-AUTH-007, and ADR-005. ADR-004 remains superseded historical evidence.
4. Every documented nullable public field remains present in the stable response shape and is returned as `null` when no value exists.
5. User Device `is_active` remains `Derived Public` with the sole canonical formula `revoked_at IS NULL`; it is never independently persisted or authoritative.
6. Ownership exceptions use only `Resolved`, `Partially Resolved`, and `Unresolved`. Missing ownership identifiers are never fabricated, and unresolved ownership never grants authorization.
7. Fields with `Secret` exposure are never returned, logged, audited, archived, or included in examples. No `Secret` field has `Public API`, `Derived Public`, or `Sensitive` exposure.
8. Login History `ip_address` follows Accepted DD-AUTH-007: primary `Enrichment Metadata`, secondary `Sensitive`, exposure `Sensitive`, and an explicitly authorized self-service policy boundary.
9. User Device `ip_address` and `user_agent` follow Accepted DD-AUTH-007: primary `Sensitive` and exposure `Persistence Only`.
10. This Decision introduces no lifecycle, classification, exposure, ownership, or audit policy that conflicts with Accepted DD-AUTH-007 or ADR-005.

## Affected Documents

- `docs/Architecture/Standards/FieldClassification.md`
- `docs/Architecture/Standards/ExposureClassification.md`
- `docs/Architecture/Standards/LifecycleSemantics.md`
- `docs/Architecture/Standards/OwnershipResolution.md`
- `docs/Architecture/Standards/AuditPolicy.md`
- `docs/Authentication/Decision/DD-AUTH-005.md`
- `docs/Authentication/Decision/DD-AUTH-007.md`
- `docs/Authentication/Requirement.md`
- `docs/Authentication/BusinessRule.md`
- `database_design/007_Authentication.md`
- `docs/Authentication/ERD.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/SequenceDiagram.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- Future Authentication Migrations, Models, Resources, Repositories, and Tests.

## Review Status

Architecture Review: PASS (`STEP_DD_AUTH_017_ARCHITECTURE_REVIEW_PASS`).

Data Review: PASS (`STEP_DD_AUTH_017_DATA_REVIEW_PASS`).

API Contract Review: PASS (`STEP_DD_AUTH_017_API_CONTRACT_REVIEW_PASS`).

Security Review: PASS (`STEP_DD_AUTH_017_SECURITY_REVIEW_PASS`).

Audit/Compliance Review: PASS (`STEP_DD_AUTH_017_AUDIT_COMPLIANCE_REVIEW_PASS`).

Final Quality Gate: PASS (`DD_AUTH_017_FINAL_QUALITY_GATE_PASS`).

Governance Acceptance: PASS (`DD_AUTH_017_ACCEPTED_PASS`).

Final Review Status: Accepted.

Implementation Status: Not started.

## Traceability

- Supersedes: `DD-AUTH-005` (completed).
- Dependencies: Accepted `DD-AUTH-007` Audit and Operational Data Lifecycle Strategy and Accepted `ADR-005` Platform Lifecycle and Audit Policy.
- Governance reference: synchronized `AGENTS.md`, `docs/Architecture/Standards/`, and Accepted ADR-005.
- Requirements: `AUTH-REQ-001`, `AUTH-REQ-002`, `AUTH-REQ-003`, `AUTH-REQ-004`, `AUTH-REQ-005`, `AUTH-REQ-006`, `AUTH-REQ-007`, `AUTH-REQ-010`, `AUTH-REQ-011`, `AUTH-REQ-012`, `AUTH-REQ-014`, `AUTH-REQ-015`, `AUTH-REQ-017`.
- Business Rules: `AUTH-BR-001`, `AUTH-BR-002`, `AUTH-BR-004`, `AUTH-BR-005`, `AUTH-BR-006`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-010`, `AUTH-BR-011`, `AUTH-BR-012`, `AUTH-BR-013`, `AUTH-BR-015`, `AUTH-BR-016`.
- ADRs: Accepted ADR-005 Platform Lifecycle and Audit Policy; Accepted ADR-006 Authentication Audit Evidence and Login History Projection Authority; Superseded ADR-004 historical Authentication Audit Strategy.
- Data entities: Login History, User Device, User Session, Access Token, Refresh Token, and Password Reset Token.
- API operations: `auth.login`, `auth.logout`, `auth.logoutAll`, `auth.refresh`, `auth.forgotPassword`, `auth.resetPassword`, `auth.changePassword`, `auth.loginHistory.index`, `auth.devices.index`, `auth.devices.destroy`.

### Entity Traceability Matrix

| Entity | Requirements | Business Rules | API Operations | Planned Tests |
|---|---|---|---|---|
| Login History | `AUTH-REQ-010`, `AUTH-REQ-014`, `AUTH-REQ-015`, `AUTH-REQ-017` | `AUTH-BR-005`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-016` | `auth.login`, `auth.logout`, `auth.logoutAll`, `auth.loginHistory.index` | `PLANNED`: nullable stable response, ownership states, controlled `logout_at`, Sensitive IP boundary, audit separation |
| User Device | `AUTH-REQ-001`, `AUTH-REQ-011`, `AUTH-REQ-012`, `AUTH-REQ-015`, `AUTH-REQ-017` | `AUTH-BR-001`, `AUTH-BR-006`, `AUTH-BR-007`, `AUTH-BR-015` | `auth.login`, `auth.devices.index`, `auth.devices.destroy` | `PLANNED`: Resolved ownership, nullable enrichment, Sensitive exclusions, derived `is_active`, revocation |
| User Session | `AUTH-REQ-001`, `AUTH-REQ-002`, `AUTH-REQ-003`, `AUTH-REQ-004`, `AUTH-REQ-006`, `AUTH-REQ-007`, `AUTH-REQ-012`, `AUTH-REQ-015` | `AUTH-BR-001`, `AUTH-BR-002`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-011`, `AUTH-BR-012`, `AUTH-BR-013`, `AUTH-BR-016` | `auth.login`, `auth.logout`, `auth.logoutAll`, `auth.refresh`, `auth.resetPassword`, `auth.changePassword`, `auth.devices.destroy` | `PLANNED`: ownership, expiry, revocation reason, descendant revocation, derived internal activity state |
| Access Token | `AUTH-REQ-001`, `AUTH-REQ-002`, `AUTH-REQ-003`, `AUTH-REQ-004`, `AUTH-REQ-006`, `AUTH-REQ-007`, `AUTH-REQ-012`, `AUTH-REQ-015`, `AUTH-REQ-017` | `AUTH-BR-001`, `AUTH-BR-002`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-011`, `AUTH-BR-012`, `AUTH-BR-013`, `AUTH-BR-016` | `auth.login`, `auth.logout`, `auth.logoutAll`, `auth.refresh`, `auth.resetPassword`, `auth.changePassword`, `auth.devices.destroy` | `PLANNED`: Session linkage, expiry, revocation, one-active-token invariant, Secret exclusion |
| Refresh Token | `AUTH-REQ-001`, `AUTH-REQ-002`, `AUTH-REQ-003`, `AUTH-REQ-004`, `AUTH-REQ-006`, `AUTH-REQ-007`, `AUTH-REQ-012`, `AUTH-REQ-015`, `AUTH-REQ-017` | `AUTH-BR-001`, `AUTH-BR-002`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-011`, `AUTH-BR-012`, `AUTH-BR-013`, `AUTH-BR-016` | `auth.login`, `auth.logout`, `auth.logoutAll`, `auth.refresh`, `auth.resetPassword`, `auth.changePassword`, `auth.devices.destroy` | `PLANNED`: rotation lineage, reuse detection, expiry, revocation, Secret exclusion |
| Password Reset Token | `AUTH-REQ-005`, `AUTH-REQ-006`, `AUTH-REQ-017` | `AUTH-BR-010`, `AUTH-BR-011` | `auth.forgotPassword`, `auth.resetPassword` | `PLANNED`: generic response, single use, expiry, ownership states, Secret exclusion |

## Post-Acceptance Governance Note

- DD-AUTH-017 Status: Accepted; active Authentication field classification, exposure, nullability, and field-governance authority.
- DD-AUTH-005 Status: Superseded by DD-AUTH-017 and retained only as historical evidence.
- DD-AUTH-007 Status: Accepted; Authentication lifecycle authority.
- ADR-005 Status: Accepted; platform lifecycle and audit authority.
- Downstream synchronization is governed by separate SDLC tasks and does not alter this Decision's accepted intent.
- ADR-004 Status: Superseded by ADR-006.
- ADR-006 Status: Accepted; active Authentication audit-evidence and Login History projection authority.
