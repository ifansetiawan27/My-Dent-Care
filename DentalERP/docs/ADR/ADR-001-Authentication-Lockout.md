# ADR-001

## Title

Authentication Lockout Strategy

## Status

Accepted

## Context

Authentication must limit failed login attempts consistently across horizontally scaled application instances without adding synchronous database writes to the login path.

## Decision

Failed login attempts and temporary account locks are stored in Redis.

Redis manages:

- Failed-attempt counter.
- Counter and lock TTL.
- Temporary account lock state.

The approved lockout policy is five failed attempts followed by a 15-minute lock for non-Super-Admin accounts.

## Reasons

- Faster atomic counter operations.
- No database write on every failed login.
- Automatic expiration through Redis TTL.
- Shared state supports horizontal application scaling.
- Redis primitives support race-safe increments and expiry.

## Consequences

The `users` table does not store:

- `failed_login_attempt`.
- `locked_until`.

Redis availability is required for consistent lockout behavior. Failed-login and lockout events remain persisted in immutable Login History and Audit records; Redis stores only transient authentication state.
