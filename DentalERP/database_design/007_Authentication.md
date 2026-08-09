# Authentication Database Design

## Overview

| Item | Detail |
|---|---|
| Module | Authentication |
| Engine | PostgreSQL |
| Cache and rate limit | Redis |
| Access token | Laravel Sanctum `personal_access_tokens` |
| Refresh token | Hashed opaque token |
| Tenant scope | `organization_id` and `branch_id` |

Authentication extends the existing `users`, `organizations`, and `branches` tables. It does not duplicate user identity or authorization data.

## Governance and Data Categories

This design follows Accepted `DD-AUTH-007`, Accepted `ADR-005`, Accepted `DD-AUTH-017`, Accepted `DD-AUTH-018`, and the synchronized Global Architecture Standards. DD-AUTH-017 is the active field classification, exposure, nullability, and field-governance authority; DD-AUTH-018 is the credential-change projection-precedence authority; DD-AUTH-005 remains superseded historical evidence.

| Persistent Object | Primary Data Category | Canonical Lifecycle |
|---|---|---|
| Audit Platform event emitted by Authentication | Immutable Audit Event | `Append Only`, `Immutable` |
| `login_histories` | Operational History Projection | `Immutable` by default; `logout_at` uses `Controlled One-Time Mutation` under DD-AUTH-017, with lifecycle authority delegated to DD-AUTH-007 |
| `user_devices` | Mutable Operational State | `Mutable Operational State`, `Revocable`; cleanup only under Accepted retention policy |
| `user_sessions` | Mutable Operational State | `Mutable Operational State`, `Revocable`, `Expiring`; cleanup only under Accepted retention policy |
| Authentication Access Token and Refresh Token records | Revocable Security Data | `Revocable`, `Expiring`, then `Hard Deletable` when eligible |
| Password Reset Token records | Expiring Security Data | `Expiring`, then `Hard Deletable` when eligible |

Data Category, Field Classification, Exposure Classification, and Lifecycle Semantics are independent. Business-specific retention durations remain governed externally and are not established by this database design.

## Entity Relationships

```text
users
  |--< personal_access_tokens
  |--< login_histories
  |--< user_devices
  `--< user_sessions

user_devices
  |--< user_sessions
  `--< login_histories

user_sessions
  |--|| personal_access_tokens
  `--< refresh_tokens
```

Authentication hierarchy:

```text
User
  └── Device
        └── Session
              ├── Access Token (Sanctum)
              └── Refresh Token family
