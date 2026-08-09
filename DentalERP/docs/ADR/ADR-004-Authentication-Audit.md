# ADR-004

## Title

User Authentication Audit Strategy

## Status

Superseded

## Superseded By

`ADR-006-Authentication-Audit-Evidence-and-History-Projection.md`

Supersession scope: ADR-004 is superseded only where it treats Login History as unconditionally immutable canonical audit evidence. Its durable-audit and transient-state separation remains preserved as historical rationale. ADR-006 is the active authority distinguishing immutable canonical Audit Events from the Login History Operational History Projection.

## Context

Authentication needs both durable auditability and fast transient state management. These concerns have different consistency and retention requirements.

## Decision

Login History is stored in PostgreSQL.

Authentication transient state is stored in Redis.

Audit records are immutable.

Database stores:

- Successful and failed login history.
- Logout time.
- Device metadata required for investigation.
- Token and device revocation audit events through the Audit Platform.

Redis stores:

- Failed login counters.
- Lock TTL.
- Temporary account lock state.

## Reasons

- PostgreSQL provides durable, queryable, long-term authentication history.
- Redis provides fast, atomic, automatically expiring transient state.
- Separating durable audit from transient state prevents unnecessary database writes during login attacks.

## Consequences

- `login_histories` is immutable and follows the Audit Platform retention policy.
- Authentication state in Redis may expire and is not considered audit evidence.
- Every login, logout, and revocation operation must emit an audit event.
- Database lock columns are not added to `users`.
