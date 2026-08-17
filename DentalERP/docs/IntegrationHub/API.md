# Phase 27 — Integration Hub API Contract

**Date:** 2026-08-17 | **Phase:** 27 — Integration Hub | **Status:** STEP_27_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/integration-configs` | `index` | Sanctum | List integration configs (paginated, filtered) |
| GET | `/api/v1/integration-configs/{id}` | `show` | Sanctum | Get integration config by ID |
| POST | `/api/v1/integration-configs` | `store` | Sanctum | Create integration config |
| PUT | `/api/v1/integration-configs/{id}` | `update` | Sanctum | Update integration config |
| DELETE | `/api/v1/integration-configs/{id}` | `destroy` | Sanctum | Soft-delete integration config |
| POST | `/api/v1/integration-configs/{id}/toggle-active` | `toggleActive` | Sanctum | Toggle is_active boolean |

**6 endpoints. ApiResponse envelope on all responses.**

**CRITICAL: The `credentials` field is WRITE-ONLY. It is NEVER returned in any API response.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `provider` | string | No | Filter by provider name |
| `is_active` | boolean | No | Filter by active status (0 or 1) |
| `search` | string | No | Search in name |
| `per_page` | int | No | Items per page (default 15, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (name, provider, created_at) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Integration Config

```
POST /api/v1/integration-configs
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `provider` | string | Yes | Provider name (max 50) |
| `name` | string | Yes | Integration name (max 100) |
| `config` | object | No | Provider-specific configuration |
| `credentials` | object | No | Sensitive credentials (encrypted, write-only) |

### Response (201)

```json
{
  "success": true,
  "message": "Integration config created successfully.",
  "data": {
    "id": "uuid",
    "provider": "string",
    "name": "string",
    "config": { "api_url": "https://..." },
    "is_active": false,
    "last_sync_at": null,
    "created_at": "2026-08-17T00:00:00.000000Z",
    "updated_at": "2026-08-17T00:00:00.000000Z"
  }
}
```

**Note: `credentials` field is NOT present in the response.**

## Update Integration Config

```
PUT /api/v1/integration-configs/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `provider` | string | No | Provider name (must be unique per org) |
| `name` | string | No | Integration name |
| `config` | object | No | Provider-specific configuration |
| `credentials` | object | No | Sensitive credentials (encrypted) |
| `is_active` | boolean | No | Active status |

### Response (200)

Same structure as Create response. `credentials` field is NOT present.

## Toggle Active

```
POST /api/v1/integration-configs/{id}/toggle-active
```

### Request Body

No request body required.

### Response (200)

```json
{
  "success": true,
  "message": "Integration config toggled successfully.",
  "data": {
    "id": "uuid",
    "provider": "string",
    "name": "string",
    "config": { "api_url": "https://..." },
    "is_active": true,
    "last_sync_at": null,
    "created_at": "2026-08-17T00:00:00.000000Z",
    "updated_at": "2026-08-17T00:00:00.000000Z"
  }
}
```

## Delete Integration Config

```
DELETE /api/v1/integration-configs/{id}
```

### Response (200)

```json
{
  "success": true,
  "message": "Integration config deleted successfully."
}
```

## Error Responses

| Code | Condition | Response |
|---|---|---|
| 401 | Unauthenticated | `{"success": false, "message": "Unauthenticated."}` |
| 403 | Forbidden | `{"success": false, "message": "Forbidden."}` |
| 404 | Not Found | `{"success": false, "message": "Integration config not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "Provider already exists for this organization."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- organization_id is derived from authenticated user, not from request body

## Authorization

| Action | Super Admin | Org Admin | Integration Manager |
|---|---|---|---|
| List | All | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped |
| Update | All | Org-scoped | Org-scoped |
| Delete | All | Org-scoped | Org-scoped |
| Toggle Active | All | Org-scoped | Org-scoped |