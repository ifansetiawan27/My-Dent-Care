# Phase 21 — Procurement API Contract

**Date:** 2026-08-17 | **Phase:** 21 — Procurement | **Status:** STEP_21_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/procurement-orders` | `index` | Sanctum | List procurement orders (paginated, filtered) |
| GET | `/api/v1/procurement-orders/{id}` | `show` | Sanctum | Get procurement order by ID |
| POST | `/api/v1/procurement-orders` | `store` | Sanctum | Create procurement order |
| PUT | `/api/v1/procurement-orders/{id}` | `update` | Sanctum | Update procurement order |
| DELETE | `/api/v1/procurement-orders/{id}` | `destroy` | Sanctum | Soft-delete procurement order |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `status` | string | No | Filter by status |
| `search` | string | No | Search in order_number, notes |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (order_date, created_at, status) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Procurement Order

```
POST /api/v1/procurement-orders
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `order_number` | string | Yes | Unique order number |
| `order_date` | date | Yes | Order date |
| `supplier_id` | uuid | No | Supplier UUID |
| `branch_id` | uuid | No | Branch UUID |
| `expected_date` | date | No | Expected delivery date |
| `total_amount` | decimal | No | Total order amount (default 0) |
| `items` | array | No | Order items |
| `notes` | text | No | Order notes |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "branch_id": "uuid|null",
    "supplier_id": "uuid|null",
    "order_number": "string",
    "status": "pending",
    "status_label": "Pending",
    "order_date": "date",
    "expected_date": "date|null",
    "total_amount": "decimal",
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

## Update Procurement Order

```
PUT /api/v1/procurement-orders/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `order_number` | string | No | Unique order number |
| `status` | string | No | Status (must follow valid transitions) |
| `supplier_id` | uuid | No | Supplier UUID |
| `branch_id` | uuid | No | Branch UUID |
| `order_date` | date | No | Order date |
| `expected_date` | date | No | Expected delivery date |
| `total_amount` | decimal | No | Total order amount |
| `items` | array | No | Order items |
| `notes` | text | No | Order notes |

### Response (200)

Same structure as Create response.

## Delete Procurement Order

```
DELETE /api/v1/procurement-orders/{id}
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
| 404 | Not Found | `{"success": false, "message": "Procurement order not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Procurement Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |