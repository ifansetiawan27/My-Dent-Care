# Phase 19 — Pharmacy Business Rules

**Date:** 2026-08-17 | **Phase:** 19 — Pharmacy | **Status:** STEP_19_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-PHARM-001 | Pharmacy item must belong to an Organization | PHARM-REQ-002 |
| BR-PHARM-002 | Branch reference is optional — SET NULL on branch delete | PHARM-REQ-003 |
| BR-PHARM-003 | Drug code must be unique across the system | PHARM-REQ-004 |
| BR-PHARM-004 | Drug name is required | PHARM-REQ-005 |
| BR-PHARM-005 | Category is optional | PHARM-REQ-006 |
| BR-PHARM-006 | Quantity must be non-negative | PHARM-REQ-007 |
| BR-PHARM-007 | Unit of measurement is optional | PHARM-REQ-008 |
| BR-PHARM-008 | Unit price must be non-negative when provided | PHARM-REQ-009 |
| BR-PHARM-009 | Expiry date must be a valid date when provided | PHARM-REQ-010 |
| BR-PHARM-010 | Default is_active is true | PHARM-REQ-012 |
| BR-PHARM-011 | Toggle active changes is_active to opposite value | PHARM-REQ-020 |
| BR-PHARM-012 | List queries are organization-scoped | PHARM-REQ-023 |
| BR-PHARM-013 | Organization-scoped listing — user cannot see items from other orgs | PHARM-REQ-023 |
| BR-PHARM-014 | Soft delete only — no hard delete | PHARM-REQ-019 |
| BR-PHARM-015 | Audit trail auto-populated via HasAudit trait | PHARM-REQ-021 |
| BR-PHARM-016 | Authorization: Super Admin full access, Pharmacy Staff/Admin CRUD, Doctor read | PHARM-REQ-022 |
| BR-PHARM-017 | API response uses ApiResponse envelope | PHARM-REQ-024 |
| BR-PHARM-018 | Routes versioned under /api/v1/pharmacy-items | PHARM-REQ-025 |
| BR-PHARM-019 | Drug code duplication check on create | PHARM-REQ-004 |
| BR-PHARM-020 | Drug code duplication check on update (excluding self) | PHARM-REQ-004 |

### Active/Inactive Rules

| Condition | Rule |
|---|---|
| is_active = false | Item still visible in list but marked inactive |
| Toggle | Requires write permission |
| Inactive items | Can still be referenced by other domains |

### Duplicate Code Rules

| Operation | Behavior |
|---|---|
| Create with existing drug_code | BusinessException: "Drug code already exists." |
| Update with existing drug_code (different item) | BusinessException: "Drug code already exists." |
| Update with same drug_code (same item) | Allowed — no change |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| organization_id | No | Internal transfer |
| drug_code | No | Can be updated (with uniqueness check) |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted items are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Pharmacy Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Toggle | All | Org-scoped | Org-scoped | ❌ | ❌ |