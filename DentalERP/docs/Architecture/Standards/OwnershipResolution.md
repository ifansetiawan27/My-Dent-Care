# Ownership Resolution Standard

## Purpose

Define ownership and tenant resolution consistently for Authentication, Integration Hub, inbound events, imports, and all ERP domains.

## Resolution States

| State | Definition |
|---|---|
| Resolved | User, Organization, Branch, and owning entity are validated and authoritative. |
| Partially Resolved | Some ownership dimensions are known; unresolved dimensions are explicitly nullable with reason. |
| Unresolved | Identity/tenant cannot yet be established, such as a failed login for an unknown identifier. |

## Ownership Matrix

Every entity with ownership exceptions must define:

| Field | Normal Requirement | Allowed Resolution State | Nullable | Resolution Trigger | Exposure |
|---|---|---|---:|---|---|

## Rules

1. Tenant-scoped business records normally require Resolved ownership.
2. Nullable ownership is allowed only for explicitly documented pre-resolution/security/event scenarios.
3. Partially Resolved or Unresolved records must preserve available evidence without fabricating tenant IDs.
4. Unresolved ownership must not grant data access or authorization context.
5. Resolution transitions must be deterministic, auditable, and documented as allowed mutations.
6. Cross-organization actions require explicit Platform authority and target tenant context.
7. Ownership exceptions must appear in Decision Records, ERD, Database Design, API exposure mapping, and tests.
