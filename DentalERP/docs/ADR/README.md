# Architecture Decision Register

This directory is the authoritative register for Architecture Decision Records (ADRs) in Dental ERP Enterprise.

## Purpose

ADRs explain why significant architecture decisions were made, the alternatives implied by the decision, and the consequences that implementation must follow.

ADRs answer questions such as:

- Why is Redis used for authentication lockout?
- Why are refresh tokens stored in PostgreSQL?
- Why is Laravel Sanctum used for access tokens?
- Why does password reset use Laravel's default broker schema?
- Why is durable audit data separated from transient authentication state?

## File Naming

```text
ADR-NNN-Short-Decision-Title.md
```

Example:

```text
ADR-001-Authentication-Lockout.md
```

## Required Sections

Every ADR must include:

1. ADR identifier.
2. Title.
3. Status.
4. Context.
5. Decision.
6. Reasons.
7. Consequences.

## Status Lifecycle

- Proposed: under review and not yet binding.
- Accepted: approved and binding.
- Deprecated: retained for history but no longer recommended.
- Superseded: replaced by a newer ADR; the replacement must be referenced.
- Rejected: considered but not adopted.

Accepted ADRs are immutable in intent. If the decision changes materially, create a new ADR and mark the old ADR as Superseded instead of rewriting history.

## Register

See [index.md](index.md) for the current ADR inventory and impact map.
