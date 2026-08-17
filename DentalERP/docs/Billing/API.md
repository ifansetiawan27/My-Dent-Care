# Phase 17 — Billing API Contract

**Date:** 2026-08-17 | **Phase:** 17 — Billing | **Status:** STEP_17_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/invoices` | `index` | Sanctum | List invoices (paginated, filtered) |
| GET | `/api/v1/invoices/{id}` | `show` | Sanctum | Get invoice by ID |
| POST | `/api/v1/invoices` | `store` | Sanctum | Create invoice |
| PUT | `/api/v1/invoices/{id}` | `update` | Sanctum | Update invoice |
| DELETE | `/api/v1/invoices/{id}` | `destroy` | Sanctum | Soft-delete invoice |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | No | Filter by patient |
| `status` | string | No | Filter by status (draft, sent, paid, overdue, cancelled) |
| `search` | string | No | Search in invoice_number, notes |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (created_at, due_date) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Invoice

```
POST /api/v1/invoices
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | No | Patient UUID |
| `total_amount` | decimal | Yes | Total invoice amount |
| `paid_amount` | decimal | No | Initial paid amount (default 0) |
| `due_date` | date | No | Payment due date |
| `items` | array | No | Line items array |
| `notes` | text | No | Additional notes |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "patient_id": "uuid|null",
    "invoice_number": "string",
    "total_amount": "decimal",
    "paid_amount": "decimal",
    "status": "draft",
    "due_date": "date|null",
    "items": "array|null",
    "notes": "string|null",
    "created_by": "uuid|null",
    "updated_by": "uuid|null",
    "deleted_by": "uuid|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
  }
}
```

## Update Invoice

```
PUT /api/v1/invoices/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | No | Patient UUID |
| `total_amount` | decimal | No | Total invoice amount |
| `paid_amount` | decimal | No | Paid amount |
| `status` | string | No | Status transition |
| `due_date` | date | No | Payment due date |
| `items` | array | No | Line items array |
| `notes` | text | No | Additional notes |

### Response (200)

Same structure as Create response.

## Delete Invoice

```
DELETE /api/v1/invoices/{id}
```

### Response (200)

```json
{
  "success": true,
  "message": "Deleted."
}
```

## Error Responses

| Code | Condition | Response |
|---|---|---|
| 401 | Unauthenticated | `{"success": false, "message": "Unauthenticated."}` |
| 403 | Forbidden | `{"success": false, "message": "Forbidden."}` |
| 404 | Not Found | `{"success": false, "message": "Invoice not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Finance | Doctor | Patient |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Own patient | Own |
| Show | All | Org-scoped | Org-scoped | Own patient | Own |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |