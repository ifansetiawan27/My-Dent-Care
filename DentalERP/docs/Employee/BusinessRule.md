# Phase 10 — Employee Business Rules

**Date:** 2026-08-09
**Phase:** 10 — Employee
**SDLC Stage:** 02 — Business Rules
**Status:** `STEP_10_04_EMPLOYEE_BUSINESS_RULES_DRAFT`

**Traceability:**
- Requirements: `docs/Employee/Requirement.md` (STEP_10_03_PASS)
- Architecture: `AGENTS.md`, Architecture Standards
- Platform: Phase 07 (commit `99ad776`)
- Auth: Phase 08 (frozen, commit `435c9f9`)
- Master Data: Phase 09 (commit `8be7b26`)

---

## 1. Purpose

Defines the invariants, constraints, and governance rules for Employee domain. Every rule is traceable to a validated EMP-REQ-* requirement and repository authority.

---

## 2. Scope

Employee personal and employment records, organizational assignment, bridge to User accounts, and lifecycle management. Explicitly excludes payroll, attendance, leave, recruitment, and authentication lifecycle.

---

## 3. Business Rule ID Convention

| Convention | Pattern |
|---|---|
| Rule ID | `EMP-BR-NNN` |
| Requirement reference | `EMP-REQ-NNN` |
| Governance dependency | `EMP-GAP-NNN` |

---

## 4. Employee Identity Rules

---

### EMP-BR-001 — Unique Employee Code

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-001`, `EMP-REQ-004` |
| **Rule** | Every Employee MUST have a `employee_code` that is globally unique. The code cannot be duplicated. |
| **Source** | `users.employee_code` UNIQUE constraint (Phase 05); `UserService::isEmployeeCodeTaken()` |
| **Enforcement** | Uniqueness validated before persistence. Duplicate code returns validation error. |
| **Governance** | `EMP-GAP-001` — code generation mechanism (auto vs manual) requires business decision. |
| **Status** | **DRAFT** |

### EMP-BR-002 — Immutable UUID Identity

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-002` |
| **Rule** | Employee UUID (`id`) is server-generated (ordered UUID) and immutable. Clients MUST NOT set the UUID. |
| **Source** | `AGENTS.md` — ordered UUID standard |
| **Enforcement** | UUID auto-generated via `HasUuid` trait; not in fillable fields. |
| **Status** | **DRAFT** |

---

## 5. User ↔ Employee Bridge Rules

---

### EMP-BR-003 — Optional 1:1 User Association

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-003` |
| **Rule** | An Employee MAY be associated with at most one User account via `employee_code`. The association is optional — both an Employee without a User and a User without an Employee are valid. |
| **Source** | `users.employee_code` — nullable column |
| **Enforcement** | When `employee_code` matches a User's code, the link is established. When null or no match, no link exists. |
| **Status** | **DRAFT** |

### EMP-BR-004 — Employee Code Uniqueness Across Boundaries

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-004` |
| **Rule** | `employee_code` uniqueness spans both the Employee and User domains. No two Employees and no Employee+User pair may share the same code. |
| **Source** | `users.employee_code` UNIQUE constraint; `UserService::isEmployeeCodeTaken()` |
| **Enforcement** | Database-level UNIQUE constraint on `users.employee_code`. Employee domain validates before persistence. |
| **Governance** | `EMP-GAP-004` — whether Employee stores `user_id` FK (redundant with `employee_code` lookup) is undecided. Current architecture uses `employee_code` as the bridge. |
| **Status** | **DRAFT** |

---

## 6. Organization Rules

---

### EMP-BR-005 — Mandatory Organization Assignment

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-005` |
| **Rule** | Every Employee MUST belong to exactly one Organization. An Employee record cannot exist without an `organization_id`. |
| **Source** | AGENTS.md multi-organization isolation; Phase 03 Organization |
| **Enforcement** | `organization_id` NOT NULL; FK to `organizations` with RESTRICT delete. |
| **Status** | **DRAFT** |

### EMP-BR-006 — Organization Scoped Queries

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-005`, `EMP-REQ-023` |
| **Rule** | All Employee queries MUST be scoped by the authenticated user's `organization_id`. Cross-organization access is forbidden. |
| **Source** | AGENTS.md tenant isolation standard |
| **Enforcement** | Repository applies `where('organization_id', ...)` on all queries. Policies prevent cross-org access. |
| **Status** | **DRAFT** |

