# Phase 26 — Dashboard Business Rules

**Date:** 2026-08-17 | **Phase:** 26 — Dashboard | **Status:** STEP_26_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-DASH-001 | Dashboard must belong to an Organization | DASH-REQ-002 |
| BR-DASH-002 | User reference is optional — SET NULL on user delete | DASH-REQ-003 |
| BR-DASH-003 | Dashboard name is required | DASH-REQ-004 |
| BR-DASH-004 | Default dashboard defaults to false | DASH-REQ-007 |
| BR-DASH-005 | List queries are organization-scoped | DASH-REQ-017 |
| BR-DASH-006 | Organization-scoped listing — user cannot see dashboards from other orgs | DASH-REQ-017 |
| BR-DASH-007 | Soft delete only — no hard delete | DASH-REQ-014 |
| BR-DASH-008 | Audit trail auto-populated via HasAudit trait | DASH-REQ-015 |
| BR-DASH-009 | Authorization: Super Admin full access, Dashboard Manager/Admin CRUD, Doctor read | DASH-REQ-016 |
| BR-DASH-010 | API response uses ApiResponse envelope | DASH-REQ-018 |
| BR-DASH-011 | Routes versioned under /api/v1/dashboards | DASH-REQ-019 |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted dashboards are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Dashboard Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |