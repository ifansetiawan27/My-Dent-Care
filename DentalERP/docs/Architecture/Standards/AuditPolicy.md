# Audit Policy Standard

## Purpose

Define durable audit evidence separately from operational logs and mutable projections.

## Data Categories

Every persistent object has exactly one primary Data Category under ADR-005. Data Category is object-level and remains separate from Field Classification, Exposure Classification, and Lifecycle Semantics.

| Primary Data Category | Mutation Policy | Purpose |
|---|---|---|
| Immutable Audit Event | Append-only and immutable | Canonical compliance and forensic evidence |
| Operational History Projection | Controlled mutations allowed only when explicitly documented | Query-friendly lifecycle view |
| Mutable Operational State | Approved mutable transitions only | Current operational workflow state |
| Revocable Security Data | Revocable; expiry may be an independent secondary semantic | Security data primarily invalidated by explicit revocation |
| Expiring Security Data | Expiring; revocation may be an independent secondary semantic | Security data primarily invalidated by time or approved single-use completion |
| Business Record | Mutable Operational State and Soft Deletable by default | Ordinary domain business workflow |

Non-canonical operational categories remain governed as follows:

| Category | Mutation Policy | Purpose |
|---|---|---|
| Technical Log | Retention-managed; not business audit evidence | Diagnostics and operations |
| Transient State | Expiring and non-auditative | Rate limits, locks, caches |

## Required Audit Context

- Event type.
- Actor User.
- Target entity/User when applicable.
- Organization and Branch context when resolved.
- Timestamp.
- Correlation/request ID.
- IP and user agent when available.
- Reason for administrative/high-risk actions.
- Outcome.

## Rules

1. Passwords, token values, and password/token hashes never enter audit payloads.
2. Audit events are immutable and never soft-deleted as ordinary domain records.
3. Operational projections must not be called immutable if controlled updates occur.
4. Every lifecycle mutation requiring evidence emits an audit event.
5. Administrative cross-tenant actions require explicit actor/target tenant context.
6. Retention requirements must be documented per event category.
7. Audit-policy exceptions to global soft-delete rules require an Accepted Decision/ADR.
8. Primary Data Categories are mutually exclusive and selected by the object's primary architectural purpose.
9. Operational History Projections never become canonical audit evidence.
10. Secret fields never enter Audit Events, archives, or destruction evidence.