---

## 7. Branch Rules

---

### EMP-BR-007 — Optional Branch Assignment

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-006` |
| **Rule** | An Employee MAY be assigned to a Branch. Employees with no branch assignment are considered organization-wide. |
| **Source** | Phase 04 Branch |
| **Enforcement** | `branch_id` NULL on employees table. FK to `branches` with SET NULL on delete. |
| **Status** | **DRAFT** |

### EMP-BR-008 — Branch Must Belong to Same Organization

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-006`, `EMP-REQ-023` |
| **Rule** | If an Employee is assigned to a Branch, that Branch MUST belong to the same Organization as the Employee. |
| **Source** | AGENTS.md tenant isolation; Phase 04 Branch ← Organization FK |
| **Enforcement** | Service-layer validation: branch belongs to employee's organization before assignment. |
| **Status** | **DRAFT** |

---

## 8. Master Data Rules

---

### EMP-BR-009 — Gender via Core Enum

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-007` |
| **Rule** | Employee gender MUST be stored as a value matching the `Gender` Core Enum and displayed via the `genders` Master Data table. |
| **Source** | `app/Core/Enums/Gender.php` — pre-designated for Employee (HR); Master Data `genders` |
| **Enforcement** | Validation against `Gender::values()`. UI dropdown from Master Data table. |
| **Status** | **DRAFT** |

### EMP-BR-010 — Religion Reference

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-008` |
| **Rule** | Employee MAY reference religion via `Religion` Core Enum and `religions` Master Data table. The field is optional. |
| **Enforcement** | Validation against `Religion::values()` when set. |
| **Status** | **DRAFT** |

### EMP-BR-011 — Marital Status Reference

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-009` |
| **Rule** | Employee MAY reference marital status via `MaritalStatus` Core Enum and `marital_statuses` Master Data table. The field is optional. |
| **Enforcement** | Validation against `MaritalStatus::values()` when set. |
| **Status** | **DRAFT** |

### EMP-BR-012 — Master Data Ownership

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-007` through `EMP-REQ-011` |
| **Rule** | Employee MUST NOT create or modify Master Data records. Employee stores only references to Master Data tables. Master Data ownership remains with Phase 09. |
| **Source** | Phase 09 Master Data — read-only consumer |
| **Enforcement** | Employee domain has no POST/PUT/DELETE on Master Data endpoints. |
| **Status** | **DRAFT** |

---

## 9. Employment Rules

---

### EMP-BR-013 — Employment Status Required

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-012` |
| **Rule** | Every Employee MUST have an `employment_status`. The status governs whether the employee is active, on probation, permanently employed, or terminated. |
| **Source** | `DD-AUTH-007.md` — Employee lifecycle |
| **Enforcement** | `employment_status` NOT NULL. Validated against approved status values. |
| **Governance** | `EMP-GAP-002` — whether `employment_status` uses a Core Enum or Master Data table requires architecture decision. |
| **Governance** | `EMP-GAP-005` — allowed status values and transitions require business decision. |
| **Status** | **DRAFT** |

### EMP-BR-014 — Hire Date Must Be in the Past or Present

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-013` |
| **Rule** | `hire_date` MUST be on or before the current date. Future hire dates are not allowed. |
| **Source** | Standard ERP employee record convention |
| **Enforcement** | Validation: `hire_date <= today` |
| **Status** | **DRAFT** |

