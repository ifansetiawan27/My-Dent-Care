# Phase 19 — Pharmacy API Contract

**Date:** 2026-08-17 | **Phase:** 19 — Pharmacy | **Status:** STEP_19_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/pharmacy-items` | `index` | Sanctum | List pharmacy items (paginated, filtered) |
| GET | `/api/v1/pharmacy-items/{id}` | `show` | Sanctum | Get pharmacy item by ID |
| POST | `/api/v1/pharmacy-items` | `store` | Sanctum | Create pharmacy item |
| PUT | `/api/v1/pharmacy-items/{id}` | `update` | Sanctum | Update pharmacy item |
| DELETE | `/api/v1/pharmacy-items/{id}` | `destroy` | Sanctum | Soft-delete pharmacy item |
| PATCH | `/api/v1/pharmacy-items/{id}/toggle-active` | `toggleActive` | Sanctum | Toggle active/inactive status |

**6 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `branch_id` | uuid | No | Filter by branch |
| `category` | string | No | Filter by category |
| `is_active` | boolean | No | Filter by active status |
| `expiry_date` | date | No | Filter by expiry date |
| `search` | string | No | Search in name, drug_code |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (name, drug_code, expiry_date) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Pharmacy Item

```
POST /api/v1/pharmacy-items
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `drug_code` | string | Yes | Unique drug code |
| `name` | string | Yes | Drug name |
| `organization_id` | uuid | Yes | Organization UUID |
| `branch_id` | uuid | No | Branch UUID |
| `category` | string | No | Drug category |
| `quantity` | decimal | No | Initial quantity (default 0) |
| `unit` | string | No | Unit of measurement |
| `unit_price` | decimal | No | Unit price |
| `expiry_date` | date | No | Expiry date |
| `batch_number` | string | No | Batch/lot number |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "branch_id": "uuid|null",
    "drug_code": "string",
    "name": "string",
    "category": "string|null",
    "quantity": "decimal",
    "unit": "string|null",
    "unit_price": "decimal|null",
    "expiry_date": "date|null",
    "batch_number": "string|null",
    "is_active": true,
    "created_by": "uuid|null",
    "updated_by": "uuid|null",
    "deleted_by": "uuid|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
  }
}
```

## Update Pharmacy Item

```
PUT /api/v1/pharmacy-items/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `drug_code` | string | No | Unique drug code |
| `name` | string | No | Drug name |
| `branch_id` | uuid | No | Branch UUID |
| `category` | string | No | Drug category |
| `quantity` | decimal | No | Current quantity |
| `unit` | string | No | Unit of measurement |
| `unit_price` | decimal | No | Unit price |
| `expiry_date` | date | No | Expiry date |
| `batch_number` | string | No | Batch/lot number |
| `is_active` | boolean | No | Active status |

### Response (200)

Same structure as Create response.

## Toggle Active

```
PATCH /api/v1/pharmacy-items/{id}/toggle-active
```

### Response (200)

Same structure as Create response with `is_active` flipped.

## Delete Pharmacy Item

```
DELETE /api/v1/pharmacy-items/{id}
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
| 404 | Not Found | `{"success": false, "message": "Pharmacy item not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Pharmacy Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Toggle | All | Org-scoped | Org-scoped | ❌ | ❌ |