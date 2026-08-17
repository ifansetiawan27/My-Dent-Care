# Phase 17 — Billing Business Rules

**Date:** 2026-08-17 | **Phase:** 17 — Billing | **Status:** STEP_17_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-BILL-001 | Invoice must belong to an Organization | BILL-REQ-002 |
| BR-BILL-002 | Patient reference is optional — SET NULL on patient delete | BILL-REQ-003 |
| BR-BILL-003 | Patient DELETE is SET NULL — preserves invoice record | BILL-REQ-025 |
| BR-BILL-004 | Invoice number must be unique across the system | BILL-REQ-004 |
| BR-BILL-005 | Total amount must be non-negative | BILL-REQ-005 |
| BR-BILL-006 | Paid amount must be non-negative | BILL-REQ-006 |
| BR-BILL-007 | Paid amount must not exceed total amount | BILL-REQ-006 |
| BR-BILL-008 | Status must be one of: draft, sent, paid, overdue, cancelled | BILL-REQ-007 |
| BR-BILL-009 | Status transition: draft → sent; draft → cancelled | BILL-REQ-007 |
| BR-BILL-010 | Status transition: sent → paid; sent → overdue | BILL-REQ-007 |
| BR-BILL-011 | Status transition: overdue → paid; overdue → cancelled | BILL-REQ-007 |
| BR-BILL-012 | Paid invoice cannot transition to any other status | BILL-REQ-007 |
| BR-BILL-013 | Cancelled invoice cannot transition to any other status | BILL-REQ-007 |
| BR-BILL-014 | Items must be valid JSON array when provided | BILL-REQ-009 |
| BR-BILL-015 | List queries are organization-scoped | BILL-REQ-021 |
| BR-BILL-016 | Organization-scoped listing — user cannot see invoices from other orgs | BILL-REQ-021 |
| BR-BILL-017 | Soft delete only — no hard delete | BILL-REQ-024 |
| BR-BILL-018 | Audit trail auto-populated via HasAudit trait | BILL-REQ-019 |
| BR-BILL-019 | Authorization: Super Admin full access, Finance/Admin CRUD, Doctor read own patient invoices | BILL-REQ-020 |
| BR-BILL-020 | API response uses ApiResponse envelope | BILL-REQ-022 |

### Status Lifecycle

```
                    ┌──────────┐
                    │  draft   │
                    └────┬─────┘
                    ┌────┴─────┐
                    │          │
                    ▼          ▼
              ┌──────────┐  ┌───────────┐
              │   sent   │  │ cancelled │
              └─────┬────┘  └───────────┘
              ┌─────┴─────┐
              │           │
              ▼           ▼
        ┌──────────┐  ┌──────────┐
        │   paid   │  │ overdue  │
        └──────────┘  └─────┬────┘
                       ┌─────┴─────┐
                       │           │
                       ▼           ▼
                 ┌──────────┐  ┌───────────┐
                 │   paid   │  │ cancelled │
                 └──────────┘  └───────────┘
```

### Status Transition Rules

| From | To | Allowed | Rule |
|---|---|---|---|
| draft | sent | ✅ | BR-BILL-009 |
| draft | cancelled | ✅ | BR-BILL-009 |
| sent | paid | ✅ | BR-BILL-010 |
| sent | overdue | ✅ | BR-BILL-010 |
| overdue | paid | ✅ | BR-BILL-011 |
| overdue | cancelled | ✅ | BR-BILL-011 |
| paid | any | ❌ | BR-BILL-012 |
| cancelled | any | ❌ | BR-BILL-013 |

### Payment Validation

| Condition | Rule |
|---|---|
| paid_amount > total_amount | BusinessException: "Paid amount cannot exceed total amount." |
| paid_amount = total_amount | Status auto-transitions to 'paid' |
| Status = 'paid' | paid_amount must equal total_amount |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| organization_id | No | Internal transfer |
| invoice_number | Yes | Unique identifier |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Paid invoices cannot be deleted
- Soft-deleted invoices are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Finance | Doctor | Patient |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Own patient | Own |
| Show | All | Org-scoped | Org-scoped | Own patient | Own |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |