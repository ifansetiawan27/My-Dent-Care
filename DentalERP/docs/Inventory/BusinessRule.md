# Phase 18 — Inventory Business Rules

**Date:** 2026-08-17 | **Phase:** 18 — Inventory | **Status:** STEP_18_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-INV-001 | Inventory item must belong to an Organization | INV-REQ-002 |
| BR-INV-002 | Branch reference is optional — SET NULL on branch delete | INV-REQ-003 |
| BR-INV-003 | Category reference is optional — SET NULL on category delete | INV-REQ-004 |
| BR-INV-004 | Item code must be unique across the system | INV-REQ-005 |
| BR-INV-005 | Item name is required | INV-REQ-006 |
| BR-INV-006 | Unit of measurement is required | INV-REQ-008 |
| BR-INV-007 | Quantity must be non-negative | INV-REQ-009 |
| BR-INV-008 | Minimum quantity must be non-negative | INV-REQ-010 |
| BR-INV-009 | Unit price must be non-negative when provided | INV-REQ-011 |
| BR-INV-010 | Default is_active is true | INV-REQ-012 |
| BR-INV-011 | Toggle active changes is_active to opposite value | INV-REQ-020 |
| BR-INV-012 | List queries are organization-scoped | INV-REQ-023 |
| BR-INV-013 | Organization-scoped listing — user cannot see items from other orgs | INV-REQ-023 |
| BR-INV-014 | Soft delete only — no hard delete | INV-REQ-019 |
| BR-INV-015 | Audit trail auto-populated via HasAudit trait | INV-REQ-021 |
| BR-INV-016 | Authorization: Super Admin full access, Inventory Staff/Admin CRUD, Doctor read | INV-REQ-022 |
| BR-INV-017 | API response uses ApiResponse envelope | INV-REQ-024 |
| BR-INV-018 | Routes versioned under /api/v1/inventory-items | INV-REQ-025 |
| BR-INV-019 | Item code duplication check on create | INV-REQ-005 |
| BR-INV-020 | Item code duplication check on update (excluding self) | INV-REQ-005 |

### Active/Inactive Rules

| Condition | Rule |
|---|---|
| is_active = false | Item still visible in list but marked inactive |
| Toggle | Requires write permission |
| Inactive items | Can still be referenced by other domains |

### Duplicate Code Rules

| Operation | Behavior |
|---|---|
| Create with existing code | BusinessException: "Item code already exists." |
| Update with existing code (different item) | BusinessException: "Item code already exists." |
| Update with same code (same item) | Allowed — no change |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| organization_id | No | Internal transfer |
| item_code | No | Can be updated (with uniqueness check) |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted items are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Inventory Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Toggle | All | Org-scoped | Org-scoped | ❌ | ❌ |