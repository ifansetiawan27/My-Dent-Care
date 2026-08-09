# DD-AUTH-007

## Title

Audit & Operational Data Lifecycle Strategy

## Metadata

| Item | Value |
|---|---|
| Identifier | `DD-AUTH-007` |
| Domain | Authentication |
| Decision Type | Domain decision with a satisfied platform-policy dependency |
| Primary Scope | Authentication lifecycle and audit behavior |
| Platform Outcome | Source decision for Accepted ADR-005 Platform Lifecycle and Audit Policy |
| Supersedes | None |

## Status

Accepted — Architecture, Security, Data, API Contract, Performance, Compliance, and Platform Reviews PASS; Final Review Status Accepted

## Superseded By

None.

## Context

Authentication persists durable audit evidence, query-oriented history, mutable operational state, and security data. These objects require different lifecycle semantics while remaining consistent with the canonical field, exposure, ownership, audit, and lifecycle standards.

This Decision owns Authentication-specific object classification and lifecycle behavior. Accepted ADR-005 owns the platform-wide lifecycle and audit principles.

## Problem Statement

Platform membutuhkan strategi lintas domain untuk membedakan immutable Audit Events, operational projections, mutable state, soft deletion, hard deletion, retention, legal hold, archive, dan cleanup.

Tanpa keputusan global, Authentication, Patient, EMR, Finance, Inventory, CRM, AI, dan Integration Hub dapat menerapkan istilah “immutable”, “audit”, dan “delete” secara berbeda sehingga menimbulkan drift, pelanggaran compliance, dan retention yang tidak konsisten.

## Current State

- ADR-004 menetapkan Audit records immutable dan Login History disimpan di persistent storage.
- `login_histories` adalah query-friendly Operational History Projection. Accepted DD-AUTH-017 is the active field-policy authority for nullable `logout_at`; this Decision remains the lifecycle authority for its `Controlled One-Time Mutation`. DD-AUTH-005 is retained only as superseded historical evidence.
- `user_devices` dan `user_sessions` menggunakan revocation lifecycle.
- Access/Refresh Tokens menggunakan expiry/revocation dan dapat dibersihkan sesuai retention/security policy.
- Global `AGENTS.md` sebelumnya mewajibkan soft delete/audit columns untuk semua tenant tables, tetapi token/event/projection tables memerlukan exception berdasarkan kategori.
- `docs/Architecture/Standards/AuditPolicy.md` dan `LifecycleSemantics.md` sudah membedakan immutable Audit Event dari mutable Operational Projection.
- Retention final dan regulatory applicability belum disetujui oleh Compliance Review.

## Decision Drivers

- **Audit integrity** — immutable Audit Events must remain reliable forensic and compliance evidence.
- **Operational lifecycle** — projections and operational state require explicit, limited mutations without being misclassified as immutable audit evidence.
- **Security** — secrets and token material require revocation, expiry, destruction, and redaction policies that differ from ordinary business data.
- **Compliance** — retention, legal hold, right-to-erasure exceptions, and jurisdictional obligations must be explicit and reviewable.
- **Scalability** — retention, archive organization, and cleanup must remain viable across 10–100 Branches and high-volume event streams.
- **Cross-domain reuse** — one canonical lifecycle vocabulary must apply consistently across Platform and business domains.
- **Traceability** — every lifecycle and deletion rule must trace to Requirement, Business Rule, Decision/ADR, data design, API contract, and planned tests.
- **Governance** — category-specific exceptions to global soft-delete/audit rules require an Accepted Decision and platform ADR.

## Options Considered

### Option A — `Soft Deletable` untuk Semua Persistent Data

**Description:** Semua persistent table menggunakan audit columns dan `deleted_at` tanpa exception.

**Advantages:** Uniform implementation pattern; familiar recovery semantics; simple baseline governance.

**Disadvantages:** Treats immutable events, operational projections, secrets, and business records as if they share the same lifecycle.

**Risks:** Retains expired secret material unnecessarily, weakens audit semantics, and creates misleading soft-delete behavior for append-only evidence.

### Option B — Lifecycle Berdasarkan Data Category

**Description:** Gunakan data categories untuk memilih lifecycle vocabulary canonical dari Global Architecture Standards:

- Audit Event data category uses `Append Only` + `Immutable`.
- Operational History Projection uses `Controlled One-Time Mutation` or `Mutable Operational State` as explicitly approved.
- Operational entities use `Mutable Operational State` and/or `Revocable`.
- Security secrets use `Revocable`, `Expiring`, and `Hard Deletable` as applicable.
- Ordinary business records use `Mutable Operational State` + `Soft Deletable` by default.

**Advantages:** Separates forensic evidence, query projections, mutable state, business data, and secret material; reusable across domains; supports deterministic retention and cleanup.

**Disadvantages:** Requires explicit classification, allowed-mutation matrix, retention matrix, and domain review before Design Freeze.

**Risks:** Incorrect classification can cause premature deletion or over-retention; governance and compliance review must remain mandatory.

### Option C — Event Store sebagai Satu-Satunya Source of Truth

**Description:** Semua perubahan disimpan sebagai immutable events dan operational projections dapat direbuild.

**Advantages:** Strong auditability, reproducible projections, and complete temporal history.

**Disadvantages:** High implementation, migration, operational, and query complexity for the current platform phase.

**Risks:** Excessive infrastructure cost, slower delivery, and domain teams implementing inconsistent event semantics without a mature event platform.

## Decision

Select Option B — lifecycle by data category using the canonical Global Architecture Standards.

### API Boundary

This Decision defines lifecycle, revocation, retention, archive, and cleanup policy only. It does not define endpoint paths, methods, request fields, response fields, status codes, messages, or operation-specific API behavior. Authentication operations apply these principles only through the separately governed Accepted Authentication Decisions, `docs/Authentication/API.md`, and `docs/api/openapi.yaml`.

Final canonical wording for review:

> Dental ERP assigns every persistent object exactly one primary Data Category. Field Classification, Exposure Classification, and Lifecycle Semantics remain independent dimensions. Immutable Audit Events use `Append Only` and `Immutable`; Operational History Projections use only explicitly approved canonical semantics; Mutable Operational State uses approved transitions; Business Records use `Soft Deletable` by default; and security data may use `Revocable`, `Expiring`, and `Hard Deletable` only under approved retention and Legal Hold policy.

All required reviews and governance acceptance are complete. This Accepted Decision is the Authentication lifecycle source for Accepted ADR-005; mandatory cross-domain policy is governed by ADR-005 and synchronized repository governance.

### Canonical Authority

Authority is resolved in this order:

1. `AGENTS.md` defines mandatory repository governance and Quality Gates.
2. `docs/Architecture/Standards/` defines canonical classification, exposure, lifecycle, ownership, audit, traceability, drift, and review vocabulary.
3. Accepted DD-AUTH-007 is the Authentication lifecycle source decision and input for ADR-005; it has no independent cross-platform authority.
4. Accepted ADR-005 is the platform lifecycle and audit authority.
5. `AGENTS.md` and affected Global Standards changed only through the separately reviewed governance synchronization, which is complete for ADR-005.
6. Domains reference synchronized canonical governance, Accepted ADR-005, and Global Standards. DD-AUTH-007 remains immutable Authentication decision history and lifecycle authority within its scope.

Authority chain:

```text
DD-AUTH-007 Accepted
        -> input for ADR-005
ADR-005 Accepted
        -> separate governance synchronization
AGENTS.md synchronized through approved governance
        -> canonical repository governance
```

Neither DD-AUTH-007 nor ADR-005 directly modifies, supersedes, or bypasses `AGENTS.md`. Repository governance changes require separate synchronization. ADR-001 and ADR-004 remain immutable unless an explicit superseding ADR is accepted.

## Dependencies

- ADR-005 is Accepted; DD-AUTH-007 is its originating Authentication source decision. This is a one-way source relationship (`DD-AUTH-007 -> ADR-005`), not a reverse dependency.
- DD-AUTH-017 is Accepted and DD-AUTH-005 is Superseded; the field-policy dependency is satisfied.
- Repository governance and affected Authentication design artifacts have been synchronized through separate SDLC tasks.
- Full Drift Detection, final Architecture Review, and Design Freeze remain separate downstream gates.
- ADR-005 references DD-AUTH-007, Global Standards, ADR-001, ADR-004, retention/compliance policy, and affected domain categories.

### Governance Authorities

| Authority | Responsibility | Required Result |
|---|---|---|
| Architecture Review Board | Boundary, lifecycle vocabulary, cross-domain consistency, dependency graph | PASS |
| Security Review | Revocation, token destruction, secret handling, legal-hold-safe cleanup | PASS |
| Data Review | Referential integrity, archive/purge, cascade behavior, storage lifecycle | PASS |
| API Contract Review | Exposure/redaction boundaries and operational projection contracts | PASS |
| Performance Review | Archive/cleanup throughput, query behavior, and storage impact | PASS |
| Compliance Review | Retention, Legal Hold, erasure exceptions, jurisdiction mapping | PASS |
| Platform Review | Reusability across all listed domains and platform authority | PASS |

No single domain owner may approve, shorten, or bypass a platform retention/deletion policy independently.

### Dependency Graph

```text
Global Architecture Standards
        ↓
DD-AUTH-007 reviews (Architecture, Security, Data, API, Performance, Compliance, Platform)
        ↓
DD-AUTH-007 Accepted
        ↓
Platform Lifecycle & Audit ADR Accepted
        ↓
DD-AUTH-017 Architecture/Data/API/Security/Audit Reviews
        ↓
DD-AUTH-017 Accepted
        ↓
Downstream synchronization and Full Drift Detection
```

Dependency direction is one-way. DD-AUTH-017 depends on Accepted DD-AUTH-007 and Accepted ADR-005; DD-AUTH-007 does not depend on DD-AUTH-017. There is no circular dependency.

## Data Category Model

Data Category is an object-level architecture concept in this Decision. It is independent from field-level classification, exposure, and lifecycle semantics.

| Primary Data Category | Authentication Persistent Object | Exclusive Boundary |
|---|---|---|
| Immutable Audit Event | Audit Platform events emitted by Authentication lifecycle actions | Canonical evidence; not Login History, operational state, security data, or a Business Record |
| Operational History Projection | `login_histories` | Query-oriented historical projection; not the canonical Audit Event source or mutable current state |
| Mutable Operational State | `user_devices`, `user_sessions` | Current Authentication operational state; not canonical evidence, a history-only projection, or security data |
| Revocable Security Data | Authentication-issued Access Token records and Refresh Token family records | Primary invalidation is explicit revocation; optional `Expiring` does not create a second category |
| Expiring Security Data | Approved credential-recovery Token records | Primary invalidation is time-based expiry or approved single-use completion; exceptional invalidation does not create a second category |
| Business Record | None in the Authentication persistence scope governed by this Decision | Reserved for ordinary domain records; not evidence, projection, operational state, or security data |

Category rules:

1. Every persistent object in scope has exactly one primary Data Category.
2. Primary Data Categories are mutually exclusive and selected by primary architectural purpose.
3. Fields independently use `FieldClassification.md`; field classification never creates a second object category.
4. Exposure independently uses `ExposureClassification.md`; exposure never changes object category.
5. Lifecycle uses only `LifecycleSemantics.md`; lifecycle behavior never changes object category.
6. Technical logs and distributed transient state remain governed by `AuditPolicy.md` and are not persistent objects in this model.

## Field Classification

All fields use `FieldClassification.md` and `ExposureClassification.md`. Types are logical and may be mapped by domain Database Design without changing classification, exposure, nullability, lifecycle, ownership, invariant, or formula semantics.

