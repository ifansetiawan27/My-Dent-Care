# Phase 25 — Reporting API Contract

**Date:** 2026-08-17 | **Phase:** 25 — Reporting | **Status:** STEP_25_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/reports` | `index` | Sanctum | List reports (paginated, filtered) |
| GET | `/api/v1/reports/{id}` | `show` | Sanctum | Get report by ID |
| POST | `/api/v1/reports` | `store` | Sanctum | Create report |
| PUT | `/api/v1/reports/{id}` | `update` | Sanctum | Update report |
| DELETE | `/api/v1/reports/{id}` | `destroy` | Sanctum | Soft-delete report |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `report_type` | string | No | Filter by report type |
| `status` | string | No | Filter by status |
| `search` | string | No | Search in name |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (report_date, created_at) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Report

```
POST /api/v1/reports
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `report_type` | string | Yes | Report type |
| `name` | string | Yes | Report name |
| `report_date` | date | Yes | Report date |
| `parameters` | array | No | Report parameters (JSONB) |
| `data` | array | No | Report data (JSONB) |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "report_type": "string",
    "name": "string",
    "parameters": "array|null",
    "data": "array|null",
    "status": "generated",
    "status_label": "Generated",
    "report_date": "date",
    "created_by": "uuid|null",
    "updated_by": "uuid|null",
    "deleted_by": "uuid|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
  }
}
```

## Update Report

```
PUT /api/v1/reports/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `report_type` | string | No | Report type |
| `name` | string | No | Report name |
| `status` | string | No | Status (generated, archived) |
| `report_date` | date | No | Report date |
| `parameters` | array | No | Report parameters (JSONB) |
| `data` | array | No | Report data (JSONB) |

### Response (200)

Same structure as Create response.

## Delete Report

```
DELETE /api/v1/reports/{id}
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
| 404 | Not Found | `{"success": false, "message": "Report not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Report Manager | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |