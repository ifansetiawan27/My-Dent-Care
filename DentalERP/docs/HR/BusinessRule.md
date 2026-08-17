# Phase 23 — HR Business Rules

**Date:** 2026-08-17 | **Phase:** 23 — HR | **Status:** STEP_23_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-HR-001 | HR record must belong to an Organization | HR-REQ-002 |
| BR-HR-002 | Employee reference is optional — SET NULL on employee delete | HR-REQ-003 |
| BR-HR-003 | Record type is required | HR-REQ-004 |
| BR-HR-004 | Status defaults to 'active' on create | HR-REQ-005 |
| BR-HR-005 | Effective date is required | HR-REQ-006 |
| BR-HR-006 | End date must be after effective date when provided | HR-REQ-007 |
| BR-HR-007 | List queries are organization-scoped | HR-REQ-020 |
| BR-HR-008 | Organization-scoped listing — user cannot see records from other orgs | HR-REQ-020 |
| BR-HR-009 | Soft delete only — no hard delete | HR-REQ-016 |
| BR-HR-010 | Audit trail auto-populated via HasAudit trait | HR-REQ-018 |
| BR-HR-011 | Authorization: Super Admin full access, HR Manager/Admin CRUD, Doctor read | HR-REQ-019 |
| BR-HR-012 | API response uses ApiResponse envelope | HR-REQ-021 |
| BR-HR-013 | Routes versioned under /api/v1/hr-records | HR-REQ-022 |
| BR-HR-014 | Status transitions must follow valid lifecycle | HR-REQ-017 |
| BR-HR-015 | Archived is a terminal state | HR-REQ-025 |

### Status Transition Rules

| From | Allowed Transitions |
|---|---|
| active | inactive, archived |
| inactive | active, archived |
| archived | (terminal) |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| record_type | No | Can be updated |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted records are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | HR Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |