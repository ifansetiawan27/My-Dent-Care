# Phase 16 — Treatment API Contract

**Date:** 2026-08-17 | **Phase:** 16 — Treatment | **Status:** STEP_16_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/treatments` | `index` | Sanctum | List treatments (paginated, filtered) |
| GET | `/api/v1/treatments/{id}` | `show` | Sanctum | Get treatment by ID |
| POST | `/api/v1/treatments` | `store` | Sanctum | Create treatment |
| PUT | `/api/v1/treatments/{id}` | `update` | Sanctum | Update treatment |
| DELETE | `/api/v1/treatments/{id}` | `destroy` | Sanctum | Soft-delete treatment |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | No | Filter by patient |
| `doctor_id` | uuid | No | Filter by doctor |
| `appointment_id` | uuid | No | Filter by appointment |
| `status` | string | No | Filter by status (planned, in_progress, completed, cancelled) |
| `treatment_type` | string | No | Filter by treatment type |
| `search` | string | No | Search in description |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (created_at, status) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create Treatment

```
POST /api/v1/treatments
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `patient_id` | uuid | Yes | Patient UUID |
| `doctor_id` | uuid | No | Doctor UUID |
| `appointment_id` | uuid | No | Appointment UUID |
| `treatment_type` | string | Yes | Treatment type code |
| `cost` | decimal | No | Treatment cost |
| `description` | text | No | Treatment description |
| `procedure_data` | object | No | Procedure data (teeth, surfaces, materials) |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "patient_id": "uuid",
    "doctor_id": "uuid|null",
    "appointment_id": "uuid|null",
    "treatment_type": "string",
    "status": "planned",
    "cost": "decimal|null",
    "description": "string|null",
    "procedure_data": "object|null",
    "created_at": "datetime",
    "updated_at": "datetime"
  }
}
```

## Update Treatment

```
PUT /api/v1/treatments/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `doctor_id` | uuid | No | Doctor UUID |
| `treatment_type` | string | No | Treatment type code |
| `status` | string | No | Status transition (planned→in_progress→completed, planned→cancelled) |
| `cost` | decimal | No | Treatment cost |
| `description` | text | No | Treatment description |
| `procedure_data` | object | No | Procedure data |

### Response (200)

Same structure as Create response.

## Delete Treatment

```
DELETE /api/v1/treatments/{id}
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
| 404 | Not Found | `{"success": false, "message": "Treatment not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | Doctor | Staff |
|---|---|---|---|---|
| List | All | Org-scoped | Own | Org-scoped |
| Show | All | Org-scoped | Own | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ |
| Update | All | Org-scoped | Own | ❌ |
| Delete | All | Org-scoped | Own | ❌ |