# ADR Index

| ADR | Judul | Status | Berdampak ke |
|---|---|---|---|
| [ADR-001](ADR-001-Authentication-Lockout.md) | Authentication Lockout Strategy | Accepted | Business Rules, ERD, Redis, Service |
| [ADR-002](ADR-002-Authentication-Token.md) | Authentication Token Strategy | Accepted | OpenAPI, Database, Service |
| [ADR-003](ADR-003-Password-Reset.md) | Password Reset Strategy | Accepted | ERD, Migration, OpenAPI |
| [ADR-004](ADR-004-Authentication-Audit.md) | User Authentication Audit Strategy | Superseded | Superseded by ADR-006; retained as historical audit-strategy rationale |
| [ADR-005](ADR-005-Platform-Lifecycle-Audit-Policy.md) | Platform Lifecycle and Audit Policy | Accepted | Platform governance, lifecycle, audit, retention, archive, Legal Hold, cleanup |
| [ADR-006](ADR-006-Authentication-Audit-Evidence-and-History-Projection.md) | Authentication Audit Evidence and Login History Projection Authority | Accepted | Canonical Audit Events, Login History projection, `logout_at` mutation, ADR-004 supersession |

## Usage

- Review impacted ADRs before changing Requirements, Business Rules, ERD, OpenAPI, Migration, or Service behavior.
- Quality Gate fails when implementation contradicts an Accepted ADR.
- A material decision change requires a new ADR and a Superseded reference.