| Entity / Field | Type | Classification | Exposure | Nullability | Default | Lifecycle | Ownership Exception | Invariant | Derived Formula |
|---|---|---|---|---|---|---|---|---|---|
| Audit Event `id` | UUID | Core Identity | Audit Only | NOT NULL | Ordered UUID | Immutable | Not Applicable | Globally unique | Not Applicable |
| Audit Event `event_type` | String/Enum | Audit Metadata | Audit Only | NOT NULL | None | Immutable | Not Applicable | Approved event taxonomy | Not Applicable |
| Audit Event `occurred_at` | Timestamp with timezone | Audit Metadata | Audit Only | NOT NULL | System timestamp | Immutable | Not Applicable | Event time never changes | Not Applicable |
| Audit Event `actor_user_id` | UUID | Audit Metadata | Audit Only | Nullable | `NULL` | Immutable | Partially Resolved/Unresolved system or pre-authentication event | Required when actor resolves | Not Applicable |
| Audit Event `target_type` | String | Audit Metadata | Audit Only | Nullable | `NULL` | Immutable | Not Applicable | Required when event targets an entity/User | Not Applicable |
| Audit Event `target_id` | UUID/String | Audit Metadata | Audit Only | Nullable | `NULL` | Immutable | Partially Resolved/Unresolved target | Paired with target type when resolved | Not Applicable |
| Audit Event `organization_id` | UUID | Tenant Ownership | Audit Only | Nullable | `NULL` | Immutable | Resolved/Partially Resolved/Unresolved per OwnershipResolution.md | Never fabricated | Not Applicable |
| Audit Event `branch_id` | UUID | Tenant Ownership | Audit Only | Nullable | `NULL` | Immutable | Resolved/Partially Resolved/Unresolved per OwnershipResolution.md | Never fabricated | Not Applicable |
| Audit Event `correlation_id` | UUID/String | Audit Metadata | Audit Only | NOT NULL | Request/event correlation ID | Immutable | Not Applicable | Traceable across services | Not Applicable |
| Audit Event `ip_address` | IP address | Sensitive | Audit Only | Nullable | `NULL` | Immutable | May exist without resolved ownership | Redacted/minimized where required | Not Applicable |
| Audit Event `user_agent` | Text | Sensitive | Audit Only | Nullable | `NULL` | Immutable | May exist without resolved ownership | Never contains Secret values | Not Applicable |
| Audit Event `reason` | String | Audit Metadata | Audit Only | Nullable | `NULL` | Immutable | Required for administrative/high-risk events | Approved reason code/text | Not Applicable |
| Audit Event `outcome` | String/Enum | Audit Metadata | Audit Only | NOT NULL | None | Immutable | Not Applicable | Approved outcome taxonomy | Not Applicable |
| Audit Event `payload` | Structured object | Sensitive | Audit Only | NOT NULL | Empty object | Immutable | Not Applicable | Never contains Secret values or hashes | Not Applicable |
| Operational Projection enrichment field | Domain scalar or structured object | Enrichment Metadata | Excluded | Nullable | `NULL` | Immutable | Not Applicable | Excluded because a generic platform field has no approved domain API contract; concrete domains may classify a named field as Public API | Not Applicable |
| `login_histories.id` | UUID | Core Identity | Public API | NOT NULL | Ordered UUID | Immutable | Not Applicable | Unique | Not Applicable |
| `login_histories.user_id` | UUID | Tenant Ownership | Persistence Only | Nullable | `NULL` | Immutable | Resolved/Partially Resolved/Unresolved authentication | Never fabricated before User resolution | Not Applicable |
| `login_histories.organization_id` | UUID | Tenant Ownership | Persistence Only | Nullable | `NULL` | Immutable | Resolved/Partially Resolved/Unresolved authentication | Never fabricated before Organization resolution | Not Applicable |
| `login_histories.branch_id` | UUID | Tenant Ownership | Persistence Only | Nullable | `NULL` | Immutable | Resolved/Partially Resolved/Unresolved authentication | Never fabricated before Branch resolution | Not Applicable |
| `login_histories.device_id` | UUID | Tenant Ownership | Persistence Only | Nullable | `NULL` | Immutable | Resolved/Partially Resolved/Unresolved authentication | Never fabricated before Device resolution | Not Applicable |
| `login_histories.identifier` | String | Sensitive | Persistence Only | NOT NULL | Submitted normalized identifier | Immutable | Available before ownership resolution | Never exposed by public history API | Not Applicable |
| `login_histories.login_status` | String/Enum | Business Data | Public API | NOT NULL | None | Immutable | Not Applicable | `success` or `failed` | Not Applicable |
| `login_histories.failure_reason` | String/Enum | Lifecycle Generated | Public API | Nullable | `NULL` | Immutable | Not Applicable | Null on success; approved reason on failure | Not Applicable |
| `login_histories.ip_address` | IP address | Enrichment Metadata (secondary sensitivity label: Sensitive) | Sensitive | Nullable | `NULL` | Immutable | May exist without resolved ownership | Explicitly approved self-service Login History contract only; current-User and active-tenant scope, purpose limitation, minimization, and Policy authorization required; nullable when unavailable | Not Applicable |
| `login_histories.browser` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Immutable | Not Applicable | Best-effort snapshot | Not Applicable |
| `login_histories.operating_system` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Immutable | Not Applicable | Best-effort snapshot | Not Applicable |
| `login_histories.device_name` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Immutable | Not Applicable | Snapshot at authentication time | Not Applicable |
| `login_histories.country` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Immutable | Not Applicable | Best-effort geolocation snapshot | Not Applicable |
| `login_histories.city` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Immutable | Not Applicable | Best-effort geolocation snapshot | Not Applicable |
| `login_histories.login_at` | Timestamp with timezone | Lifecycle Generated | Public API | NOT NULL | System timestamp | Immutable | Not Applicable | Set once at authentication attempt | Not Applicable |
| `login_histories.logout_at` | Timestamp with timezone | Lifecycle Generated | Public API | Nullable | `NULL` | Controlled One-Time Mutation | Not Applicable | Field policy: Accepted DD-AUTH-017; lifecycle authority: DD-AUTH-007; populated once on approved logout/revocation semantics | Not Applicable |
| `user_devices.id` | UUID | Core Identity | Public API | NOT NULL | Ordered UUID | Immutable | Not Applicable | Unique | Not Applicable |
| `user_devices.user_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Never reassigned | Not Applicable |
| `user_devices.organization_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Never reassigned | Not Applicable |
| `user_devices.branch_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Never reassigned | Not Applicable |
| `user_devices.device_uuid` | String | Core Identity | Public API | NOT NULL | None | Immutable | Resolved User required | Unique per User Device | Not Applicable |
| `user_devices.device_name` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Mutable Operational State | Not Applicable | Missing value remains null | Not Applicable |
| `user_devices.device_type` | String/Enum | Business Data | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Approved Device type | Not Applicable |
| `user_devices.platform` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Mutable Operational State | Not Applicable | Best-effort value | Not Applicable |
| `user_devices.user_agent` | Text | Sensitive | Persistence Only | Nullable | `NULL` | Mutable Operational State | Not Applicable | Latest observed; never ordinary API output | Not Applicable |
| `user_devices.browser` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Mutable Operational State | Not Applicable | Best-effort value | Not Applicable |
| `user_devices.operating_system` | String | Enrichment Metadata | Public API | Nullable | `NULL` | Mutable Operational State | Not Applicable | Best-effort value | Not Applicable |
| `user_devices.ip_address` | IP address | Sensitive | Persistence Only | Nullable | `NULL` | Mutable Operational State | Not Applicable | Latest observed; never ordinary API output | Not Applicable |
| `user_devices.last_login_at` | Timestamp with timezone | Lifecycle Generated | Public API | Nullable | `NULL` | Mutable Operational State | Not Applicable | Latest successful Device login | Not Applicable |
| `user_devices.last_activity_at` | Timestamp with timezone | Lifecycle Generated | Public API | Nullable | `NULL` | Mutable Operational State | Not Applicable | Latest tracked activity | Not Applicable |
| `user_devices.is_trusted` | Boolean | Business Data | Public API | NOT NULL | `false` | Mutable Operational State | Not Applicable | Changed only by approved trust capability | Not Applicable |
| `user_devices.revoked_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Controlled One-Time Mutation | Not Applicable | `NULL` to timestamp once | Not Applicable |
| `user_devices.created_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable per current schema | System timestamp | Immutable | Not Applicable | Creation timestamp | Not Applicable |
| `user_devices.updated_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable per current schema | System timestamp | Mutable Operational State | Not Applicable | Updated by approved metadata/lifecycle changes | Not Applicable |
| User Device `is_active` | Boolean | Derived | Derived Public | NOT NULL | Computed | Immutable | Not Applicable | Mirrors revocation state | `revoked_at IS NULL` |
| `user_sessions.id` | UUID | Core Identity | Persistence Only | NOT NULL | Ordered UUID | Immutable | Not Applicable | Unique Session boundary | Not Applicable |
| `user_sessions.user_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Never reassigned | Not Applicable |
| `user_sessions.organization_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Never reassigned | Not Applicable |
| `user_sessions.branch_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved ownership required | Never reassigned | Not Applicable |
| `user_sessions.user_device_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved Device required | Never reassigned | Not Applicable |
| `user_sessions.login_history_id` | UUID | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Immutable | Not Applicable | Links successful immutable Login History when available | Not Applicable |
| `user_sessions.started_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | NOT NULL | System timestamp | Immutable | Not Applicable | Set once at Session creation | Not Applicable |
| `user_sessions.expires_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | NOT NULL | Policy-derived timestamp | Expiring | Not Applicable | Expiry independent from revocation | Not Applicable |
| `user_sessions.revoked_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Controlled One-Time Mutation | Not Applicable | `NULL` to timestamp once | Not Applicable |
| `user_sessions.revoke_reason` | String/Enum | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Controlled One-Time Mutation | Not Applicable | Set atomically with revocation | Not Applicable |
| `user_sessions.created_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable per current schema | System timestamp | Immutable | Not Applicable | Creation timestamp | Not Applicable |
| `user_sessions.updated_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable per current schema | System timestamp | Mutable Operational State | Not Applicable | Updated by approved lifecycle changes | Not Applicable |
| User Session `is_active` | Boolean | Derived | Persistence Only | NOT NULL | Computed | Immutable | Not Applicable | True only when not revoked and not expired | `revoked_at IS NULL AND expires_at > CURRENT_TIMESTAMP` |
| Access Token `id` | Unique identifier | Core Identity | Persistence Only | NOT NULL | Authority generated | Immutable | Not Applicable | Unique token record | Not Applicable |
| Access Token `tokenable_type` | String | Business Data | Persistence Only | NOT NULL | User model type | Immutable | Not Applicable | Matches approved authenticatable type | Not Applicable |
| Access Token secret | Opaque token/hash | Secret | Secret | NOT NULL while stored/issued | Cryptographically generated | Revocable | Not Applicable | Never exposed after issuance or audited/logged | Not Applicable |
| Access Token `session_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved Session required | Never reassigned | Not Applicable |
| Access Token `tokenable_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved User required | Matches Session owner | Not Applicable |
| Access Token `name` | String | Business Data | Persistence Only | NOT NULL | Policy-generated | Immutable | Not Applicable | Approved token naming policy | Not Applicable |
| Access Token `abilities` | JSON/Text | Business Data | Persistence Only | Nullable | `NULL`/approved abilities | Immutable | Not Applicable | Least-privilege abilities | Not Applicable |
| Access Token `expires_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | NOT NULL | Policy-derived timestamp | Expiring | Not Applicable | Expiry independent from revocation | Not Applicable |
| Access Token `last_used_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Mutable Operational State | Not Applicable | Latest successful token use | Not Applicable |
| Access Token `created_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable per authority contract | Authority timestamp | Immutable | Not Applicable | Creation timestamp | Not Applicable |
| Access Token `updated_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable per authority contract | Authority timestamp | Mutable Operational State | Not Applicable | Authority-managed metadata timestamp | Not Applicable |
| Refresh Token `id` | UUID | Core Identity | Persistence Only | NOT NULL | Ordered UUID | Immutable | Not Applicable | Unique token record | Not Applicable |
| Refresh Token `token_hash` | Fixed hash string | Secret | Secret | NOT NULL | Cryptographic hash | Revocable | Not Applicable | Unique; plaintext never persisted | Not Applicable |
| Refresh Token `session_id` | UUID | Tenant Ownership | Persistence Only | NOT NULL | None | Immutable | Resolved Session required | Never reassigned | Not Applicable |
| Refresh Token `expires_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | NOT NULL | Policy-derived timestamp | Expiring | Not Applicable | Expiry independent from revocation | Not Applicable |
| Refresh Token `last_used_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Mutable Operational State | Not Applicable | Latest accepted rotation use | Not Applicable |
| Refresh Token `revoked_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Controlled One-Time Mutation | Not Applicable | `NULL` to timestamp once | Not Applicable |
| Refresh Token `replaced_by_id` | UUID | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Controlled One-Time Mutation | Not Applicable | Set once during rotation | Not Applicable |
| Refresh Token `created_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable | System timestamp | Immutable | Not Applicable | Creation timestamp | Not Applicable |
| Refresh Token `updated_at` | Timestamp with timezone | Audit Metadata | Persistence Only | Nullable | System timestamp | Mutable Operational State | Not Applicable | Updated by approved usage/revocation changes | Not Applicable |
| Password Reset `email` | String | Sensitive | Persistence Only | NOT NULL | Normalized email | Immutable | Resolved User not required for generic response | Credential-recovery authority key | Not Applicable |
| Password Reset `token` | Hash string | Secret | Secret | NOT NULL | Authority-generated hash | Expiring | Not Applicable | Valid for 15 minutes; never exposed after issuance | Not Applicable |
| Password Reset `created_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable per authority contract | System timestamp | Immutable | Not Applicable | Authority-managed | Not Applicable |
| Business Record `deleted_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | `NULL` | Controlled One-Time Mutation | Resolved domain ownership required | `Soft Deletable` after authorization | Not Applicable |
| Archive `purge_eligible_at` | Timestamp with timezone | Lifecycle Generated | Persistence Only | Nullable | Policy-derived | Mutable Operational State | Platform-owned retention metadata | Recomputed only on retention/hold change | Not Applicable |
| Legal Hold `active_hold_count` | Integer | Audit Metadata | Persistence Only | NOT NULL | `0` | Mutable Operational State | Platform-owned Legal Hold state | Count cannot be negative | Not Applicable |
| Archive `is_held` | Boolean | Derived | Persistence Only | NOT NULL | Computed | Immutable | Platform-owned Legal Hold state | Reflects active Legal Holds | `active_hold_count > 0` |
| Legal Hold state | Structured record | Audit Metadata | Audit Only | NOT NULL | Not Held | Mutable Operational State | Platform-owned; Legal/Compliance roles only | Follows approved hold lifecycle | Not Applicable |

DD-AUTH-007 governs Authentication only. Other domains MAY adopt this classification strategy as non-binding guidance before ADR-005 is Accepted. Cross-domain adoption becomes mandatory only after ADR-005 is Accepted and synchronized into repository governance. When a domain voluntarily adopts the strategy, its Derived fields should use one canonical formula and should never be independently authoritative.

## Exposure Classification

Canonical exposure terms are used exactly as defined by `ExposureClassification.md`.

- **Public API**: explicitly approved operational projection and Device fields.
- **Derived Public**: approved formulas exposed without duplicating authoritative storage.
- **Persistence Only**: database-only ownership, lifecycle, and retention metadata.
- **Audit Only**: immutable Audit Events and evidence available through authorized audit/compliance capabilities.
- **Sensitive**: classification label requiring purpose limitation and redaction; public exposure requires a specific domain contract.
- **Secret**: passwords and token values/hashes; never exposed, logged, audited, or archived.
- **Excluded**: omitted from a specific contract with a documented reason.

No exposure term outside the global vocabulary is allowed.

## Lifecycle Semantics

### Batch 1 — Audit Boundary

- Audit Event is append-only and immutable.
- Operational Projection is optimized for query and may have explicitly documented controlled mutations.
- `login_histories` is an Operational Projection, not the canonical immutable Audit Event store.
- `PASSWORD_CHANGED`, `SESSION_REVOKED`, `DEVICE_REVOKED`, `FORCE_PASSWORD_RESET`, `ACCOUNT_LOCKED`, and `ACCOUNT_UNLOCKED` are immutable Audit Events.

### Batch 2 — Allowed Mutation Matrix

Lifecycle vocabulary uses only terms approved by `LifecycleSemantics.md`. Revocation and expiry remain independent: expiry is time-driven; revocation is action-driven.

`login_histories` is an Operational History Projection. Its default is `Immutable`, except `logout_at`, which follows the `Controlled One-Time Mutation` classified by Accepted DD-AUTH-017 under the lifecycle authority of DD-AUTH-007. DD-AUTH-005 remains superseded historical evidence.

| Field | Initial State | Allowed Mutation | Trigger | Final State | Repeatable | Audit Event |
|---|---|---|---|---|---:|---|
| Audit Event fields | Inserted values | None | Not Applicable | Original values | No | Event is the audit evidence |
| `user_devices.device_name` | nullable/string | Replace approved metadata | User-approved metadata update | nullable/string | Yes | Domain policy when material |
| `user_devices.platform` | nullable/string | Replace enrichment | Successful login | nullable/string | Yes | No secret payload |
| `user_devices.user_agent` | nullable/text | Replace enrichment | Login or tracked activity | nullable/text | Yes | Sensitive handling; not ordinary audit payload |
| `user_devices.browser` | nullable/string | Replace enrichment | Login/activity parser | nullable/string | Yes | No |
| `user_devices.operating_system` | nullable/string | Replace enrichment | Login/activity parser | nullable/string | Yes | No |
| `user_devices.ip_address` | nullable IP address | Replace latest observed value | Login or tracked activity | nullable IP address | Yes | Sensitive handling |
| `user_devices.last_login_at` | `NULL`/timestamp | Replace latest timestamp | Successful Device login | Timestamp | Yes | Login Audit Event exists separately |
| `user_devices.last_activity_at` | `NULL`/timestamp | Replace latest timestamp | Tracked authenticated activity | Timestamp | Yes | No |
| `user_devices.is_trusted` | `false`/boolean | Toggle through verified capability | Trusted-Device verification/revocation | Boolean | Yes | Trust-change Audit Event |
| `user_devices.revoked_at` | `NULL` | Set timestamp once | Approved Device-boundary revocation policy | Timestamp | No | `DEVICE_REVOKED` |
| `user_sessions.revoked_at` | `NULL` | Set timestamp once | Approved current-Session termination; approved User-wide, Device-boundary, credential-change, credential-recovery, administrative, or token-reuse revocation policy | Timestamp | No | `SESSION_REVOKED` or the applicable high-level Audit Event |
| `user_sessions.revoke_reason` | `NULL` | Set reason in same atomic revocation transition | Session revocation | Approved reason code | No | Same revocation Audit Event |
| Access Token last-use metadata | nullable/timestamp | Provider-supported update | Successful token use | Timestamp | Yes | No secret payload |
| Refresh Token `last_used_at` | nullable/timestamp | Set latest use timestamp | Successful rotation attempt | Timestamp | Yes | Token rotation Audit Event where required |
| Refresh Token `revoked_at` | `NULL` | Set timestamp once | Rotation or approved Session/Device/User/security revocation policy | Timestamp | No | Applicable revocation Audit Event |
| Refresh Token `replaced_by_id` | `NULL` | Set replacement UUID once | Successful rotation | Replacement UUID | No | Rotation Audit Event |
| Business Record `deleted_at` | `NULL` | Set timestamp once | Authorized domain soft delete | Timestamp | No | Domain deletion Audit Event |
| Archive `purge_eligible_at` | nullable/timestamp | Recompute | Retention or Legal Hold state change | nullable/timestamp | Yes | Retention/hold Audit Event |
| Legal Hold state | no active hold | Add/remove hold through authorized workflow | Legal/Compliance action | Active/inactive hold records | Yes | Legal Hold Audit Event |

All unlisted fields are immutable for the entity lifecycle defined by their domain. Every mutation is atomic, authorization-scoped, idempotent where applicable, and traceable.
#### Controlled One-Time Mutation Rules

- A Controlled One-Time Mutation starts from one documented initial value and transitions once to one documented final value.
- The mutation is atomic, auditable, and idempotent.
- Repeating the same command must not create a second transition or overwrite the final value.
- `user_devices.revoked_at`, `user_sessions.revoked_at`, `user_sessions.revoke_reason`, `refresh_tokens.revoked_at`, and `refresh_tokens.replaced_by_id` follow this rule where specified above.

#### Token Canonical Semantics

| Object | Canonical Lifecycle Semantics |
|---|---|
| Access Token | `Revocable`, `Expiring`, then `Hard Deletable` after approved cleanup eligibility |
| Refresh Token | `Revocable`, `Expiring`; rotation fields use `Controlled One-Time Mutation`; then `Hard Deletable` after approved cleanup eligibility |
| Password Reset Token | `Expiring`, then `Hard Deletable` through the approved credential-recovery lifecycle |

#### Deterministic Security Lifecycle

The state names below are operational conditions, not additional lifecycle vocabulary. Their governing semantics remain `Immutable`, `Controlled One-Time Mutation`, `Mutable Operational State`, `Revocable`, `Expiring`, and `Hard Deletable` as applicable.

##### Access Token

| From | Trigger | Actor | Resulting State | Allowed Transition | Forbidden Transition |
|---|---|---|---|---|---|
| Not issued | Successful authentication or successful Refresh Token rotation after User, Organization, Branch, Device, and Session validation | Authentication Service or Token Service under authenticated system authority | Active Access Token linked to exactly one Session | Issue exactly one Access Token for the Session | Issue without an active Session; issue before tenant/Device validation; persist plaintext token; issue a second active token for the same Session |
| Active | Approved revocation policy targets the owning Session, User, Device, credential lifecycle, administrative scope, or detected token reuse | Authenticated owner under an approved self-service policy, authorized administrator under DD-AUTH-003, or Authentication security policy | Revoked | `Revocable` through the owning Session boundary | Revoke outside the authorized User/tenant target; reactivate a revoked token |
| Active | Approved TTL reached | Trusted system clock and token validation policy | Expired | `Expiring` | Extend the issued token lifetime in place; treat an expired token as active |
| Revoked or Expired | Approved cleanup eligibility, durable redacted evidence, retention completion, and applicable Legal Hold evaluation | Authorized security cleanup authority | Destroyed | `Hard Deletable` | Destroy before evidence durability or cleanup eligibility; archive token value or hash; restore destroyed verifier material |

##### Refresh Token

| From | Trigger | Actor | Resulting State | Allowed Transition | Forbidden Transition |
|---|---|---|---|---|---|
| Not issued | Successful authentication after User, Organization, Branch, Device, and Session validation | Authentication Service or Token Service under authenticated system authority | Active Refresh Token in one Session family | Issue one active token in the Session family and persist only its hash | Issue without an active Session; persist plaintext; create more than one active Refresh Token in the same Session family |
| Active | Successful single-use refresh after Session, User, Organization, Branch, and Device revalidation | Holder of the valid Refresh Token through Token Service | Previous token Revoked with `replaced_by_id`; replacement token Active in the same Session family | Rotate atomically using `Controlled One-Time Mutation`; replace the Access Token in the same Session | Rotate across Session families; reuse the old token; leave both old and replacement tokens active; change ownership during rotation |
| Revoked after rotation | Reuse attempt of the rotated token | Token Service security policy | Entire Refresh Token family Revoked; owning Session and descendant Access Token Revoked | Detect reuse and revoke the family deterministically | Issue a replacement after reuse detection; limit revocation to only the replayed row |
| Active | Approved revocation policy targets the owning Session, User, Device, credential lifecycle, or administrative scope | Authenticated owner under an approved self-service policy, authorized administrator under DD-AUTH-003, or Authentication security policy | Revoked | `Revocable` through the owning Session boundary | Revoke outside authorized scope; reactivate a revoked token |
| Active | Approved TTL reached | Trusted system clock and token validation policy | Expired | `Expiring` | Rotate or accept an expired token; extend expiry in place |
| Revoked or Expired | Token family is terminal, approved cleanup eligibility is reached, durable redacted evidence exists, and applicable Legal Hold evaluation is complete | Authorized security cleanup authority | Destroyed | `Hard Deletable` | Destroy a hash while it is required for active-family reuse detection; archive plaintext/hash; restore destroyed token material |

##### Session

| From | Trigger | Actor | Resulting State | Allowed Transition | Forbidden Transition |
|---|---|---|---|---|---|
| Not created | Successful authentication after User, Organization, Branch, and Device validation | Authentication Service | Active Session linked to one User and one Device | Create one Session boundary before descendant token issuance | Create without resolved ownership/Device; attach the Session to multiple Devices; issue descendant tokens before Session creation |
| Active | Approved current-Session termination policy | Authenticated User owning the current Session | Revoked current Session and all descendant tokens | `Revocable` for the current Session | Revoke another User's Session through a current-Session policy; preserve descendant active tokens |
| Active | Approved User-wide, Device-boundary, other-Session credential-change, credential-recovery, administrative, or token-reuse revocation policy | Authenticated owner, authorized administrator under DD-AUTH-003, or Authentication security policy | Revoked targeted Session set and all descendant tokens | `Revocable` within the deterministic User, Device, or Session boundary | Revoke outside validated target scope; preserve any descendant active token; reactivate a revoked Session |
| Active | Approved Session expiry reached | Trusted system clock and Session validation policy | Expired Session with no valid descendant token | `Expiring` independently from revocation | Extend Session expiry in place without a new approved Session; treat expired Session as active |
| Revoked or Expired | Approved archive eligibility, retention completion, and Legal Hold evaluation | Authorized retention authority | Archived Session metadata when archive is required | Preserve redacted metadata and immutable evidence without restoring operational authority | Archive token secrets; make an archived Session active; issue tokens from archived metadata |
| Revoked, Expired, or Archived | Approved cleanup eligibility after descendant token cleanup and referential-safety validation | Authorized security cleanup authority | Destroyed operational Session record with preserved evidence | `Hard Deletable` when approved | Destroy while descendant security data remains orphaned; delete immutable Audit Events or required Operational History Projection evidence |

Revocation and expiry are independent terminal security conditions. If both apply, the earlier condition prevents further authorization; later evidence may be recorded without reactivating or weakening the Session.

##### Credential-Change Revocation Principle under DD-AUTH-004

The credential-change lifecycle follows Accepted `DD-AUTH-004` exactly. Endpoint behavior remains owned by the API contract; this section states only the resulting lifecycle invariants.

| Target | Required Lifecycle Invariant After Approved Credential Change | Allowed Transition | Forbidden Transition |
|---|---|---|---|
| Current Session | Remains Active | No lifecycle transition; continuity is preserved after approved credential verification and update | Revoke, expire, replace, archive, or destroy the current Session solely because of the credential change |
| Current Access Token | Remains Active until normal expiry or an independent later revocation trigger | No lifecycle transition | Revoke or replace the current Access Token solely because of the credential change |
| Current Refresh Token family | Remains Active and usable under its existing rotation and expiry policy | No lifecycle transition | Revoke, rotate, replace, or destroy the current family solely because of the credential change |
| Every other active Session owned by the User | Revoked immediately | `Revocable` through the User-to-Session boundary | Preserve another active Session; revoke a Session outside the verified User boundary |
| Descendant Access Token of every revoked other Session | Revoked immediately | `Revocable` through its owning Session | Preserve an active descendant Access Token after its Session is revoked |
| Descendant Refresh Token family of every revoked other Session | Revoked immediately | `Revocable` through its owning Session | Preserve, rotate, or issue a replacement from a family whose Session is revoked |
| Registered Device records | Remain registered unless independently revoked | No lifecycle transition | Revoke a Device solely because the credential change succeeded |

The lifecycle actor is the authenticated User in the current Session after approved credential verification. The transition emits the immutable `PASSWORD_CHANGED` Audit Event without password or hash material. Session selection is deterministic through `DD-AUTH-001`; current-versus-other Session policy is authoritative under `DD-AUTH-004`.

##### Device

| From | Trigger | Actor | Resulting State | Allowed Transition | Forbidden Transition |
|---|---|---|---|---|---|
| Not registered | Approved Device-registration lifecycle trigger with resolved User, Organization, Branch, and Device identity | Authorized Device lifecycle authority | Active registered Device | Register one Device identity for the resolved User and tenant context | Register before ownership resolution; assign one Device record to multiple Users; grant trusted status implicitly |
| Active | Approved Device-activity lifecycle trigger | Authorized Device lifecycle authority | Active Device with approved mutable enrichment/activity fields updated | `Mutable Operational State` only for allowlisted fields | Reassign User/Organization/Branch ownership; change immutable Device identity; overwrite revocation evidence |
| Active | Approved owner-initiated or administrative Device-boundary revocation policy | Device owner or authorized administrator under DD-AUTH-003 | Revoked Device and all child Sessions and descendant tokens Revoked | `Revocable` with synchronous descendant revocation | Revoke outside authorized target scope; preserve active child Session/token; reactivate the revoked record |
| Revoked | Any lifecycle transition targeting the same Device record | Authorized Device lifecycle authority | Remains Revoked | No transition; revocation is terminal for that Device record | Re-register, reactivate, or overwrite the same revoked Device record |
| Revoked | Approved cleanup eligibility after all child Session/token cleanup, evidence durability, retention completion, and Legal Hold evaluation | Authorized security cleanup authority | Destroyed Device record with preserved redacted evidence | `Hard Deletable` when approved | Destroy while child security data would become orphaned; restore destroyed Device authority; delete immutable Audit Events |

###### Device Lifecycle Boundary

Device revocation is terminal for the revoked Device record:

1. A revoked Device record can never transition back to Active.
2. The revoked Device record cannot be restored, overwritten, or silently replaced by a lifecycle mutation.
3. The revoked Device record retains its revocation evidence until approved `Hard Deletable` cleanup eligibility.
4. This Decision defines the revoked Device state and lifecycle only; it does not define Authentication-flow behavior for a revoked Device.
5. Handling of revoked Devices during Authentication remains owned by `DD-AUTH-001`, Authentication Business Rules, `docs/Authentication/API.md`, and `docs/api/openapi.yaml`.
6. Any future re-enrollment or replacement policy requires its own approved authority and cannot mutate the revoked record.

- Expiry is time-driven and does not imply an explicit security revocation event.
- Revocation is action-driven and emits the applicable immutable Audit Event.
- Token hard deletion removes secret/security state only; it never deletes immutable Audit Events or required Operational Projection evidence.
- Access Token TTL remains 60 minutes under ADR-002.
- Refresh Token TTL remains 30 days under ADR-002.
- Refresh rotation is single-use; reuse of a rotated token revokes the entire Refresh Token family.
- Token rotation policy revalidates User, Organization, Branch, and Device state as required by ADR-002. The owning Session must separately remain active under DD-AUTH-001 before a replacement pair is issued.
- Password Reset Token is valid for 15 minutes, single-use, and authority-managed under ADR-003.
- Approved credential-recovery completion revokes every active User Session and all descendant Access/Refresh Tokens, while registered Devices remain, as governed by ADR-003.

#### Cleanup Governance Sequence

Cleanup is not an additional lifecycle vocabulary. It is a governance sequence applied after the object's canonical lifecycle semantics: the object must satisfy its approved retention trigger, Legal Hold evaluation, archive requirement where applicable, and `Hard Deletable` authorization.

- Retention enforcement, cleanup, purge, and cryptographic destruction are scheduled asynchronous background lifecycle operations.
- These operations must never execute inline with a normal user request or normal business transaction.
- A normal business transaction may record lifecycle completion, detached evidence, and cleanup eligibility only; the scheduled background operation performs physical purge or destruction later.
- Background lifecycle operations are idempotent, resumable, bounded, observable, retry-safe, and independently auditable.
- Cleanup eligibility begins only after operational use ends and the applicable retention period has completed.
- Legal Hold blocks archive purge and hard deletion.
- Cleanup jobs must be idempotent, tenant-aware where ownership exists, and emit immutable audit evidence.
- FK/cascade behavior must preserve Audit Events and required Operational Projections.

#### Archive Policy

- This Decision defines archive lifecycle, archive eligibility, purge authority, and destruction authority only. Operational access mechanisms are defined by future API Contracts where applicable.
- Archive, archive movement, archive compaction, and archive migration are asynchronous background lifecycle operations.
- These archive operations must never execute synchronously within a normal user request or normal business transaction.
- A normal business transaction may only record the lifecycle event and archive eligibility metadata required for later background processing; it does not wait for archive storage work to complete.
- Background archive processing must be idempotent, resumable, bounded, observable, and independently auditable without changing the outcome of the originating business transaction.
- Archive is a storage transition, not deletion.
- Archived records are immutable.
- Archive preserves identifiers, ownership context, integrity metadata, retention deadline, and Legal Hold state.
- Archive contains no live authorization authority and cannot authenticate a principal, authorize a request, issue a token, validate a token, or establish a Session.
- Access Token values/hashes, Refresh Token plaintext/hashes, Password Reset Token values/hashes, password material, authorization headers, and cookies are never archived.
- Revoked or Expired security data can never become Active through an archive lifecycle transition.
- Archived evidence remains immutable and authoritative for its historical purpose.
- Archive purge occurs only after retention completion, Legal Hold clearance, authorization, and an immutable purge audit event.

##### Archive Authority

| Lifecycle Concern | Authority | Mandatory Boundary |
|---|---|---|
| Archive eligibility | Authorized retention authority | Operational purpose ended, approved retention trigger reached, Legal Hold evaluated, and integrity metadata available |
| Archive transition | Authorized retention authority | Tenant/ownership scope preserved, Secret material excluded, archive immutable, and transition audited |
| Purge | Authorized retention authority with required approval | Retention complete, Legal Hold cleared, archive integrity verified, scope bounded, and destruction evidence emitted |
| Cryptographic destruction | Restricted data-governance/security authority with dual control where required | Purge conditions satisfied, key/archive identity validated, retained data unaffected, and destruction irreversible |

No tenant administrator, domain service, or cleanup authority may bypass these lifecycle boundaries. This Decision does not define archive read, restore, copy, or endpoint behavior; such operations require separately reviewed future Decisions and API Contracts where applicable.

### Batch 3 — Deletion Strategy

Deletion is a deterministic lifecycle transition, never an ad-hoc repository operation.

| Category / Entity | Operational End Trigger | Strategy | Cascade Behavior | Archive Policy | Purge Authorization | Destruction Evidence |
|---|---|---|---|---|---|---|
| Immutable Audit Event | Online retention threshold reached | No soft/hard delete under current governance | Never cascade-delete evidence | Move to immutable protected archive storage; retain indefinitely | Purge prohibited unless separately synchronized canonical governance and an explicit superseding ADR authorize it | Archive-integrity event with category, range, count, digest, approver, timestamp; no payload secrets |
| `login_histories` Operational Projection | Retention deadline reached | No user delete; controlled hard purge after archive | FK uses `SET NULL`/preserve strategy so purge does not delete Audit Events | Archive immutable projection snapshot before purge | Compliance role + retention job + no Legal Hold | `PROJECTION_PURGED` audit event with IDs/range/count/digest |
| `user_devices` | Final revocation plus retention elapsed | Revoke first; hard delete after eligibility | Device revocation synchronously revokes child Sessions and descendant tokens; hard delete only after child cleanup or FK-safe archive | Archive metadata required for investigation before purge | Security operations role + retention job + no Legal Hold | `DEVICE_RECORD_PURGED` audit event; no user-agent/token secret values |
| `user_sessions` | Revoked/expired plus retention elapsed | Revoke/expire first; hard delete after eligibility | Session revocation synchronously revokes descendant Access/Refresh Tokens; Login History/Audit remains | Archive Session metadata before purge when required by incident/legal policy | Security operations role + retention job + no Legal Hold | `SESSION_RECORD_PURGED` audit event with Session ID and descendant counts |
| Access Token | Replacement, revocation, or expiry | Cryptographic revocation, evidence detachment, then scheduled background hard deletion of the token record | Deleting token must not delete Session, Device, Login History, or Audit Event; replacement preserves one Access Token record per Session | Never archive secret/hash; detach redacted revocation/rotation evidence into Audit | Eligible for asynchronous security cleanup after evidence durability; Legal Hold applies to detached metadata only | `TOKEN_DESTROYED` audit event with token record ID, type, reason, timestamp; never token/hash value |
| Refresh Token | Family becomes terminal or final token expires, followed by the Organization security-retention window; 90 days is an illustrative baseline subject to Compliance approval and Platform ADR authority | Retain hashes for family reuse detection, then hard delete after eligibility | Token family linkage remains until cleanup; no cascade into Session/Audit | Never archive plaintext; retain hash only during the approved reuse-detection window and detached redacted evidence thereafter | Automated cleanup after the approved Organization security-retention window; Legal Hold applies to detached evidence only | `TOKEN_DESTROYED` audit event with record/family ID, reason, timestamp; never token/hash value |
| Password Reset Token | Consumed/expired | Authority-governed hard delete | Token-record deletion has no ownership cascade; approved credential recovery separately revokes all User Sessions/tokens before cleanup | No archive | Credential-recovery authority cleanup under approved schedule | Aggregate cleanup audit/metric; never token/hash value |
| Business Record | Domain-approved deletion | `Soft Deletable` | Restrict by default; cascade only through Accepted domain decision | Domain retention/archival policy | Authorized domain role + retention/legal checks | Domain-specific immutable deletion/purge audit event |

Deterministic rules:

1. Revocation, expiry, replacement, or authority-approved single-use consumption must occur before security state becomes Hard Deletable, according to the entity lifecycle.
2. Retention completion and Legal Hold clearance are mandatory before permitted projection/state purge or hard deletion; immutable Audit Events are not purge-eligible under current governance. Secret/token hashes follow security destruction schedules even under Legal Hold after required evidence is detached and held.
3. Cascade is used for revocation commands, not for deleting immutable evidence.
4. Database FK cascades must never remove Audit Events or required Operational Projections.
5. Hard-delete jobs are idempotent, resumable, batch-limited, and produce destruction evidence.
6. Secret values and hashes are never copied to archives, audit payloads, or destruction evidence.
7. `Soft Deletable` is the default only for ordinary Business Records; it is not the default for Audit Events, projections, or expiring secrets.

### Batch 4 — Audit Retention

Illustrative retention guidance for final Compliance Review:

#### Retention Authority

- This Decision defines the data retention model, retention categories, retention ownership, retention start triggers, cleanup authority, retention workflow, and interaction with lifecycle semantics.
- This Decision does **not** define binding retention durations.
- Every duration below is an illustrative baseline only, subject to Compliance Review and Organization policy.
- Binding retention durations become effective only after **all** of the following conditions are satisfied:
  1. `STEP_05_17_6_COMPLIANCE_REVIEW_PASS`.
  2. DD-AUTH-007 is Accepted.
  3. The Platform Lifecycle and Audit ADR is Accepted.
- Until the Platform ADR is Accepted, these examples are non-binding and cannot authorize archive, purge, or cleanup timing.
- Applicable Organization policy may select or refine durations only within the authority granted after all three conditions above are satisfied.
- Cleanup ordering remains deterministic and independent from duration approval: lifecycle completion, retention eligibility under the binding policy, Legal Hold evaluation, archive requirements, and cleanup authorization remain mandatory in that order.

| Category | Illustrative Baseline (Non-binding) | Retention Start | Archive | Purge | Legal Hold |
|---|---:|---|---|---|---|
| Login History Operational Projection | Example baseline: 7 years, subject to Compliance approval, Organization policy, and Platform ADR authority | `login_at` | Archive before purge | After the binding Organization retention duration and hold clearance | Supported |
| Immutable Audit Event | Example baseline: 10 years online/primary retention with preserved archive, subject to Compliance approval and Platform ADR authority | Event timestamp | Immutable protected archive storage | No purge under current governance | Supported |
| Critical Security Event | Example baseline: 10 years online/primary retention with preserved archive, subject to Compliance approval and Platform ADR authority | Event timestamp | Immutable encrypted archive | No purge under current governance | Supported |
| Revoked/Expired Access Token record | Immediate after replacement/revocation, or at expiry | Replacement, `revoked_at`, or `expires_at` | No token/hash archive; detached redacted evidence retained in Audit | Hard delete token row after evidence is durably recorded | Hold applies only to detached metadata/evidence, never token/hash |
| Refresh Token family records | Example baseline: until terminal plus 90 days, subject to Compliance approval, Organization security policy, and Platform ADR authority | Later of family terminal revocation or final family-token expiry | No plaintext archive; token hashes remain only for reuse detection until cleanup eligibility | Hard delete after the binding security-retention duration and hold-safe evidence detachment | Hold applies to detached family metadata/evidence; hashes are destroyed after security retention |
| Password Reset Token | Example cleanup window: credential-recovery TTL plus no more than 24 hours, subject to Compliance approval and Platform ADR authority | Expiry/consumption | None | Authority cleanup after the binding approved window | Not applicable to token secret; related Audit Event may be held |
| User Device metadata | Example baseline: 1 year, subject to Compliance approval, Organization policy, and Platform ADR authority | Later of final `revoked_at` or `last_activity_at` | Archive before purge when required | After the binding Organization retention duration and hold clearance | Supported |
| User Session metadata | Example baseline: 1 year, subject to Compliance approval, Organization policy, and Platform ADR authority | Later of `revoked_at` or `expires_at` | Archive before purge when required | After the binding Organization retention duration and hold clearance | Supported |

These durations remain non-binding guidance inside this Accepted Decision. Binding values require the completed Compliance Review, Accepted DD-AUTH-007, Accepted ADR-005, and the applicable Organization policy. No single condition is sufficient by itself. Secret/token-hash destruction windows likewise require that authority; only detached redacted evidence may be retained longer when the approved policy permits it.

### Legal Hold Policy

Legal Hold applies to Audit Events, Operational Projections, Device metadata, Session metadata, and redacted token metadata when required for an investigation or legal obligation.

| Hold Condition | Trigger | Allowed Action | Exit Condition |
|---|---|---|---|
| Not Held | Default | Normal retention/archive/cleanup | Hold issued |
| Held | Authorized legal/compliance request | Read under authorization; no purge/hard delete; archive allowed without destruction | Authorized release |
| Released | Legal/compliance release recorded | Retention clock resumes from the preserved original deadline; immediate eligibility is recalculated | Purge/archive completion or new hold |
| Expired Hold | Only when hold includes approved expiry | Same as Released after audit validation | System records expiration and reviewer confirmation |

Legal Hold rules:

- Hold creation, modification, release, and expiry require authorized Legal/Compliance roles and immutable audit events.
- A hold stores scope, reason, authority, start, optional expiry, approver, and correlation ID.
- Legal Hold never requires retaining plaintext token/password secrets or token hashes beyond security retention. Secret material is destroyed on schedule; redacted metadata and immutable Audit Events remain under hold as evidence.
- Cleanup jobs must check hold state atomically immediately before purge.
- Records under hold cannot be removed by tenant administrators or ordinary domain users.

#### Legal Hold Precedence

When security destruction, retention, and Legal Hold requirements apply to the same lifecycle evidence, the following priority is authoritative:

1. **Priority 1 - Secret destruction required by security policy.** Secret values, token hashes, password material, encryption material scheduled for destruction, and other cryptographic material are destroyed according to the approved security policy. Secret destruction takes precedence over retention of cryptographic material.
2. **Priority 2 - Immutable Audit Evidence retained according to Legal Hold.** Immutable Audit Events and detached redacted destruction/revocation evidence remain available under the applicable Legal Hold without retaining Secret material.
3. **Priority 3 - Operational History Projection follows retention policy.** Operational History Projections follow their approved retention policy and are preserved when the Legal Hold scope requires them; they never substitute for immutable Audit evidence.
4. **Priority 4 - Business Records follow Legal Hold.** Business Records within hold scope remain protected from purge or destructive cleanup until an authorized hold release and retention re-evaluation.

Legal Hold never restores destroyed Secret material and never creates a recovery path for destroyed cryptographic material. Secret destruction is irreversible. Detached immutable audit evidence may continue to exist after Secret destruction and remains available for audit, investigation, and Legal Hold purposes. Legal Hold preserves evidence, not cryptographic secrets. Operational History Projections never become the canonical audit source.

When policies conflict, the security policy governing Secret destruction takes precedence. Required audit evidence remains preserved through detached Immutable Audit Events without retaining or reconstructing the destroyed Secret material. Once Secret destruction has completed, no later hold creation, modification, release, archive restoration, or operational-copy process may recreate that material.

Token-specific clarification:

- Same-Session rotation replaces the single active Access Token record. The prior Access Token verifier is destroyed after detached revocation/rotation evidence is durably recorded, preserving the one-active-Access-Token-per-Session uniqueness invariant.
- Rotated Refresh Token hashes remain in their family until the family is terminal or expired so reuse detection can revoke the active family as required by ADR-002.
- Refresh family cleanup begins only after the final family token can no longer be valid; an illustrative 90-day window may preserve security investigation evidence and late reuse detection but is non-binding until Compliance Review PASS, Platform ADR acceptance, and Organization policy approval.

### Batch 5 — Compliance Exception

### Compliance Mapping

This decision targets compatible architecture patterns; it does not claim certification or legal compliance.

| Authority / Regulation | Intended Compatibility | Required Deployment Review |
|---|---|---|
| Indonesia Personal Data Protection Law and health/medical-record obligations | Data minimization, lawful retention, auditability, access control, deletion exceptions | Indonesian Legal/Compliance review for current sector regulations and retention obligations |
| GDPR | Purpose limitation, storage limitation, erasure assessment, legal obligation exception, pseudonymization | EU deployment-specific lawful basis, controller/processor, residency, and DPA review |
| PDPA variants | Access control, retention, disposal, accountability | Country-specific PDPA review |
| HIPAA | Audit controls, access history, integrity, minimum necessary, retention policy | US healthcare deployment and covered-entity/business-associate review |
| Contractual / Insurance / Government integrations | Evidence retention, security incidents, delivery history | Contract-by-contract review |

### Right to Erasure

- A request is evaluated against legal obligation, medical/financial retention, fraud/security defense, and Legal Hold.
- Immutable Audit Events are not automatically deleted when lawful retention applies.
- Immutable Audit Events use data minimization and pseudonymous subject references at ingestion. Direct identity, when needed, is stored in a separately governed identity-mapping record rather than rewritten inside the immutable event.
- When erasure is legally permitted, the detachable identity mapping may be deleted or irreversibly pseudonymized after retention/hold validation; the immutable Audit Event remains unchanged and no longer resolves to the erased identity.
- Secret token/password material follows security cleanup and is not retained merely for erasure evidence.
- Erasure denial, partial fulfillment, pseudonymization, or purge emits an immutable compliance audit event with legal basis and approver.

### Cryptographic Destruction Evidence

- Encrypted archives may be cryptographically destroyed only after retention completion, Legal Hold clearance, authorization, and integrity verification.
- Destruction evidence records key identifier/version, archive identifier/digest, category, record range/count, approval, timestamp, and outcome.
- Encryption keys and plaintext secrets are never placed in audit evidence.
- Key destruction is irreversible and requires dual control for regulated/critical datasets.
- Cryptographic destruction is prohibited for Immutable Audit Event and Critical Security Event archives while current governance requires indefinite preservation.
- Archive key destruction applies only to categories that are purge-eligible under an Accepted Platform ADR and completed Compliance Review.

### ADR-001 and Audit ADR Relationship

ADR-001 remains the transient lockout authority. ADR-004 is retained as Superseded historical evidence; Accepted ADR-006 is the active authority distinguishing immutable canonical Authentication Audit Events from the Login History Operational History Projection.

This Decision's lifecycle model is reconciled with ADR-006 as follows:

- `login_histories` is an **Operational History Projection**. Its fields are `Immutable` except the `logout_at` `Controlled One-Time Mutation` classified by Accepted DD-AUTH-017 under the lifecycle authority of DD-AUTH-007. DD-AUTH-005 remains superseded historical evidence.
- Login identity/status/evidence fields remain immutable.
- Every login/logout/revocation also emits a separate append-only immutable Audit Event under ADR-006 and ADR-005.
- The controlled projection mutation does not alter the canonical Audit Event and does not erase or rewrite login evidence.

Accepted authority relationships:

- ADR-001 remains Accepted and is not superseded for distributed transient lockout state and retained Login History evidence.
- ADR-004 is Superseded by ADR-006 and remains immutable historical evidence.
- ADR-006 is the Accepted active authority for Authentication audit separation and Login History projection behavior.
- Accepted ADR-005 is required before cross-domain synchronization may begin. It does not supersede `AGENTS.md`; the broad soft-delete rule changes only through a separate approved synchronization of repository governance and affected Global Standards.
- No downstream artifact may call Login History itself the immutable audit source.

## Cross-domain Applicability

This section preserves the candidate guidance reviewed before platform acceptance. DD-AUTH-007 applies only to Authentication; cross-domain authority now belongs to Accepted ADR-005 and synchronized repository governance.

| Candidate Domain | Non-binding Candidate Guidance |
|---|---|
| Authentication | Login History, Devices, Sessions, Tokens, security Audit Events |
| Patient | Patient lifecycle, consent, demographic changes, access audit |
| Appointment | Appointment lifecycle, cancellation/no-show history, scheduling audit, retention |
| EMR | Medical records, amendments, access trail, retention/legal hold |
| Finance | Invoice/payment state, financial audit, retention |
| Inventory | Stock movement ledger, balance projections, corrections |
| HR | Employee lifecycle, attendance/payroll projections, access and change audit |
| CRM | Customer interactions, consent, retention/privacy |
| AI | Prompts, model outputs, provenance, safety/audit evidence |
| Integration Hub | Requests/responses, retries, delivery status, payload retention |
| Future Domains | Follow Accepted ADR-005 and synchronized repository governance within their approved domain scope |

#### Cross-Domain Platform Review Matrix

| Domain | Representative Data | Candidate Lifecycle Application | Audit Boundary | Status |
|---|---|---|---|---|
| Authentication | Login History, Devices, Sessions, Tokens | Authentication-specific mappings defined by this Decision | Authentication Audit Events remain immutable | In scope |
| Patient | Demographics, consent, identity merges, access history | To be classified by domain Decision | Patient access/change Audit Events remain separate from records | ADR-005 Accepted; domain review deferred |
| Appointment | Schedules, confirmations, cancellations, no-shows | To be classified by domain Decision | Scheduling Audit Events remain separate from Appointment records | ADR-005 Accepted; domain review deferred |
| EMR | Clinical entries, amendments, access history | To be classified by domain Decision | Clinical access/amendment Audit Events remain separate | ADR-005 Accepted; domain review deferred |
| Finance | Invoice/payment state, reversals, ledger projections | To be classified by domain Decision | Financial Audit Events remain separate from operational projections | ADR-005 Accepted; domain review deferred |
| Inventory | Stock movements, balances, corrections | To be classified by domain Decision | Movement/correction Audit Events remain separate | ADR-005 Accepted; domain review deferred |
| HR | Employee lifecycle, attendance, payroll projections | To be classified by domain Decision | HR access/change Audit Events remain separate from records | ADR-005 Accepted; domain review deferred |
| CRM | Interactions, consent, campaign history | To be classified by domain Decision | Consent and high-risk change Audit Events remain separate from records | ADR-005 Accepted; domain review deferred |
| AI | Prompt/input metadata, outputs, provenance, safety decisions | To be classified by domain Decision | AI provenance/safety Audit Events remain separate | ADR-005 Accepted; domain review deferred |
| Integration Hub | Requests, responses, retries, delivery state, payload metadata | To be classified by domain Decision | Integration attempt/delivery Audit Events remain separate | ADR-005 Accepted; domain review deferred |

Boundary rules:

- Cross-domain reuse was validated by the completed Platform Review and Accepted ADR-005.
- Domain teams receive authority from ADR-005 and synchronized repository governance, not directly from DD-AUTH-007.
- No cross-domain row above independently authorizes implementation; domain Design Freeze gates still apply.
- Domain-specific obligations remain deferred to their designated reviews and Design Freeze gates.

## Ownership Exceptions

- Audit Events preserve actor/target ownership when resolved and may be Partially Resolved or Unresolved only for pre-authentication/security events.
- Operational Projections follow their domain ownership model and must document nullable ownership exceptions.
- Cross-tenant administrative actions require explicit Platform-level authority, permission, Policy approval, purpose/reason, step-up authentication where required, and validated target tenant context; they record both actor tenant and target tenant.
- Legal Hold and retention metadata is platform-owned and cannot be changed by ordinary tenant users.
- Platform lifecycle governance is owned by the Architecture Review Board with mandatory Security, Data, Compliance, and Platform Review participation.
- Domain teams classify their data and may propose stricter retention, but cannot weaken the accepted Platform ADR baseline without a superseding platform decision.

### Sensitive Audit Ingestion Policy

- Immutable Audit Event payloads use an explicit event-type allowlist; fields not allowlisted are rejected before ingestion.
- Sensitive fields require documented purpose limitation and least-privilege Audit Only access.
- IP address and user agent are minimized deterministically according to the event contract and deployment privacy policy before immutable ingestion.
- Passwords, token values, password/token hashes, authorization headers, cookies, and raw secret payloads are always denied regardless of event allowlist.
- Redaction/minimization occurs before event persistence; immutable Audit Events are never rewritten later to repair over-collection.
- Audit read access requires explicit permission, tenant/target scope, reason for high-risk access, and immutable access-audit evidence.

## Consequences

- Global soft-delete requirements become category-aware instead of universal.
- Authentication may use Immutable Audit Events, an Operational History Projection with the approved `logout_at` `Controlled One-Time Mutation`, Device/Session revocation, and `Hard Deletable` token cleanup without semantic conflict.
- Authentication must add its allowed-mutation matrix and retention/deletion classification before its Design Freeze; other domains follow this requirement only after Accepted ADR-005 establishes cross-domain authority.
- Cleanup/archive jobs require legal-hold checks and immutable audit evidence.
- Retention durations in this proposal are non-binding guidance. Binding durations require Compliance Review PASS, DD-AUTH-007 acceptance, Platform ADR acceptance, and applicable Organization policy; no individual approval establishes them independently.
- DD-AUTH-017 is Accepted and is the active field-policy authority; DD-AUTH-005 is Superseded.
- An architecture-significant Accepted outcome must be recorded in ADR-005 before a separate governance synchronization may align `AGENTS.md` and affected Global Standards.
- DD-AUTH-007 acceptance alone did not authorize cross-domain synchronization; Accepted ADR-005 and separate governance synchronization now provide platform authority.
- Decision and ADR status must be reflected accurately in the Decision Register, ADR Register, Traceability Matrix, Drift Detection Report, and Architecture Checklist.
- Tests must prove immutable audit behavior, controlled projection mutation, revocation cascade, retention eligibility, and legal-hold blocking.
- **Performance:** retention evaluation, archive jobs, storage organization, hold checks, and cleanup batches must be benchmarked at expected event volume; cleanup is batch-limited and must not block authentication or domain transactions.
- **Migration/compatibility:** existing tables require classification and allowed-mutation review before schema changes; backfill/archival migration must preserve identifiers, FK evidence, timestamps, tenant context, and audit integrity; rollback cannot restore cryptographically destroyed secrets.
- **Operations:** platform monitoring must report retention backlog, archive failures, hold-blocked records, purge eligibility, destruction evidence, and background-operation retry/failure state.

## Affected Documents

- `AGENTS.md`
- `docs/Architecture/Standards/index.md`
- `docs/Architecture/Standards/ArchitectureReviewChecklist.md`
- `docs/Architecture/Standards/AuditPolicy.md`
- `docs/Architecture/Standards/LifecycleSemantics.md`
- `docs/Architecture/Standards/FieldClassification.md`
- `docs/Architecture/Standards/ExposureClassification.md`
- `docs/Architecture/Standards/OwnershipResolution.md`
- `docs/Architecture/Standards/DecisionLifecycle.md`
- `docs/Architecture/Standards/TraceabilityRules.md`
- `docs/Architecture/Standards/DriftDetection.md`
- `docs/Authentication/Decision/DD-AUTH-017.md`
- `docs/Authentication/Requirement.md`
- `docs/Authentication/ERD.md`
- `database_design/007_Authentication.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/Architecture.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/DependencyGraph.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- `docs/ADR/ADR-001-Authentication-Lockout.md`
- `docs/ADR/ADR-002-Authentication-Token.md`
- `docs/ADR/ADR-003-Password-Reset.md`
- `docs/ADR/ADR-004-Authentication-Audit.md`
- `docs/ADR/ADR-005-Platform-Lifecycle-Audit-Policy.md` (Accepted platform lifecycle and audit authority).
- Future domain Requirements, Business Rules, Decisions/ADRs, Database Designs, ERDs, APIs, Services, cleanup/archive jobs, and tests.

## Review Status

Structure Completion Review: PASS (`STEP_05_17_0A_STRUCTURE_PASS`).

Lifecycle Canonicalization Review: PASS (`STEP_05_17_0B_LIFECYCLE_PASS`).

Retention Design Completeness Review: PASS (`STEP_05_17_0C_RETENTION_PASS`).

Governance Structure Review: PASS (`STEP_05_17_0D_GOVERNANCE_PASS`).

Architecture Review: PASS (`STEP_05_17_1_ARCHITECTURE_REVIEW_PASS`).

Security Review: PASS (`STEP_05_17_2_SECURITY_REVIEW_PASS`).

Data Review: PASS (`STEP_05_17_3_DATA_REVIEW_PASS`).

API Contract Review: PASS (`STEP_05_17_4_API_REVIEW_PASS`).

Performance Review: PASS (`STEP_05_17_5_PERFORMANCE_REVIEW_PASS`).

Compliance Review: PASS (`STEP_05_17_6_COMPLIANCE_REVIEW_PASS`).

Platform Review: PASS (`STEP_05_17_7_PLATFORM_REVIEW_PASS`).

ADR-005: Accepted (`ADR_005_ACCEPTED_PASS`); platform-policy dependency satisfied.

Final Review Status: Accepted.

Implementation Status: Not started.

