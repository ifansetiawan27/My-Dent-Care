# Authentication Design Decision Index

These records document Authentication design decisions and unresolved drift. Accepted records are authoritative within their scope; Proposed/TBD records do not authorize implementation changes.

| Decision | Topic | Status | Drift Finding |
|---|---|---|---|
| [DD-AUTH-001](DD-AUTH-001.md) | Access Token to Device Linkage | Accepted | DD-AUTH-001 resolved |
| [DD-AUTH-002](DD-AUTH-002.md) | Argon2id vs bcrypt | Accepted | DD-AUTH-002 resolved; downstream verification pending |
| [DD-AUTH-003](DD-AUTH-003.md) | Super Admin Session Scope | Accepted | DD-AUTH-003 resolved; downstream verification pending |
| [DD-AUTH-004](DD-AUTH-004.md) | Change Password Session Behavior | Accepted | DD-AUTH-004 resolved and synchronized |
| [DD-AUTH-005](DD-AUTH-005.md) | OpenAPI and ERD Nullability | Superseded | Superseded by DD-AUTH-017 |
| [DD-AUTH-006](DD-AUTH-006.md) | Device List Pagination | Accepted | DD-AUTH-006 |
| [DD-AUTH-007](DD-AUTH-007.md) | Audit & Operational Data Lifecycle Strategy | Accepted | Source decision for Accepted ADR-005; prerequisite satisfied |
| [DD-AUTH-008](DD-AUTH-008.md) | Remember Me Behavior | Accepted | DD-AUTH-008 |
| [DD-AUTH-010](DD-AUTH-010.md) | Login History Index Strategy | Accepted | Option C; deterministic `login_at DESC, id DESC` indexes synchronized |
| [DD-AUTH-017](DD-AUTH-017.md) | Authentication Field Classification and Nullability | Accepted | Active field classification, exposure, nullability, and field-governance authority; supersedes DD-AUTH-005 |
| [DD-AUTH-018](DD-AUTH-018.md) | Credential-Change Revocation and Login History Projection Policy | Accepted | Specific precedence authority; Password Change does not mutate Login History `logout_at` |

## Governance

Each record uses the same decision format:

1. Problem.
2. Current State.
3. Options.
4. Decision.
5. Consequences.
6. Affected Documents.
7. Review Status.
8. Traceability where applicable.

When approved:

- Set the Decision and Consequences.
- Change Review Status to PASS/Approved.
- Update all affected design artifacts.
- Run Drift Detection again.
- Create or supersede an ADR when the decision is architecture-significant.

## Critical Dependency Path

See [DependencyGraph.md](DependencyGraph.md).

```text
DD-AUTH-001 -> DD-AUTH-004 -> DD-AUTH-018
-> Sequence Diagram -> Flowchart
-> Architecture Review -> Drift Detection -> Design Freeze

DD-AUTH-008 (Accepted, independent)

DD-AUTH-007 (Accepted) -> ADR-005 (Accepted)
-> DD-AUTH-017 reviews -> DD-AUTH-017 Accepted
-> DD-AUTH-005 Superseded

DD-AUTH-004 + DD-AUTH-007 + DD-AUTH-017
-> DD-AUTH-018 Accepted
-> Credential-change projection precedence synchronized
```
