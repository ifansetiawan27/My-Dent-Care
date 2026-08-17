# Phase 20 — Laboratory Business Rules

**Date:** 2026-08-17 | **Phase:** 20 — Laboratory | **Status:** STEP_20_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-LAB-001 | Lab order must belong to an Organization | LAB-REQ-002 |
| BR-LAB-002 | Patient must exist and be valid | LAB-REQ-003 |
| BR-LAB-003 | Patient DELETE is RESTRICT — cannot delete patient with active lab orders | LAB-REQ-003 |
| BR-LAB-004 | Doctor FK is optional — SET NULL on doctor delete | LAB-REQ-004 |
| BR-LAB-005 | Order number must be unique per organization | LAB-REQ-005 |
| BR-LAB-006 | Category FK is optional — SET NULL on category delete | LAB-REQ-006 |
| BR-LAB-007 | Status must be one of: pending, in_progress, completed, cancelled | LAB-REQ-007 |
| BR-LAB-008 | Status transition: pending → in_progress → completed | LAB-REQ-007 |
| BR-LAB-009 | Status transition: pending → cancelled | LAB-REQ-007 |
| BR-LAB-010 | Completed lab order cannot transition to any other status | LAB-REQ-007 |
| BR-LAB-011 | Cancelled lab order cannot transition to any other status | LAB-REQ-007 |
| BR-LAB-012 | Results must be valid JSON when provided | LAB-REQ-009 |
| BR-LAB-013 | Ordered_at must be a valid date | LAB-REQ-010 |
| BR-LAB-014 | Completed_at must be a valid date when provided | LAB-REQ-011 |
| BR-LAB-015 | List queries are organization-scoped | LAB-REQ-023 |
| BR-LAB-016 | Organization-scoped listing — user cannot see lab orders from other orgs | LAB-REQ-023 |
| BR-LAB-017 | Soft delete only — no hard delete | LAB-REQ-019 |
| BR-LAB-018 | Audit trail auto-populated via HasAudit trait | LAB-REQ-021 |
| BR-LAB-019 | Authorization: Super Admin full access, Organization Admin org-scoped, Doctor own orders | LAB-REQ-022 |
| BR-LAB-020 | API response uses ApiResponse envelope | LAB-REQ-024 |
| BR-LAB-021 | Routes versioned under /api/v1/lab-orders | LAB-REQ-025 |

### Status Lifecycle

```
                    ┌─────────┐
                    │ pending │
                    └────┬────┘
                    ┌────┴─────┐
                    │          │
                    ▼          ▼
              ┌──────────┐  ┌───────────┐
              │in_progress│  │ cancelled │
              └─────┬────┘  └───────────┘
                    │
                    ▼
              ┌──────────┐
              │completed │
              └──────────┘
```

### Status Transition Rules

| From | To | Allowed | Rule |
|---|---|---|---|
| pending | in_progress | ✅ | BR-LAB-008 |
| pending | cancelled | ✅ | BR-LAB-009 |
| in_progress | completed | ✅ | BR-LAB-008 |
| in_progress | cancelled | ✅ | OPEN DECISION — allow cancellation during lab work? |
| completed | any | ❌ | BR-LAB-010 |
| cancelled | any | ❌ | BR-LAB-011 |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| patient_id | No | May be reassigned (OPEN DECISION) |
| organization_id | No | Internal transfer |
| order_number | No | May be corrected |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Completed lab orders cannot be deleted (OPEN DECISION)
- Soft-deleted lab orders are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Doctor | Lab Tech | Staff | Patient |
|---|---|---|---|---|---|---|
| List | All | Org-scoped | Own | Org-scoped | Org-scoped | Own |
| Show | All | Org-scoped | Own | Org-scoped | Org-scoped | Own |
| Create | All | Org-scoped | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Own | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Own | ❌ | ❌ | ❌ |