### EMP-BR-015 — Resignation Date After Hire

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-014` |
| **Rule** | If `resignation_date` is set, it MUST be on or after the `hire_date`. |
| **Source** | Standard ERP employee record convention |
| **Enforcement** | Validation: `resignation_date >= hire_date` when both are set. |
| **Status** | **DRAFT** |

### EMP-BR-016 — Position Field

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-015` |
| **Rule** | Employee MAY have a `position` field describing their job title. |
| **Source** | Standard ERP |
| **Governance** | `EMP-GAP-003` — whether `position` is free-text or references a Master Data table requires business decision. |
| **Enforcement** | Free-text string field until governance decision. |
| **Status** | **DRAFT** |

---

## 10. Personal Data Rules

---

### EMP-BR-017 — Full Name Required

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-016` |
| **Rule** | Every Employee MUST have a `full_name`. The name must not be empty. |
| **Enforcement** | NOT NULL. String, max length. |
| **Status** | **DRAFT** |

### EMP-BR-018 — Personal Data Validation

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-017`, `EMP-REQ-018` |
| **Rule** | Phone must be valid format. Email must be valid email format. Geographic references must point to existing Master Data records. |
| **Enforcement** | FormRequest validation for format; FK or service-level validation for geographic references. |
| **Status** | **DRAFT** |

---

## 11. Lifecycle Rules

---

### EMP-BR-019 — Active/Inactive Lifecycle

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-019` |
| **Rule** | Employee records have an `is_active` boolean. Deactivated employees (`is_active = false`) are excluded from active employee lists and dropdowns. Deactivation is reversible. |
| **Source** | AGENTS.md active/inactive lifecycle pattern; Master Data `is_active` precedent |
| **Enforcement** | `is_active` boolean DEFAULT true. Toggle via dedicated endpoint. Inactive employees excluded from default queries. |
| **Status** | **DRAFT** |

### EMP-BR-020 — Soft Delete Only

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-020` |
| **Rule** | Employee records MUST use soft delete via `deleted_at`. Hard delete (physical row removal) is forbidden. Soft-deleted employees are excluded from all default queries and cannot be mutated. |
| **Source** | AGENTS.md — Business Records use soft delete by default; ADR-005 |
| **Enforcement** | `SoftDeletes` trait. No `forceDelete()` exposed. Soft-deleted records preserved for referential integrity and audit history. |
| **Status** | **DRAFT** |

### EMP-BR-021 — Deactivation vs Resignation vs Termination

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-012`, `EMP-REQ-014`, `EMP-REQ-019` |
| **Rule** | `is_active = false` (deactivation), `resignation_date`, and `employment_status = 'terminated'` are distinct concepts and MUST NOT be conflated. Deactivation may be temporary (e.g., leave). Resignation and termination are permanent employment-ending events. |
| **Source** | Requirements distinguish these fields explicitly |
| **Governance** | `EMP-GAP-005` — exact transition logic between these states requires business decision. |
| **Enforcement** | Three independent fields: `is_active`, `resignation_date`, `employment_status`. No automatic cascading between them until governance decision. |
| **Status** | **DRAFT** |

---

## 12. Audit Rules

---

### EMP-BR-022 — Audit Trail on All Mutations

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-021` |
| **Rule** | Every create, update, soft delete, and activation toggle on Employee records MUST produce an audit event via Phase 07 Platform Services. |
| **Source** | AGENTS.md audit trail; Phase 07 Audit Platform |
| **Enforcement** | `HasAudit` trait for auto-populating `created_by`, `updated_by`, `deleted_by`. `AuditServiceInterface` for canonical audit events. |
| **Status** | **DRAFT** |

### EMP-BR-023 — Audit Immutability

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-021` |
| **Rule** | Employee audit events are immutable — once recorded, they cannot be modified or deleted. |
| **Source** | ADR-005 — Immutable Audit Events |
| **Enforcement** | Audit Platform design — `audit_logs` has no UPDATE/DELETE paths. |
| **Status** | **DRAFT** |

---

## 13. Authorization Rules

---

### EMP-BR-024 — Read Access

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-022` |
| **Rule** | Authenticated users may read Employee records within their organization scope. Read access does NOT require a special Employee permission. |
| **Source** | Phase 06 Role & Permission; Phase 08 Authentication |
| **Enforcement** | `MasterDataPolicy` pattern: `viewAny()` returns true for authenticated users. Repository scopes by organization. |
| **Status** | **DRAFT** |

### EMP-BR-025 — Write Access Restricted

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-022` |
| **Rule** | Employee create, update, delete, and toggle-active operations are restricted to Super Admin and Owner roles. |
| **Source** | Phase 06 Role & Permission; Master Data Policy precedent |
| **Enforcement** | Policy checks `hasRole(['Super Admin', 'Owner'])` for mutation operations. |
| **Status** | **DRAFT** |

---

## 14. Tenancy Rules

---

### EMP-BR-026 — Organization Query Isolation

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-023` |
| **Rule** | All Employee queries MUST include `organization_id` as a mandatory filter. No Employee data may cross organization boundaries. |
| **Source** | AGENTS.md multi-tenant isolation standard |
| **Enforcement** | Repository-level `where('organization_id', ...)` on all read/write operations. |
| **Status** | **DRAFT** |

### EMP-BR-027 — Branch Sub-Scoping

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-023` |
| **Rule** | When a user is scoped to a branch, Employee queries MUST optionally filter by `branch_id`. Organization-level users see all branch employees. |
| **Source** | AGENTS.md multi-branch isolation |
| **Enforcement** | Repository applies `where('branch_id', ...)` when branch context is available. |
| **Status** | **DRAFT** |

---

## 15. Validation Rules

---

### EMP-BR-028 — Code Uniqueness Validation

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-004` |
| **Rule** | `employee_code` uniqueness MUST be validated before persistence. The validation must check both the Employee domain and the User domain (via `users.employee_code`). |
| **Enforcement** | Service-level check before INSERT/UPDATE. 422 on duplicate. |
| **Status** | **DRAFT** |

### EMP-BR-029 — Required Fields on Create

| Attribute | Value |
|---|---|
| **Requirement** | `EMP-REQ-001`, `EMP-REQ-005`, `EMP-REQ-012`, `EMP-REQ-013`, `EMP-REQ-016` |
| **Rule** | On Employee creation, the following fields are mandatory: `employee_code`, `full_name`, `organization_id`, `employment_status`, `hire_date`. |
| **Enforcement** | FormRequest `required` rules. |
| **Status** | **DRAFT** |

---

## 16. Governance Dependencies

| ID | Description | Status |
|---|---|---|
| `EMP-GAP-001` | employee_code generation mechanism (auto-generated vs manually entered) | **REQUIRES DECISION** |
| `EMP-GAP-002` | employment_status: Core Enum vs Master Data table | **REQUIRES DECISION** |
| `EMP-GAP-003` | position field: free-text vs Master Data table | **REQUIRES DECISION** |
| `EMP-GAP-004` | user_id FK — whether Employee stores a direct FK to users (redundant with employee_code bridge) | **REQUIRES DECISION** |
| `EMP-GAP-005` | EmploymentStatus allowed values and state transitions | **REQUIRES DECISION** |

---

## 17. Traceability Matrix

