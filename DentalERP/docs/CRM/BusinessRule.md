# Phase 24 — CRM Business Rules

**Date:** 2026-08-17 | **Phase:** 24 — CRM | **Status:** STEP_24_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-CRM-001 | CRM contact must belong to an Organization | CRM-REQ-002 |
| BR-CRM-002 | Patient reference is optional — SET NULL on patient delete | CRM-REQ-003 |
| BR-CRM-003 | Contact type is required | CRM-REQ-004 |
| BR-CRM-004 | Status defaults to 'new' on create | CRM-REQ-008 |
| BR-CRM-005 | List queries are organization-scoped | CRM-REQ-021 |
| BR-CRM-006 | Organization-scoped listing — user cannot see contacts from other orgs | CRM-REQ-021 |
| BR-CRM-007 | Soft delete only — no hard delete | CRM-REQ-017 |
| BR-CRM-008 | Audit trail auto-populated via HasAudit trait | CRM-REQ-019 |
| BR-CRM-009 | Authorization: Super Admin full access, CRM Staff/Admin CRUD, Doctor read | CRM-REQ-020 |
| BR-CRM-010 | API response uses ApiResponse envelope | CRM-REQ-022 |
| BR-CRM-011 | Routes versioned under /api/v1/crm-contacts | CRM-REQ-023 |
| BR-CRM-012 | Status transitions must follow valid lifecycle | CRM-REQ-018 |
| BR-CRM-013 | Closed is a terminal state | CRM-REQ-025 |

### Status Transition Rules

| From | Allowed Transitions |
|---|---|
| new | in_progress, resolved, closed |
| in_progress | resolved, closed |
| resolved | closed |
| closed | (terminal) |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| contact_type | No | Can be updated |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted contacts are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | CRM Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |