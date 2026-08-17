# Phase 24 — CRM API Contract

**Date:** 2026-08-17 | **Phase:** 24 — CRM | **Status:** STEP_24_05_DRAFT

## Endpoints

| Method | Path | Action | Auth | Description |
|---|---|---|---|---|
| GET | `/api/v1/crm-contacts` | `index` | Sanctum | List CRM contacts (paginated, filtered) |
| GET | `/api/v1/crm-contacts/{id}` | `show` | Sanctum | Get CRM contact by ID |
| POST | `/api/v1/crm-contacts` | `store` | Sanctum | Create CRM contact |
| PUT | `/api/v1/crm-contacts/{id}` | `update` | Sanctum | Update CRM contact |
| DELETE | `/api/v1/crm-contacts/{id}` | `destroy` | Sanctum | Soft-delete CRM contact |

**5 endpoints. ApiResponse envelope on all responses.**

## List Query Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `status` | string | No | Filter by status |
| `contact_type` | string | No | Filter by contact type |
| `search` | string | No | Search in subject, message |
| `per_page` | int | No | Items per page (default 20, max 100) |
| `page` | int | No | Page number |
| `sort_by` | string | No | Sort field (follow_up_date, created_at, status) |
| `sort_dir` | string | No | Sort direction (asc, desc) |

## Create CRM Contact

```
POST /api/v1/crm-contacts
```

### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `contact_type` | string | Yes | Contact type |
| `patient_id` | uuid | No | Patient UUID |
| `channel` | string | No | Communication channel |
| `subject` | string | No | Contact subject |
| `message` | text | No | Contact message |
| `follow_up_date` | date | No | Follow-up date |
| `resolution` | text | No | Resolution notes |

### Response (201)

```json
{
  "data": {
    "id": "uuid",
    "organization_id": "uuid",
    "patient_id": "uuid|null",
    "contact_type": "string",
    "channel": "string|null",
    "subject": "string|null",
    "message": "string|null",
    "status": "new",
    "status_label": "New",
    "follow_up_date": "date|null",
    "resolution": "string|null",
    "created_by": "uuid|null",
    "updated_by": "uuid|null",
    "deleted_by": "uuid|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
  }
}
```

## Update CRM Contact

```
PUT /api/v1/crm-contacts/{id}
```

### Request Body

All fields optional. Only provided fields are updated.

| Field | Type | Required | Description |
|---|---|---|---|
| `contact_type` | string | No | Contact type |
| `status` | string | No | Status (must follow valid transitions) |
| `patient_id` | uuid | No | Patient UUID |
| `channel` | string | No | Communication channel |
| `subject` | string | No | Contact subject |
| `message` | text | No | Contact message |
| `follow_up_date` | date | No | Follow-up date |
| `resolution` | text | No | Resolution notes |

### Response (200)

Same structure as Create response.

## Delete CRM Contact

```
DELETE /api/v1/crm-contacts/{id}
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
| 404 | Not Found | `{"success": false, "message": "CRM contact not found."}` |
| 422 | Validation Error | `{"success": false, "message": "...", "errors": {...}}` |
| 422 | Business Error | `{"success": false, "message": "..."}` |

## Tenant Isolation

- All queries automatically scoped to `auth()->user()->organization_id`
- Cross-organization access returns 404
- Organization_id in request body must match authenticated user's organization

## Authorization

| Action | Super Admin | Org Admin | CRM Staff | Doctor | Staff |
|---|---|---|---|---|---|
| List | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Update | All | Org-scoped | Org-scoped | ❌ | ❌ |
| Delete | All | Org-scoped | Org-scoped | ❌ | ❌ |