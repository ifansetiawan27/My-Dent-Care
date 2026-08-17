# Phase 26 — Dashboard API Contract

**Date:** 2026-08-17 | **Phase:** 26 — Dashboard | **Status:** STEP_26_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/dashboards` | `index` | Sanctum | List dashboards (paginated, filtered) |
| GET | `/api/v1/dashboards/{id}` | `show` | Sanctum | Get dashboard by ID |
| POST | `/api/v1/dashboards` | `store` | Sanctum | Create dashboard |
| PUT | `/api/v1/dashboards/{id}` | `update` | Sanctum | Update dashboard |
| DELETE | `/api/v1/dashboards/{id}` | `destroy` | Sanctum | Soft-delete dashboard |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `user_id` | uuid | No | Filter by user |
| `search` | string | No | Search in name |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (name, created_at) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Dashboard

```
POST /api/v1/dashboards
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `name` | string | Yes | Dashboard name |
| `user_id` | uuid | No | User UUID |
| `config` | array | No | Dashboard configuration (JSONB) |
| `widgets` | array | No | Widget definitions (JSONB) |
| `is_default` | boolean | No | Set as default dashboard |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "user_id": "uuid|null",
    "name": "string",
    "config": "array|null",
    "widgets": "array|null",
    "is_default": false,
    "created_by": "uuid|null",
    "updated_by": "uuid|null",
    "deleted_by": "uuid|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
  }
}
```

## Update Dashboard

```
PUT /api/v1/dashboards/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `name` | string | No | Dashboard name |
| `user_id` | uuid | No | User UUID |
| `config` | array | No | Dashboard configuration (JSONB) |
| `widgets` | array | No | Widget definitions (JSONB) |
| `is_default` | boolean | No | Set as default dashboard |

### Response (200)

Same structure as Create response.

## Delete Dashboard

```
DELETE /api/v1/dashboards/{id}
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
| 404 | Not Found | `{"success": false, "message": "Dashboard not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Dashboard Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |