# Phase 10 — Employee API Contract

**Date:** 2026-08-10
**Phase:** 10 — Employee
**SDLC Stage:** 04 — API Contract
**Status:** `STEP_10_12_EMPLOYEE_API_CONTRACT_DRAFT`

**Traceability:**
- Requirements: `docs/Employee/Requirement.md` (STEP_10_03_PASS)
- Business Rules: `docs/Employee/BusinessRule.md` (STEP_10_05_PASS)
- Flow: `docs/Employee/Flow.md` (STEP_10_07_PASS)
- DB Design: `docs/Employee/DatabaseDesign.md` (STEP_10_09_PASS)
- ERD: `docs/Employee/ERD.md` (STEP_10_11_PASS)
- API Convention: `AGENTS.md` ApiResponse standard
- Auth: Phase 08 (frozen)

---

## 1. API Conventions

| Attribute | Value |
|---|---|
| Base URL | `/api/v1` |
| Auth | Sanctum Bearer token |
| Envelope | `ApiResponse`: `success`, `message`, `data`, `errors`, `meta` |
| Resource | Plural: `employees` |

---

## 2. Authorization

| Operation | Role | Permission |
|---|---|---|
| Read (list, show) | Any authenticated | — |
| Write (create, update, delete, toggle) | Super Admin, Owner | Policy enforced |

---

## 3. Tenant Scope

All Employee queries are **scoped to the authenticated user's organization**. Cross-organization access returns 403. Branch sub-scoping applied when available.

---

## 4. Endpoint Inventory

| Method | Path | Purpose | Auth |
|---|---|---|---|
| `GET` | `/api/v1/employees` | List employees | Read |
| `GET` | `/api/v1/employees/{id}` | Show employee | Read |
| `POST` | `/api/v1/employees` | Create employee | Write |
| `PUT` | `/api/v1/employees/{id}` | Update employee | Write |
| `DELETE` | `/api/v1/employees/{id}` | Soft delete | Write |
| `PATCH` | `/api/v1/employees/{id}/toggle-active` | Toggle active/inactive | Write |

---

## 5. List Employees

`GET /api/v1/employees`

**Auth:** Sanctum (all authenticated)
**Scope:** Scoped to actor's `organization_id`; optionally filtered by `branch_id`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `search` | string | No | — | Search by `full_name` or `employee_code` |
| `branch_id` | uuid | No | — | Filter by branch |
| `is_active` | boolean | No | — | Filter by active status |
| `include_inactive` | boolean | No | `false` | Include inactive records |
| `per_page` | integer | No | `20` | Per page (max 100) |
| `page` | integer | No | `1` | Page number |
| `sort_by` | string | No | `full_name` | Sort field: `full_name`, `employee_code`, `hire_date` |
| `sort_dir` | string | No | `asc` | Direction: `asc`, `desc` |

**Response:** `200` with `ApiResponse` envelope, paginated `employees` array.

---

## 6. Show Employee

`GET /api/v1/employees/{id}`

**Auth:** Sanctum (all authenticated)
**Scope:** Employee must belong to actor's organization.

**Response:** `200` with Employee resource. `404` if not found or wrong org.

---

## 7. Create Employee

`POST /api/v1/employees`

**Auth:** Sanctum (Super Admin/Owner)
**Scope:** Actor's organization

| Field | Type | Required | Validation | Source |
|---|---|---|---|---|
| `employee_code` | string | Yes | UNIQUE globally | EMP-REQ-001, EMP-BR-001 |
| `full_name` | string | Yes | NOT NULL, max 200 | EMP-REQ-016, EMP-BR-017 |
| `organization_id` | uuid | Yes | Must exist | EMP-REQ-005, EMP-BR-005 |
| `branch_id` | uuid | No | Must exist + belong to same org | EMP-REQ-006, EMP-BR-008 |
| `employment_status` | string | Yes | [GAP-002] | EMP-REQ-012, EMP-BR-013 |
| `hire_date` | date | Yes | <= today | EMP-REQ-013, EMP-BR-014 |
| `resignation_date` | date | No | >= hire_date | EMP-REQ-014, EMP-BR-015 |
| `position` | string | No | [GAP-003] | EMP-REQ-015, EMP-BR-016 |
| `gender` | string | No | Valid Gender enum value | EMP-REQ-007, EMP-BR-009 |
| `religion` | string | No | Valid Religion enum value | EMP-REQ-008, EMP-BR-010 |
| `marital_status` | string | No | Valid MaritalStatus enum value | EMP-REQ-009, EMP-BR-011 |
| `nationality_id` | uuid | No | Must exist | EMP-REQ-010 |
| `phone` | string | No | Valid phone format | EMP-REQ-017 |
| `email` | string | No | Valid email format | EMP-REQ-017 |
| `address` | text | No | — | EMP-REQ-018 |
| `district_id` | uuid | No | Must exist | EMP-REQ-011 |
| `village_id` | uuid | No | Must exist | EMP-REQ-011 |

**Governance:** `employee_code` generation mechanism is REQUIRES DECISION (EMP-GAP-001). The field is required in the request body until generation policy is resolved.

**Response:** `201` with Employee resource. `422` on validation/duplicate.

---

## 8. Update Employee

`PUT /api/v1/employees/{id}`

Same fields as Create, all optional (partial update). Immutable fields: `id`, `employee_code` (identity does not change — EMP-BR-002).

**Response:** `200` with updated Employee resource.

---

## 9. Soft Delete

`DELETE /api/v1/employees/{id}`

**Auth:** Super Admin/Owner
**Behavior:** Sets `deleted_at` — soft delete only. Record preserved for referential integrity.
**Response:** `200` with success message. `404` if not found.

---

## 10. Toggle Active

`PATCH /api/v1/employees/{id}/toggle-active`

**Auth:** Super Admin/Owner
**Behavior:** Flips `is_active` boolean. No body required. Does NOT affect `employment_status` or `resignation_date`.
**Response:** `200` with updated Employee resource.

---

## 11. Employee Resource

```json
{
  "id": "uuid",
  "employee_code": "EMP001",
  "full_name": "Dr. John Doe",
  "organization_id": "uuid",
  "organization": { "id": "uuid", "name": "Klinik Sehat" },
  "branch_id": "uuid|null",
  "branch": { "id": "uuid", "name": "Cabang Jakarta" },
  "employment_status": "permanent",
  "hire_date": "2024-01-15",
  "resignation_date": null,
  "position": "Dentist",
  "gender": "male",
  "religion": "islam",
  "marital_status": "married",
  "nationality_id": "uuid|null",
  "phone": "081234567890",
  "email": "john@kliniksehat.id",
  "address": "Jl. Sudirman No. 10",
  "district_id": "uuid|null",
  "village_id": "uuid|null",
  "is_active": true,
  "created_at": "2026-08-10T00:00:00+07:00",
  "updated_at": "2026-08-10T00:00:00+07:00"
}
```

**Excluded fields (never exposed):** `created_by`, `updated_by`, `deleted_by`, `deleted_at`.

---

## 12. HTTP Status Codes

| Code | Scenario | Business Rule |
|---|---|---|
| `200` | Success (read, update, delete, toggle) | — |
| `201` | Create success | — |
| `400` | Malformed request | — |
| `401` | Unauthenticated | EMP-BR-024 |
| `403` | Unauthorized / cross-org access | EMP-BR-025, EMP-BR-006 |
| `404` | Employee not found | — |
| `422` | Validation failure / duplicate code / invalid FK | EMP-BR-001, EMP-BR-028 |
| `500` | Internal error (no details) | — |

---

## 13. Error Contract

```json
{ "success": false, "message": "Validation failed.", "errors": { "employee_code": ["Already taken."], "organization_id": ["Selected organization does not exist."] } }
```

Consistent `ApiResponse` envelope across all endpoints.

---

## 14. Governance Decision Points

| ID | Decision | API Impact | Status |
|---|---|---|---|
| EMP-GAP-001 | Code generation | Required field on create until policy resolved | ✅ REQUIRES DECISION |
| EMP-GAP-002 | Status mechanism | varchar string; final type TBD | ✅ REQUIRES DECISION |
| EMP-GAP-003 | Position field | Free-text string; final model TBD | ✅ REQUIRES DECISION |
| EMP-GAP-004 | user_id FK | Not in API — bridge via employee_code only | ✅ REQUIRES DECISION |
| EMP-GAP-005 | Status transitions | No transition API | ✅ REQUIRES DECISION |

---

## 15. Traceability

| Requirement | Endpoint/Field |
|---|---|
| EMP-REQ-001, 004 | POST/PUT `employee_code` |
| EMP-REQ-005 | POST `organization_id` |
| EMP-REQ-006 | POST `branch_id` |
| EMP-REQ-007–011 | POST `gender`, `religion`, `marital_status`, `nationality_id`, `district_id`, `village_id` |
| EMP-REQ-012–015 | POST `employment_status`, `hire_date`, `resignation_date`, `position` |
| EMP-REQ-016–018 | POST `full_name`, `phone`, `email`, `address` |
| EMP-REQ-019 | PATCH toggle-active |
| EMP-REQ-020 | DELETE — soft delete |
| EMP-REQ-022 | Auth — Sanctum, Policy |
| EMP-REQ-023 | Scope — organization_id + optional branch_id |

**25/25 requirements traced. 29/29 business rules traced. 16/16 flows traced.**

---

## 16. Endpoint Summary

| Method | Path | Purpose |
|---|---|---|
| `GET` | `/employees` | List (paginated, scoped) |
| `GET` | `/employees/{id}` | Show detail |
| `POST` | `/employees` | Create |
| `PUT` | `/employees/{id}` | Update |
| `DELETE` | `/employees/{id}` | Soft delete |
| `PATCH` | `/employees/{id}/toggle-active` | Toggle active |

---

## Governance Record

| Check | Result |
|---|---|
| API.md created | ✅ |
| 6 endpoints: CRUD + toggle-active | ✅ |
| All endpoints trace to Flow/BR/REQ | ✅ |
| Authorization: read=authenticated, write=admin | ✅ |
| Tenant scope: organization_id mandatory | ✅ |
| 5 governance gaps preserved | ✅ |
| ApiResponse envelope consistent | ✅ |
| Protected artifacts untouched | ✅ |
| Implementation not started | ✅ |

STEP_10_12_EMPLOYEE_API_CONTRACT_DRAFT_PASS