## Traceability

- Drift finding: `DD-AUTH-007` in `docs/Authentication/DriftDetectionReport.md`.
- Dependency outcome: DD-AUTH-017 Accepted; DD-AUTH-005 Superseded.
- Global standards: AuditPolicy, LifecycleSemantics, FieldClassification, OwnershipResolution.
- ADR dependencies: ADR-001 Authentication Lockout, ADR-002 Authentication Token, ADR-003 Password Reset, and ADR-004 Authentication Audit.
- Authentication Requirements: `AUTH-REQ-005`, `AUTH-REQ-006`, `AUTH-REQ-010`, `AUTH-REQ-012`, `AUTH-REQ-014`, `AUTH-REQ-017`.
- Authentication Business Rules: `AUTH-BR-005`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-010`, `AUTH-BR-011`, `AUTH-BR-012`, `AUTH-BR-016`.
- Authentication entities: `login_histories`, `user_devices`, `user_sessions`, `personal_access_tokens`, `refresh_tokens`.
- Authentication API operations: `auth.loginHistory.index`, `auth.devices.index`, `auth.devices.destroy`, `auth.logout`, `auth.logoutAll`, `auth.resetPassword`, `auth.changePassword`.
- Authentication API schemas: `LoginHistory`, `Device`, `DeviceListResponse`, `ChangePasswordResponse`.
- Audit Events: `PASSWORD_CHANGED`, `SESSION_REVOKED`, `DEVICE_REVOKED`, `FORCE_PASSWORD_RESET`, `ACCOUNT_LOCKED`, `ACCOUNT_UNLOCKED`.
- Planned tests: mutation restrictions, audit immutability, revocation cascade, password-reset 15-minute expiry/single-use/authority cleanup/all-session revocation, retention eligibility, legal hold, cross-tenant audit context.
- Planned implementation: platform audit/archive/retention services, legal-hold policy, cleanup jobs, observability, and domain adapters are `PLANNED` only.
- Planned test artifacts: Audit immutability tests, Operational Projection mutation tests, Session/Device revocation cascade tests, retention/Legal Hold tests, purge/destruction-evidence tests, and cross-domain adoption tests are `PLANNED`.
- Governance dependency: `docs/Architecture/Standards/ArchitectureReviewChecklist.md`.
- Platform authority dependency: Accepted `docs/ADR/ADR-005-Platform-Lifecycle-Audit-Policy.md`.
- Dependent decision: Accepted `DD-AUTH-017`; no circular dependency.

## Post-Acceptance Governance Note

- DD-AUTH-007 Status: Accepted; Authentication lifecycle authority.
- ADR-005 Status: Accepted; platform lifecycle and audit authority.
- DD-AUTH-017 Status: Accepted; active Authentication field-policy authority.
- DD-AUTH-005 Status: Superseded by DD-AUTH-017 and retained only as historical evidence.
- Repository governance synchronization: completed as a separate SDLC activity.
- Full Drift Detection, final Architecture Review, and Design Freeze remain separate downstream gates.
- ADR-004 Status: Superseded by ADR-006.
- ADR-006 Status: Accepted; active Authentication audit-evidence and Login History projection authority.
