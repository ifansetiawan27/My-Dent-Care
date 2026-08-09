# Lifecycle Semantics Standard

## Purpose

Define allowed mutations and lifecycle transitions for every entity and field.

## Lifecycle Categories

| Category | Meaning |
|---|---|
| Immutable | Never updated after creation. |
| Append Only | New events/rows may be added; existing rows never change. |
| Controlled One-Time Mutation | One approved transition from null/initial to final value. |
| Mutable Operational State | May change through approved state transitions. |
| Revocable | Becomes inactive through a revocation timestamp/state. |
| Expiring | Becomes invalid after configured time. |
| Soft Deletable | Retained with `deleted_at`. |
| Hard Deletable | Physical deletion allowed only by an accepted lifecycle/retention decision. |

## Allowed Mutations Matrix

Every affected entity must define:

| Field | Initial State | Allowed Mutation | Trigger | Final State | Repeatable | Audit Event |
|---|---|---|---|---|---:|---|

Examples of controlled lifecycle fields:

- `logout_at`: `NULL` -> timestamp, once, on approved logout/revocation semantics.
- `last_login_at`: nullable -> latest successful login timestamp, repeatable.
- `last_activity_at`: nullable -> latest tracked activity timestamp, repeatable.
- `revoked_at`: `NULL` -> timestamp, once, on revocation.

## Rules

1. “Immutable” must not coexist with undocumented field updates.
2. Allowed mutation fields must be listed explicitly; all unlisted fields inherit the entity default.
3. State transitions must appear in Business Rules, Flow, Database Design, and tests.
4. Expiry and revocation are different states and must not be conflated.
5. Retention and deletion behavior require explicit triggers and audit evidence.
6. Lifecycle changes that alter an Accepted decision require a superseding Decision Record.
7. Business Records use Soft Deletable by default.
8. Immutable Audit Events use Append Only and Immutable and are not Soft Deletable.
9. Revocable Security Data and Expiring Security Data may become Hard Deletable only after an Accepted lifecycle/retention policy, retention eligibility, Legal Hold evaluation, authorization, and immutable evidence.
10. Data Category, Field Classification, Exposure Classification, and Lifecycle Semantics are independent dimensions.
11. Archive, retention enforcement, cleanup, purge, cryptographic destruction, and lifecycle Legal Hold evaluation run as idempotent, retry-safe, resumable, bounded background operations and do not block normal business transactions.
