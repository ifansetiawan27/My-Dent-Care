# Phase 21 — Procurement Business Rules

**Date:** 2026-08-17 | **Phase:** 21 — Procurement | **Status:** STEP_21_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-PROC-001 | Procurement order must belong to an Organization | PROC-REQ-002 |
| BR-PROC-002 | Branch reference is optional — SET NULL on branch delete | PROC-REQ-003 |
| BR-PROC-003 | Supplier reference is optional — SET NULL on supplier delete | PROC-REQ-004 |
| BR-PROC-004 | Order number must be unique across the system | PROC-REQ-005 |
| BR-PROC-005 | Status defaults to 'pending' on create | PROC-REQ-006 |
| BR-PROC-006 | Order date is required | PROC-REQ-007 |
| BR-PROC-007 | Total amount must be non-negative | PROC-REQ-009 |
| BR-PROC-008 | List queries are organization-scoped | PROC-REQ-022 |
| BR-PROC-009 | Organization-scoped listing — user cannot see orders from other orgs | PROC-REQ-022 |
| BR-PROC-010 | Soft delete only — no hard delete | PROC-REQ-018 |
| BR-PROC-011 | Audit trail auto-populated via HasAudit trait | PROC-REQ-020 |
| BR-PROC-012 | Authorization: Super Admin full access, Procurement Staff/Admin CRUD, Doctor read | PROC-REQ-021 |
| BR-PROC-013 | API response uses ApiResponse envelope | PROC-REQ-023 |
| BR-PROC-014 | Routes versioned under /api/v1/procurement-orders | PROC-REQ-024 |
| BR-PROC-015 | Order number duplication check on create | PROC-REQ-005 |
| BR-PROC-016 | Order number duplication check on update (excluding self) | PROC-REQ-005 |
| BR-PROC-017 | Status transitions must follow valid lifecycle | PROC-REQ-019 |
| BR-PROC-018 | Cancelled and received are terminal states | PROC-REQ-019 |
| BR-PROC-019 | Order can be cancelled from pending, approved, or ordered states | PROC-REQ-025 |

### Status Transition Rules

| From | Allowed Transitions |
|---|---|
| pending | approved, cancelled |
| approved | ordered, cancelled |
| ordered | received, cancelled |
| received | (terminal) |
| cancelled | (terminal) |

### Duplicate Order Number Rules

| Operation | Behavior |
|---|---|
| Create with existing order_number | BusinessException: "Order number already exists." |
| Update with existing order_number (different order) | BusinessException: "Order number already exists." |
| Update with same order_number (same order) | Allowed — no change |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| order_number | No | Can be updated (with uniqueness check) |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted orders are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Procurement Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |