# Phase 18 — Inventory API Contract

**Date:** 2026-08-17 | **Phase:** 18 — Inventory | **Status:** STEP_18_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/inventory-items` | `index` | Sanctum | List inventory items (paginated, filtered) |
| GET | `/api/v1/inventory-items/{id}` | `show` | Sanctum | Get inventory item by ID |
| POST | `/api/v1/inventory-items` | `store` | Sanctum | Create inventory item |
| PUT | `/api/v1/inventory-items/{id}` | `update` | Sanctum | Update inventory item |
| DELETE | `/api/v1/inventory-items/{id}` | `destroy` | Sanctum | Soft-delete inventory item |
| PATCH | `/api/v1/inventory-items/{id}/toggle-active` | `toggleActive` | Sanctum | Toggle active/inactive status |

**6 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `branch_id` | uuid | No | Filter by branch |
| `category_id` | uuid | No | Filter by category |
| `is_active` | boolean | No | Filter by active status |
| `search` | string | No | Search in name, item_code, description |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (name, created_at, quantity) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Inventory Item

```
POST /api/v1/inventory-items
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `item_code` | string | Yes | Unique item code |
| `name` | string | Yes | Item name |
| `unit` | string | Yes | Unit of measurement |
| `branch_id` | uuid | No | Branch UUID |
| `category_id` | uuid | No | Category UUID |
| `description` | text | No | Item description |
| `quantity` | decimal | No | Initial quantity (default 0) |
| `min_quantity` | decimal | No | Minimum quantity threshold (default 0) |
| `unit_price` | decimal | No | Unit price |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "branch_id": "uuid|null",
    "category_id": "uuid|null",
    "item_code": "string",
    "name": "string",
    "description": "string|null",
    "unit": "string",
    "quantity": "decimal",
    "min_quantity": "decimal",
    "unit_price": "decimal|null",
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

## Update Inventory Item

```
PUT /api/v1/inventory-items/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `item_code` | string | No | Unique item code |
| `name` | string | No | Item name |
| `unit` | string | No | Unit of measurement |
| `branch_id` | uuid | No | Branch UUID |
| `category_id` | uuid | No | Category UUID |
| `description` | text | No | Item description |
| `quantity` | decimal | No | Current quantity |
| `min_quantity` | decimal | No | Minimum quantity threshold |
| `unit_price` | decimal | No | Unit price |
| `is_active` | boolean | No | Active status |

### Response (200)

Same structure as Create response.

## Toggle Active

```
PATCH /api/v1/inventory-items/{id}/toggle-active
```

### Response (200)

Same structure as Create response with `is_active` flipped.

## Delete Inventory Item

```
DELETE /api/v1/inventory-items/{id}
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
| 404 | Not Found | `{"success": false, "message": "Inventory item not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Inventory Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Toggle | All | Org-scoped | Org-scoped | ❌ | ❌ |