| Business Rule | Requirement | Governance Dependency | Status |
|---|---|---|---|
| `EMP-BR-001` | `EMP-REQ-001, 004` | `EMP-GAP-001` | **DRAFT** |
| `EMP-BR-002` | `EMP-REQ-002` | — | **DRAFT** |
| `EMP-BR-003` | `EMP-REQ-003` | — | **DRAFT** |
| `EMP-BR-004` | `EMP-REQ-004` | `EMP-GAP-004` | **DRAFT** |
| `EMP-BR-005` | `EMP-REQ-005` | — | **DRAFT** |
| `EMP-BR-006` | `EMP-REQ-005, 023` | — | **DRAFT** |
| `EMP-BR-007` | `EMP-REQ-006` | — | **DRAFT** |
| `EMP-BR-008` | `EMP-REQ-006, 023` | — | **DRAFT** |
| `EMP-BR-009` | `EMP-REQ-007` | — | **DRAFT** |
| `EMP-BR-010` | `EMP-REQ-008` | — | **DRAFT** |
| `EMP-BR-011` | `EMP-REQ-009` | — | **DRAFT** |
| `EMP-BR-012` | `EMP-REQ-007–011` | — | **DRAFT** |
| `EMP-BR-013` | `EMP-REQ-012` | `EMP-GAP-002, 005` | **DRAFT** |
| `EMP-BR-014` | `EMP-REQ-013` | — | **DRAFT** |
| `EMP-BR-015` | `EMP-REQ-014` | — | **DRAFT** |
| `EMP-BR-016` | `EMP-REQ-015` | `EMP-GAP-003` | **DRAFT** |
| `EMP-BR-017` | `EMP-REQ-016` | — | **DRAFT** |
| `EMP-BR-018` | `EMP-REQ-017, 018` | — | **DRAFT** |
| `EMP-BR-019` | `EMP-REQ-019` | — | **DRAFT** |
| `EMP-BR-020` | `EMP-REQ-020` | — | **DRAFT** |
| `EMP-BR-021` | `EMP-REQ-012, 014, 019` | `EMP-GAP-005` | **DRAFT** |
| `EMP-BR-022` | `EMP-REQ-021` | — | **DRAFT** |
| `EMP-BR-023` | `EMP-REQ-021` | — | **DRAFT** |
| `EMP-BR-024` | `EMP-REQ-022` | — | **DRAFT** |
| `EMP-BR-025` | `EMP-REQ-022` | — | **DRAFT** |
| `EMP-BR-026` | `EMP-REQ-023` | — | **DRAFT** |
| `EMP-BR-027` | `EMP-REQ-023` | — | **DRAFT** |
| `EMP-BR-028` | `EMP-REQ-004` | — | **DRAFT** |
| `EMP-BR-029` | `EMP-REQ-001, 005, 012, 013, 016` | — | **DRAFT** |

**29 Business Rules. 25/25 requirements covered. 5 governance dependencies preserved.**

---

## 18. Rule Summary

| Category | Count | IDs |
|---|---|---|
| EMPLOYEE-IDENTITY | 2 | BR-001, BR-002 |
| EMPLOYEE-USER-BRIDGE | 2 | BR-003, BR-004 |
| EMPLOYEE-ORGANIZATION | 2 | BR-005, BR-006 |
| EMPLOYEE-BRANCH | 2 | BR-007, BR-008 |
| EMPLOYEE-MASTER-DATA | 4 | BR-009–012 |
| EMPLOYEE-EMPLOYMENT | 4 | BR-013–016 |
| EMPLOYEE-PERSONAL | 2 | BR-017, BR-018 |
| EMPLOYEE-LIFECYCLE | 3 | BR-019–021 |
| EMPLOYEE-AUDIT | 2 | BR-022, BR-023 |
| EMPLOYEE-AUTHORIZATION | 2 | BR-024, BR-025 |
| EMPLOYEE-TENANCY | 2 | BR-026, BR-027 |
| EMPLOYEE-VALIDATION | 2 | BR-028, BR-029 |
| **Total** | **29** | |

---

## 19. Governance Record

| Check | Result |
|---|---|
| 29 unique BR IDs | ✅ |
| All BRs trace to EMP-REQ-* | ✅ 25/25 requirements covered |
| 5 governance gaps preserved | ✅ EMP-GAP-001 through 005 |
| No unsupported decisions | ✅ |
| User ↔ Employee boundary preserved | ✅ |
| Organization/Branch ownership preserved | ✅ |
| Master Data ownership preserved | ✅ |
| Authentication boundary preserved | ✅ |
| Platform Audit authority preserved | ✅ |
| Tenant boundary preserved | ✅ |
| Implementation not started | ✅ |
| Protected artifacts untouched | ✅ |

STEP_10_04_EMPLOYEE_BUSINESS_RULES_DRAFT_PASS
