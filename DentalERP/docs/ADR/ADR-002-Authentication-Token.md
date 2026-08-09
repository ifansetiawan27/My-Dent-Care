# ADR-002

## Title

Authentication Token Strategy

## Status

Accepted

## Context

Dental ERP requires revocable API access, multi-device sessions, refresh-token rotation, and tenant-aware authentication.

## Decision

Use Laravel Sanctum for Access Tokens.

### Authentication Hierarchy

```text
User
  └── Device
        └── Session
              ├── Access Token (Sanctum)
              └── Refresh Token
```

### Session Boundary

`user_sessions` represents an authenticated login.

Device represents hardware/browser identity.

Session lifecycle is independent from Device lifecycle.

Access Token TTL:

- 60 minutes.

Refresh Tokens:

- Stored in the database only as cryptographic hashes.
- Valid for 30 days.
- Single-use.
- Rotation enabled.
- The old token becomes invalid immediately after a successful refresh.
- Reuse of a rotated token revokes its token family.
- Every Authentication-issued Access Token and Refresh Token belongs to exactly one `user_session` through `session_id`.
- Refresh Token rotation remains inside the same Session and token family.

## Reasons

- Sanctum integrates natively with Laravel authentication and middleware.
- Database-backed refresh tokens support device sessions, revocation, auditability, and rotation chains.
- Short-lived access tokens limit exposure after compromise.

## Consequences

- Sanctum's `personal_access_tokens` migration must support User UUIDs.
- A custom `refresh_tokens` table and token repository are required.
- Access and refresh tokens must never be logged or stored in plaintext.
- Refresh operations must revalidate User, Organization, Branch, and Device status.
- `user_devices` represents long-lived Device identity; `user_sessions` represents authenticated Session lifecycle.
- Sanctum `personal_access_tokens` is extended with indexed `session_id` referencing `user_sessions.id`.
- `refresh_tokens` references `user_sessions.id` through `session_id`, not `user_devices.id` directly.
- Logout revokes the current Session and descendant tokens.
- Logout All revokes all Sessions owned by the User and descendant tokens.
- Device Revocation revokes every Session under the Device and descendant tokens.