```

Device represents hardware/browser identity. `user_sessions` represents one authenticated login. Device lifecycle is independent from Session lifecycle.

## Sanctum Table: `personal_access_tokens`

Laravel Sanctum manages access tokens. Requirements:

- `tokenable_id` supports User UUID.
- Access tokens are short-lived and revocable.
- Logout revokes the current Session and its descendant Access Token.
- Logout All revokes every active Session owned by the User and all descendant Access Tokens.
- Revoking a Device revokes every active Session under it and all descendant Access Tokens.
- Authentication-issued `personal_access_tokens` has required, indexed `session_id` referencing `user_sessions.id`.
- Each active Session owns exactly one active Sanctum Access Token.

| Column | Type | Nullable | Constraint / Index | Description |
|---|---|---:|---|---|
| `id` | bigint | NO | PRIMARY KEY | Sanctum token row identifier |
| `tokenable_type` | varchar(255) | NO | COMPOSITE INDEX | Approved authenticatable model type |
| `tokenable_id` | uuid | NO | COMPOSITE INDEX, POLYMORPHIC | Resolved User identifier; no physical FK because the relationship is polymorphic |
| `session_id` | uuid | NO | FK, UNIQUE | References `user_sessions.id` |
| `name` | varchar(255) | NO | | Policy-generated token name |
| `token` | char(64) | NO | UNIQUE | Hashed Sanctum token verifier; Secret |
| `abilities` | text | YES | | Serialized least-privilege abilities |
| `last_used_at` | timestamptz | YES | | Last successful token use |
| `expires_at` | timestamptz | NO | INDEX | Access Token expiry |
| `created_at` | timestamptz | YES | | Created timestamp |
| `updated_at` | timestamptz | YES | | Updated timestamp |

Additional indexes:

- `(tokenable_type, tokenable_id)`
- `(expires_at)`

Unique constraints:

- `(token)`
- `(session_id)`

Active Access Token invariant:

```text
UNIQUE (session_id)
```

One Session owns one Sanctum Access Token row. Revocation/deletion mechanics for that row are finalized by DD-AUTH-007; the database must not use a volatile time expression in an index predicate.

## Table: `user_sessions`

`user_sessions` is the Authentication session boundary. Device lifecycle remains independent from Session lifecycle.

One Device may own multiple Sessions. Each successful authentication creates exactly one Session.

| Column | Type | Nullable | Constraint / Index | Description |
|---|---|---:|---|---|
| `id` | uuid | NO | PRIMARY KEY | Ordered UUID Session identifier |
| `user_id` | uuid | NO | FK, INDEX | Session owner |
| `organization_id` | uuid | NO | FK, INDEX | Tenant Organization context |
| `branch_id` | uuid | NO | FK, INDEX | Tenant Branch context |
| `user_device_id` | uuid | NO | FK, INDEX | Parent recognized Device |
| `login_history_id` | uuid | YES | FK, INDEX | Successful Login History reference |
| `started_at` | timestamptz | NO | INDEX | Session start timestamp |
| `expires_at` | timestamptz | NO | INDEX | Session maximum lifetime |
| `revoked_at` | timestamptz | YES | INDEX | Session revocation timestamp |
| `revoke_reason` | varchar(100) | YES | | Revocation reason code |
| `created_at` | timestamptz | YES | | Created timestamp |
| `updated_at` | timestamptz | YES | | Updated timestamp |

Composite indexes:

- `(user_device_id, revoked_at)`
- `(user_id, revoked_at)`
- `(organization_id, branch_id, user_id, revoked_at)`
- `(expires_at)`

Session lifecycle:

- Active when `revoked_at IS NULL` and `expires_at > CURRENT_TIMESTAMP`.
- Revoked Sessions cannot own active Access Tokens or an active Refresh Token.
- `login_history_id` is nullable and uses `SET NULL` so the Operational History Projection remains independent from Session cleanup.

## Table: `refresh_tokens`

Plain refresh tokens are returned once and never persisted. Only their SHA-256 hash is stored.

Each Session owns exactly one Refresh Token family. The family may contain historical revoked/replaced tokens, but only one Refresh Token may be active at a time.

| Column | Type | Nullable | Constraint / Index | Description |
|---|---|---:|---|---|
| `id` | uuid | NO | PRIMARY KEY | Ordered UUID |
| `session_id` | uuid | NO | FK, INDEX | References `user_sessions.id` |
| `token_hash` | char(64) | NO | UNIQUE | SHA-256 refresh token hash |
| `expires_at` | timestamptz | NO | INDEX | Expiry timestamp |
| `last_used_at` | timestamptz | YES | | Last successful rotation |
| `revoked_at` | timestamptz | YES | INDEX | Revocation timestamp |
| `replaced_by_id` | uuid | YES | FK | Rotation-chain reference |
| `created_at` | timestamptz | YES | | Created timestamp |
| `updated_at` | timestamptz | YES | | Updated timestamp |

Composite indexes:

- `(session_id)`
- `(expires_at)`
- `(revoked_at)`

Unique index:

- `(token_hash)`

Active Refresh Token invariant:

```text
UNIQUE (session_id) WHERE revoked_at IS NULL
```

Rotation revokes the previous active token and creates its replacement inside the same Session and token family.

## Table: `user_devices`

| Column | Type | Nullable | Constraint / Index | Description |
|---|---|---:|---|---|
| `id` | uuid | NO | PRIMARY KEY | Ordered UUID |
| `user_id` | uuid | NO | FK, INDEX | Device owner |
| `organization_id` | uuid | NO | FK, INDEX | Tenant context |
| `branch_id` | uuid | NO | FK, INDEX | Branch context |
| `device_uuid` | varchar(100) | NO | UNIQUE per user | Stable client identifier |
| `device_name` | varchar(150) | YES | | Human-readable name |
| `device_type` | varchar(30) | NO | INDEX | web, mobile, tablet, api |
| `platform` | varchar(50) | YES | | Android, iOS, Windows, etc. |
| `user_agent` | text | YES | | Last user agent |
| `browser` | varchar(100) | YES | | Parsed browser name/version |
| `operating_system` | varchar(100) | YES | | Parsed operating system |
| `ip_address` | inet | YES | | Last IP address |
| `last_login_at` | timestamptz | YES | INDEX | Latest successful login on this device |
| `last_activity_at` | timestamptz | YES | INDEX | Latest authenticated activity |
| `is_trusted` | boolean | NO | INDEX, DEFAULT false | Whether User has trusted this device |
| `revoked_at` | timestamptz | YES | INDEX | Device revocation |
| `created_at` | timestamptz | YES | | Created timestamp |
| `updated_at` | timestamptz | YES | | Updated timestamp |

Unique constraint: `(user_id, device_uuid)`.

Composite index: `(user_id, revoked_at, last_activity_at)`.

API field `is_active` is derived from `revoked_at IS NULL` and is not stored as a duplicate column.

Device lifecycle:

- Device remains registered after Logout and Logout All.
- Device Revocation sets `revoked_at` and revokes every active Session under the Device.
- One Device may own multiple Sessions over time.

## Table: `login_histories`

Operational History Projection of successful and failed authentication events. Fields are immutable after creation except `logout_at`, which permits one controlled `NULL`-to-timestamp mutation under Accepted DD-AUTH-017 and the lifecycle authority of DD-AUTH-007. Canonical immutable evidence remains in the Audit Platform.

| Column | Type | Nullable | Constraint / Index | Description |
|---|---|---:|---|---|
| `id` | uuid | NO | PRIMARY KEY | Ordered UUID |
| `user_id` | uuid | YES | FK, INDEX | Null when identity is unknown |
| `organization_id` | uuid | YES | FK, INDEX | Resolved tenant context |
| `branch_id` | uuid | YES | FK, INDEX | Resolved branch context |
| `device_id` | uuid | YES | FK, INDEX | Recognized device |
| `identifier` | varchar(150) | NO | INDEX | Normalized username/email |
| `login_status` | varchar(20) | NO | INDEX | success or failed |
| `failure_reason` | varchar(100) | YES | | Generic reason code |
| `ip_address` | inet | YES | INDEX | Client IP |
| `browser` | varchar(100) | YES | | Parsed browser name/version |
| `operating_system` | varchar(100) | YES | | Parsed operating system |
| `device_name` | varchar(150) | YES | | Device name captured at event time |
| `country` | varchar(100) | YES | | Country when geolocation is available |
| `city` | varchar(100) | YES | | City when geolocation is available |
| `login_at` | timestamptz | NO | INDEX | Authentication attempt/login timestamp |
| `logout_at` | timestamptz | YES | INDEX | Controlled one-time logout/revocation timestamp under DD-AUTH-017; lifecycle authority remains DD-AUTH-007 |

Composite indexes:

- `(user_id, login_at DESC, id DESC)`
- `(organization_id, branch_id, login_at DESC, id DESC)`
- `(identifier, login_status, login_at DESC, id DESC)`

Accepted DD-AUTH-010 requires `id DESC` as the deterministic tie-breaker. Partial indexes remain excluded until approved query/load evidence exists.

## Password Reset

Managed by Laravel Framework.

Reference:

```text
Illuminate\Auth\Passwords\DatabaseTokenRepository
```

No custom migration. The framework-managed persistent shape is documented for ERD alignment:

| Column | Type | Nullable | Constraint / Index | Description |
|---|---|---:|---|---|
| `email` | varchar(255) | NO | PRIMARY KEY | Normalized credential-recovery lookup key |
| `token` | varchar(255) | NO | | Hashed single-use reset-token verifier; Secret |
| `created_at` | timestamptz | YES | | Issuance timestamp used with configured TTL |

Expiry is derived from `created_at` and the configured Password Reset Token TTL; no separate expiry, consumption, or revocation column is persisted by the framework-managed repository.

Password Reset Token TTL is configured as 15 minutes through the Laravel password broker in `config/auth.php`.

## Token Expiry

| Token | Expiry |
|---|---|
| Sanctum Access Token | 60 minutes |
| Refresh Token | 30 days |
| Password Reset Token | 15 minutes |

All expiry values are environment-backed configuration.

## Foreign Keys

| Child Table | Column | Parent Table | Delete Behavior |
|---|---|---|---|
| `user_devices` | `user_id` | `users.id` | RESTRICT |
| `user_devices` | `organization_id` | `organizations.id` | RESTRICT |
| `user_devices` | `branch_id` | `branches.id` | RESTRICT |
| `user_sessions` | `user_id` | `users.id` | RESTRICT |
| `user_sessions` | `organization_id` | `organizations.id` | RESTRICT |
| `user_sessions` | `branch_id` | `branches.id` | RESTRICT |
| `user_sessions` | `user_device_id` | `user_devices.id` | RESTRICT |
| `user_sessions` | `login_history_id` | `login_histories.id` | SET NULL |
| `personal_access_tokens` | `session_id` | `user_sessions.id` | CASCADE through Session lifecycle |
| `refresh_tokens` | `session_id` | `user_sessions.id` | RESTRICT |
| `refresh_tokens` | `replaced_by_id` | `refresh_tokens.id` | SET NULL |
| `login_histories` | `user_id` | `users.id` | SET NULL |
| `login_histories` | `organization_id` | `organizations.id` | RESTRICT |
| `login_histories` | `branch_id` | `branches.id` | RESTRICT |
| `login_histories` | `device_id` | `user_devices.id` | SET NULL |

`personal_access_tokens.tokenable_type` and `tokenable_id` form the framework polymorphic relationship and therefore do not use a physical foreign key. Authentication ownership is additionally constrained by required `session_id` and the invariant that `tokenable_id` matches the Session owner.

## Revocation Matrix

| Action | Device | Session | Access Token | Refresh Token |
|---|---|---|---|---|
| Logout | Remains registered | Revoke current Session | Revoke current Session token | Revoke current Session active family |
| Logout All | Remains registered | Revoke all User Sessions | Revoke all User tokens | Revoke all User token families |
| Revoke Device | Set `revoked_at` | Revoke every Device Session | Revoke all descendant tokens | Revoke all descendant token families |

Revocation never deletes Login History or canonical Audit Events. Login History retains its approved Operational History Projection lifecycle; canonical Audit Events remain append-only and immutable.

## Retention, Archive, and Cleanup Boundary

- Archive, retention enforcement, cleanup, purge, cryptographic destruction, and Legal Hold eligibility evaluation are asynchronous background lifecycle operations.
- Normal Authentication transactions perform only minimum revocation/expiry transitions and immutable evidence emission; they do not wait for archive or physical cleanup.
- Cleanup is idempotent, retry-safe, resumable, bounded, tenant-aware, and auditable.
- Secret values and hashes are never archived or written to audit/destruction evidence.
- `Hard Deletable` requires retention eligibility, Legal Hold evaluation, authorized execution, and detached immutable evidence where required.
- Cleanup must preserve referential integrity and must never cascade-delete canonical Audit Events or the retained Operational History Projection.

## Account Locking in Redis

```text
auth:failed:{normalized_identifier}:{ip_address} -> integer, TTL 15 minutes
auth:locked:{user_uuid}                         -> boolean, TTL 15 minutes
```

Rules:

- Maximum 5 failed attempts per active window.
- Lock duration is 15 minutes after the fifth failure.
- Successful login clears the failure counter.
- Super Admin is exempt from automatic locking, but failures remain audited.
- Redis must be shared by every application instance.

## Security Constraints

1. Refresh and reset tokens are stored only as hashes.
2. Refresh token rotation revokes the exchanged token.
3. Reuse of a rotated token revokes its entire token family, owning Session, and descendant Access Token under Accepted DD-AUTH-007.
4. Tokens are scoped to user, organization, branch, and device.
5. Inactive User, Organization, or Branch cannot receive tokens.
6. Authentication errors do not reveal whether an identifier exists.
7. Device revocation revokes every access and refresh token for that device.
8. Login History is an Operational History Projection with only the approved `logout_at` one-time mutation; canonical Audit Events are immutable and follow the Accepted platform retention policy.
9. Under Accepted DD-AUTH-018, credential-change revocation does not invoke the `logout_at` mutation; Database Design and ERD structure remain unchanged.
