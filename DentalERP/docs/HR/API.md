# Phase 23 — HR API Contract

**Date:** 2026-08-17 | **Phase:** 23 — HR | **Status:** STEP_23_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/hr-records` | `index` | Sanctum | List HR records (paginated, filtered) |
| GET | `/api/v1/hr-records/{id}` | `show` | Sanctum | Get HR record by ID |
| POST | `/api/v1/hr-records` | `store` | Sanctum | Create HR record |
| PUT | `/api/v1/hr-records/{id}` | `update` | Sanctum | Update HR record |
| DELETE | `/api/v1/hr-records/{id}` | `destroy` | Sanctum | Soft-delete HR record |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `record_type` | string | No | Filter by record type |
| `status` | string | No | Filter by status |
| `employee_id` | uuid | No | Filter by employee |
| `search` | string | No | Search in notes, record_type |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (effective_date, created_at, status) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create HR Record

```
POST /api/v1/hr-records
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `record_type` | string | Yes | Record type |
| `effective_date` | date | Yes | Effective date |
| `employee_id` | uuid | No | Employee UUID |
| `end_date` | date | No | End date |
| `data` | array | No | Record data (JSONB) |
| `notes` | text | No | Record notes |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "employee_id": "uuid|null",
    "record_type": "string",
    "status": "active",
    "status_label": "Active",
    "effective_date": "date",
    "end_date": "date|null",
    "data": "array|null",
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

## Update HR Record

```
PUT /api/v1/hr-records/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `record_type` | string | No | Record type |
| `status` | string | No | Status (must follow valid transitions) |
| `employee_id` | uuid | No | Employee UUID |
| `effective_date` | date | No | Effective date |
| `end_date` | date | No | End date |
| `data` | array | No | Record data (JSONB) |
| `notes` | text | No | Record notes |

### Response (200)

Same structure as Create response.

## Delete HR Record

```
DELETE /api/v1/hr-records/{id}
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
| 404 | Not Found | `{"success": false, "message": "HR record not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | HR Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |