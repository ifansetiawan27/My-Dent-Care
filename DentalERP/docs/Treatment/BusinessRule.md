# Phase 16 — Treatment Business Rules

**Date:** 2026-08-17 | **Phase:** 16 — Treatment | **Status:** STEP_16_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-TREAT-001 | Treatment must belong to an Organization | TREAT-REQ-002 |
| BR-TREAT-002 | Patient must exist and be valid | TREAT-REQ-003 |
| BR-TREAT-003 | Patient DELETE is RESTRICT — cannot delete patient with active treatments | TREAT-REQ-003 |
| BR-TREAT-004 | Doctor FK is optional — SET NULL on doctor delete | TREAT-REQ-004 |
| BR-TREAT-005 | Appointment FK is optional — SET NULL on appointment delete | TREAT-REQ-005 |
| BR-TREAT-006 | Treatment type must be a valid treatment category | TREAT-REQ-006 |
| BR-TREAT-007 | Status must be one of: planned, in_progress, completed, cancelled | TREAT-REQ-007 |
| BR-TREAT-008 | Status transition: planned → in_progress → completed | TREAT-REQ-007 |
| BR-TREAT-009 | Status transition: planned → cancelled | TREAT-REQ-007 |
| BR-TREAT-010 | Completed treatment cannot transition to any other status | TREAT-REQ-007 |
| BR-TREAT-011 | Cancelled treatment cannot transition to any other status | TREAT-REQ-007 |
| BR-TREAT-012 | Cost must be non-negative when provided | TREAT-REQ-008 |
| BR-TREAT-013 | Procedure data must be valid JSON when provided | TREAT-REQ-010 |
| BR-TREAT-014 | List queries are organization-scoped | TREAT-REQ-021 |
| BR-TREAT-015 | Organization-scoped listing — user cannot see treatments from other orgs | TREAT-REQ-021 |
| BR-TREAT-016 | Soft delete only — no hard delete | TREAT-REQ-024 |
| BR-TREAT-017 | Audit trail auto-populated via HasAudit trait | TREAT-REQ-019 |
| BR-TREAT-018 | Authorization: Super Admin full access, Organization Admin org-scoped, Doctor own treatments | TREAT-REQ-020 |
| BR-TREAT-019 | API response uses ApiResponse envelope | TREAT-REQ-022 |
| BR-TREAT-020 | Routes versioned under /api/v1/treatments | TREAT-REQ-023 |

### Status Lifecycle

```
                    ┌──────────┐
                    │ planned  │
                    └────┬─────┘
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
| planned | in_progress | ✅ | BR-TREAT-008 |
| planned | cancelled | ✅ | BR-TREAT-009 |
| in_progress | completed | ✅ | BR-TREAT-008 |
| in_progress | cancelled | ✅ | OPEN DECISION — allow cancellation during treatment? |
| completed | any | ❌ | BR-TREAT-010 |
| cancelled | any | ❌ | BR-TREAT-011 |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| patient_id | No | May be reassigned (OPEN DECISION) |
| organization_id | No | Internal transfer |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Completed treatments cannot be deleted (OPEN DECISION)
- Soft-deleted treatments are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Doctor | Staff | Patient |
|---|---|---|---|---|---|
| List | All | Org-scoped | Own | Org-scoped | Own |
| Show | All | Org-scoped | Own | Org-scoped | Own |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Own | ❌ | ❌ |
| Delete | All | Org-scoped | Own | ❌ | ❌ |