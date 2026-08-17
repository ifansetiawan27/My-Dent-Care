# Phase 22 — Asset API Contract

**Date:** 2026-08-17 | **Phase:** 22 — Asset | **Status:** STEP_22_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/assets` | `index` | Sanctum | List assets (paginated, filtered) |
| GET | `/api/v1/assets/{id}` | `show` | Sanctum | Get asset by ID |
| POST | `/api/v1/assets` | `store` | Sanctum | Create asset |
| PUT | `/api/v1/assets/{id}` | `update` | Sanctum | Update asset |
| DELETE | `/api/v1/assets/{id}` | `destroy` | Sanctum | Soft-delete asset |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `status` | string | No | Filter by status |
| `category_id` | uuid | No | Filter by category |
| `search` | string | No | Search in name, asset_code, description |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (name, created_at, purchase_date) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Asset

```
POST /api/v1/assets
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `asset_code` | string | Yes | Unique asset code |
| `name` | string | Yes | Asset name |
| `category_id` | uuid | No | Category UUID |
| `branch_id` | uuid | No | Branch UUID |
| `description` | text | No | Asset description |
| `purchase_date` | date | No | Purchase date |
| `purchase_price` | decimal | No | Purchase price |
| `warranty_expiry` | date | No | Warranty expiry date |
| `notes` | text | No | Asset notes |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "branch_id": "uuid|null",
    "category_id": "uuid|null",
    "asset_code": "string",
    "name": "string",
    "description": "string|null",
    "purchase_date": "date|null",
    "purchase_price": "decimal|null",
    "status": "active",
    "status_label": "Active",
    "warranty_expiry": "date|null",
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

## Update Asset

```
PUT /api/v1/assets/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `asset_code` | string | No | Unique asset code |
| `name` | string | No | Asset name |
| `status` | string | No | Status (must follow valid transitions) |
| `category_id` | uuid | No | Category UUID |
| `branch_id` | uuid | No | Branch UUID |
| `description` | text | No | Asset description |
| `purchase_date` | date | No | Purchase date |
| `purchase_price` | decimal | No | Purchase price |
| `warranty_expiry` | date | No | Warranty expiry date |
| `notes` | text | No | Asset notes |

### Response (200)

Same structure as Create response.

## Delete Asset

```
DELETE /api/v1/assets/{id}
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
| 404 | Not Found | `{"success": false, "message": "Asset not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Asset Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |