# Phase 22 — Asset Business Rules

**Date:** 2026-08-17 | **Phase:** 22 — Asset | **Status:** STEP_22_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-ASST-001 | Asset must belong to an Organization | ASST-REQ-002 |
| BR-ASST-002 | Branch reference is optional — SET NULL on branch delete | ASST-REQ-003 |
| BR-ASST-003 | Category reference is optional — SET NULL on category delete | ASST-REQ-004 |
| BR-ASST-004 | Asset code must be unique across the system | ASST-REQ-005 |
| BR-ASST-005 | Asset name is required | ASST-REQ-006 |
| BR-ASST-006 | Status defaults to 'active' on create | ASST-REQ-010 |
| BR-ASST-007 | Purchase price must be non-negative when provided | ASST-REQ-009 |
| BR-ASST-008 | List queries are organization-scoped | ASST-REQ-023 |
| BR-ASST-009 | Organization-scoped listing — user cannot see assets from other orgs | ASST-REQ-023 |
| BR-ASST-010 | Soft delete only — no hard delete | ASST-REQ-019 |
| BR-ASST-011 | Audit trail auto-populated via HasAudit trait | ASST-REQ-021 |
| BR-ASST-012 | Authorization: Super Admin full access, Asset Manager/Admin CRUD, Doctor read | ASST-REQ-022 |
| BR-ASST-013 | API response uses ApiResponse envelope | ASST-REQ-024 |
| BR-ASST-014 | Routes versioned under /api/v1/assets | ASST-REQ-025 |
| BR-ASST-015 | Asset code duplication check on create | ASST-REQ-005 |
| BR-ASST-016 | Asset code duplication check on update (excluding self) | ASST-REQ-005 |
| BR-ASST-017 | Status transitions must follow valid lifecycle | ASST-REQ-020 |
| BR-ASST-018 | Disposed is a terminal state | ASST-REQ-020 |

### Status Transition Rules

| From | Allowed Transitions |
|---|---|
| active | maintenance, retired, disposed |
| maintenance | active, retired, disposed |
| retired | disposed |
| disposed | (terminal) |

### Duplicate Code Rules

| Operation | Behavior |
|---|---|
| Create with existing asset_code | BusinessException: "Asset code already exists." |
| Update with existing asset_code (different asset) | BusinessException: "Asset code already exists." |
| Update with same asset_code (same asset) | Allowed — no change |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| asset_code | No | Can be updated (with uniqueness check) |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted assets are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Asset Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |