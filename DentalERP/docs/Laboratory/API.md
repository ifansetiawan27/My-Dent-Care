# Phase 20 — Laboratory API Contract

**Date:** 2026-08-17 | **Phase:** 20 — Laboratory | **Status:** STEP_20_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/lab-orders` | `index` | Sanctum | List lab orders (paginated, filtered) |
| GET | `/api/v1/lab-orders/{id}` | `show` | Sanctum | Get lab order by ID |
| POST | `/api/v1/lab-orders` | `store` | Sanctum | Create lab order |
| PUT | `/api/v1/lab-orders/{id}` | `update` | Sanctum | Update lab order |
| DELETE | `/api/v1/lab-orders/{id}` | `destroy` | Sanctum | Soft-delete lab order |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | No | Filter by patient |
| `doctor_id` | uuid | No | Filter by doctor |
| `category_id` | uuid | No | Filter by laboratory category |
| `status` | string | No | Filter by status (pending, in_progress, completed, cancelled) |
| `search` | string | No | Search in description, order_number, notes |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (created_at, status, ordered_at) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Lab Order

```
POST /api/v1/lab-orders
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | Yes | Patient UUID |
| `order_number` | string | Yes | Unique order number |
| `doctor_id` | uuid | No | Doctor UUID |
| `category_id` | uuid | No | Laboratory category UUID |
| `description` | text | No | Lab order description |
| `results` | object | No | Results data (test values, findings) |
| `ordered_at` | date | Yes | Date when order was placed |
| `notes` | text | No | Additional notes |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "patient_id": "uuid",
    "doctor_id": "uuid|null",
    "order_number": "string",
    "category_id": "uuid|null",
    "status": "pending",
    "description": "string|null",
    "results": "object|null",
    "ordered_at": "date",
    "completed_at": "date|null",
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

## Update Lab Order

```
PUT /api/v1/lab-orders/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `doctor_id` | uuid | No | Doctor UUID |
| `category_id` | uuid | No | Laboratory category UUID |
| `status` | string | No | Status transition (pending→in_progress→completed, pending→cancelled) |
| `description` | text | No | Lab order description |
| `results` | object | No | Results data |
| `ordered_at` | date | No | Date when order was placed |
| `completed_at` | date | No | Date when order was completed |
| `notes` | text | No | Additional notes |

### Response (200)

Same structure as Create response.

## Delete Lab Order

```
DELETE /api/v1/lab-orders/{id}
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
| 404 | Not Found | `{"success": false, "message": "Lab order not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Doctor | Lab Tech | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Own | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Own | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | Org-scoped | ❌ |
| Update | All | Org-scoped | Own | Org-scoped | ❌ |
| Delete | All | Org-scoped | Own | ❌ | ❌ |