# Decision Lifecycle Standard

## Purpose

Preserve a stable, auditable history of design and architecture decisions.

## Identifier Policy

- Every Decision Record receives a unique sequential ID within its domain: `DD-{DOMAIN}-NNN`.
- Do not use version suffixes such as `-v2`, `-v3`, or `-final`.
- A replacement decision receives the next unused unique ID and declares `Supersedes: DD-{DOMAIN}-NNN`.

## Status Lifecycle

```text
TBD / Proposed
      -> Accepted
      -> Superseded (only through a new Decision Record)

TBD / Proposed
      -> Rejected
```

## Immutability

1. Accepted Decision Records are immutable.
2. Do not edit an Accepted decision to change its policy, classification, lifecycle, or consequences.
3. Downstream artifacts may be synchronized to an Accepted decision without editing the decision.
4. A material correction creates a new Decision Record and marks the old record Superseded in the register.
5. Superseding records link both directions and list migration/synchronization consequences.

## Review Requirements

A Decision may become Accepted only after all applicable reviews PASS:

- Architecture.
- Security.
- Data.
- API Contract.
- Performance.
- Audit/Compliance.

The Decision register must always reflect the actual status.
