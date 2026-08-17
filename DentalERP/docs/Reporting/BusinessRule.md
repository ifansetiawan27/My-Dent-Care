# Phase 25 — Reporting Business Rules

**Date:** 2026-08-17 | **Phase:** 25 — Reporting | **Status:** STEP_25_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-RPT-001 | Report must belong to an Organization | RPT-REQ-002 |
| BR-RPT-002 | Report type is required | RPT-REQ-003 |
| BR-RPT-003 | Report name is required | RPT-REQ-004 |
| BR-RPT-004 | Status defaults to 'generated' on create | RPT-REQ-007 |
| BR-RPT-005 | Report date is required | RPT-REQ-008 |
| BR-RPT-006 | List queries are organization-scoped | RPT-REQ-019 |
| BR-RPT-007 | Organization-scoped listing — user cannot see reports from other orgs | RPT-REQ-019 |
| BR-RPT-008 | Soft delete only — no hard delete | RPT-REQ-015 |
| BR-RPT-009 | Audit trail auto-populated via HasAudit trait | RPT-REQ-017 |
| BR-RPT-010 | Authorization: Super Admin full access, Report Manager/Admin CRUD, Doctor read | RPT-REQ-018 |
| BR-RPT-011 | API response uses ApiResponse envelope | RPT-REQ-020 |
| BR-RPT-012 | Routes versioned under /api/v1/reports | RPT-REQ-021 |
| BR-RPT-013 | Status transitions: generated ↔ archived (both directions allowed) | RPT-REQ-016 |

### Status Transition Rules

| From | Allowed Transitions |
|---|---|
| generated | archived |
| archived | generated |

No terminal states — both directions are allowed.

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| report_type | No | Can be updated |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted reports are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Report Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |