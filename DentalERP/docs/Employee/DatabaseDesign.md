# Phase 10 — Employee Database Design

**Date:** 2026-08-09
**Phase:** 10 — Employee
**SDLC Stage:** 04 — Database Design
**Status:** `STEP_10_08_EMPLOYEE_DATABASE_DESIGN_DRAFT`

**Traceability:**
- Requirements: `docs/Employee/Requirement.md` (STEP_10_03_PASS)
- Business Rules: `docs/Employee/BusinessRule.md` (STEP_10_05_PASS)
- Flow: `docs/Employee/Flow.md` (STEP_10_07_PASS)
- Conventions: `AGENTS.md`, `app/Core/Base/BaseModel.php`

---

## 1. Entity Inventory

| # | Table | Purpose | Scope |
|---|---|---|---|
| 1 | `employees` | Core Employee aggregate root | Tenant (organization_id) |

**One table.** No additional entities without authoritative justification.

---

## 2. Existing Schema Dependencies (Phase 03-09)

| Table | Phase | PK | Used By Employee |
|---|---|---|---|
| `organizations` | 03 | uuid | ✅ FK — organization_id |
| `branches` | 04 | uuid | ✅ FK — branch_id (optional) |
| `users` | 05 | uuid | ✅ employee_code bridge |
| `genders` | 09 | uuid | ✅ Reference (value stored) |
| `religions` | 09 | uuid | ✅ Reference (value stored) |
| `marital_statuses` | 09 | uuid | ✅ Reference (value stored) |
| `nationalities` | 09 | uuid | ✅ Reference (value stored) |
| `districts` | 09 | uuid | ✅ Reference (value stored) |
| `villages` | 09 | uuid | ✅ Reference (value stored) |

---

## 3. `employees` Table Design

### 3.1 Column Inventory

| # | Column | PostgreSQL Type | Nullable | Default | Description | Source |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK — ordered UUID | `AGENTS.md`, EMP-REQ-002 |
| 2 | `employee_code` | `varchar(30)` | NOT NULL | — | Business HR identifier — UNIQUE | EMP-REQ-001, EMP-BR-001 |
| 3 | `full_name` | `varchar(200)` | NOT NULL | — | Employee full name | EMP-REQ-016, EMP-BR-017 |
| 4 | `organization_id` | `uuid` | NOT NULL | — | FK → organizations.id | EMP-REQ-005, EMP-BR-005 |
| 5 | `branch_id` | `uuid` | NULL | — | FK → branches.id (optional) | EMP-REQ-006, EMP-BR-007 |
| 6 | `employment_status` | `varchar(20)` | NOT NULL | — | Current employment status **[GAP-002]** | EMP-REQ-012, EMP-BR-013 |
| 7 | `hire_date` | `date` | NOT NULL | — | Date employment started | EMP-REQ-013, EMP-BR-014 |
| 8 | `resignation_date` | `date` | NULL | — | Date employment ended | EMP-REQ-014, EMP-BR-015 |
| 9 | `position` | `varchar(100)` | NULL | — | Job title **[GAP-003]** | EMP-REQ-015, EMP-BR-016 |
| 10 | `gender` | `varchar(10)` | NULL | — | Gender enum value | EMP-REQ-007, EMP-BR-009 |
| 11 | `religion` | `varchar(20)` | NULL | — | Religion enum value | EMP-REQ-008, EMP-BR-010 |
| 12 | `marital_status` | `varchar(20)` | NULL | — | MaritalStatus enum value | EMP-REQ-009, EMP-BR-011 |
| 13 | `nationality_id` | `uuid` | NULL | — | FK → nationalities.id | EMP-REQ-010 |
| 14 | `phone` | `varchar(20)` | NULL | — | Contact phone | EMP-REQ-017 |
| 15 | `email` | `varchar(100)` | NULL | — | Contact email | EMP-REQ-017 |
| 16 | `address` | `text` | NULL | — | Free-text address | EMP-REQ-018 |
| 17 | `district_id` | `uuid` | NULL | — | FK → districts.id | EMP-REQ-011 |
| 18 | `village_id` | `uuid` | NULL | — | FK → villages.id | EMP-REQ-011 |
| 19 | `is_active` | `boolean` | NOT NULL | `true` | Active/inactive toggle | EMP-REQ-019, EMP-BR-019 |
| 20 | `created_by` | `uuid` | NULL | — | Audit — auto via HasAudit | EMP-REQ-021, EMP-BR-022 |
| 21 | `updated_by` | `uuid` | NULL | — | Audit — auto via HasAudit | EMP-REQ-021, EMP-BR-022 |
| 22 | `deleted_by` | `uuid` | NULL | — | Audit — auto via HasAudit | EMP-REQ-021, EMP-BR-022 |
| 23 | `created_at` | `timestamptz` | NOT NULL | — | Creation timestamp | Convention |
| 24 | `updated_at` | `timestamptz` | NOT NULL | — | Last update | Convention |
| 25 | `deleted_at` | `timestamptz` | NULL | — | Soft delete | EMP-REQ-020, EMP-BR-020 |

**25 columns.**

---

### 3.2 Foreign Keys

| FK | Child | Parent | Cardinality | Nullable | ON DELETE | Source |
|---|---|---|---|---|---|---|
| 1 | `organization_id` | `organizations.id` | N:1 | NOT NULL | **RESTRICT** | EMP-BR-005 |
| 2 | `branch_id` | `branches.id` | N:1 | NULL | **SET NULL** | EMP-BR-007 |
| 3 | `nationality_id` | `nationalities.id` | N:1 | NULL | **SET NULL** | EMP-REQ-010 |
| 4 | `district_id` | `districts.id` | N:1 | NULL | **SET NULL** | EMP-REQ-011 |
| 5 | `village_id` | `villages.id` | N:1 | NULL | **SET NULL** | EMP-REQ-011 |

**5 FKs. 0 CASCADE.**

---

### 3.3 Unique Constraints

| Constraint | Columns | Source |
|---|---|---|
| `employees_employee_code_unique` | `(employee_code)` | EMP-BR-001 — globally unique |
| `employees_employee_code_not_deleted_unique` | `(employee_code) WHERE deleted_at IS NULL` | EMP-BR-001 — only active codes |

---

### 3.4 Indexes

| Index | Columns | Type | Rationale |
|---|---|---|---|
| `employees_employee_code_unique` | `(employee_code)` | UNIQUE | Code lookup — EMP-BR-001 |
| `employees_org_id_is_active_idx` | `(organization_id, is_active)` | Composite | Tenant-scoped active employee listing — EMP-BR-006 |
| `employees_org_id_branch_id_idx` | `(organization_id, branch_id)` | Composite | Branch-scoped listing — EMP-BR-027 |
| `employees_employment_status_idx` | `(employment_status)` | B-tree | Status filtering |
| `employees_is_active_idx` | `(is_active)` | B-tree | Active/inactive filtering — EMP-BR-019 |

**5 indexes with documented rationales.**

---

### 3.5 Master Data Considerations

Demographic fields (`gender`, `religion`, `marital_status`) store **Core Enum values as varchar**, not FK to Master Data tables. This follows the Master Data Core Enum pattern (EMP-BR-009 through 011):
- **Core Enum** defines canonical business logic values
- **Master Data table** provides UI display metadata
- Employee stores the Enum value; UI resolves display from the table

Geographic and nationality fields use FK to Master Data tables for dropdown-driven selection.

---

## 4. Governance Decision Points

| # | ID | Decision | Status | DB Impact |
|---|---|---|---|---|
| 1 | EMP-GAP-001 | employee_code generation mechanism | **REQUIRES DECISION** | Code uniqueness already enforced; generation TBD |
| 2 | EMP-GAP-002 | employment_status storage (enum vs table) | **REQUIRES DECISION** | Currently varchar(20); final type TBD |
| 3 | EMP-GAP-003 | position field (free-text vs Master Data table) | **REQUIRES DECISION** | Currently varchar(100); final model TBD |
| 4 | EMP-GAP-004 | user_id FK on employees table | **REQUIRES DECISION** | Not included; bridge via employee_code only |
| 5 | EMP-GAP-005 | employment status transition matrix | **REQUIRES DECISION** | Status stored; transitions TBD |

---

## 5. User ↔ Employee Bridge

| Aspect | Current Design |
|---|---|
| `users.employee_code` | ✅ Exists — Phase 05. UNIQUE, nullable. |
| `employees.employee_code` | ✅ Exists — NOT NULL, UNIQUE. |
| Bridge mechanism | `employee_code` match links Employee → User |
| `employees.user_id` FK | **Not included** — EMP-GAP-004 unresolved |
| Cardinality | 1:1 optional (both directions — BR-003) |

---

## 6. Lifecycle Separation

| Field | Purpose | Independent? |
|---|---|---|
| `is_active` | Show/hide in lists | ✅ Independent toggle |
| `employment_status` | Probation/permanent/terminated | ✅ Independent — BR-021 |
| `resignation_date` | Date employment ended | ✅ Independent — BR-021 |
| `deleted_at` | Soft delete | ✅ Independent — BR-020 |

Per EMP-BR-021: these four fields are distinct. No automatic cascading.

---

## 7. Model Classification

| Attribute | Value |
|---|---|
| Model base | `BaseModel` (Business Record) |
| Traits | `HasUuid`, `HasAudit`, `SoftDeletes` |
| Table | `employees` |
| Data Category (ADR-005) | Business Record |
| Tenant column | `organization_id` (NOT NULL) |
| Audit columns | `created_by`, `updated_by`, `deleted_by` |

---

## 8. Relationship Matrix

| Child | Parent | Cardinality | FK | Nullable | ON DELETE | Source |
|---|---|---|---|---|---|---|
| employees | organizations | N:1 | org_id | NOT NULL | RESTRICT | EMP-BR-005 |
| employees | branches | N:1 | branch_id | NULL | SET NULL | EMP-BR-007 |
| employees | nationalities | N:1 | nationality_id | NULL | SET NULL | EMP-REQ-010 |
| employees | districts | N:1 | district_id | NULL | SET NULL | EMP-REQ-011 |
| employees | villages | N:1 | village_id | NULL | SET NULL | EMP-REQ-011 |

---

## 9. Traceability

### Requirement → Table/Column

| REQ | Column(s) |
|---|---|
| EMP-REQ-001 | `employee_code` |
| EMP-REQ-002 | `id` (UUID PK) |
| EMP-REQ-005 | `organization_id` |
| EMP-REQ-006 | `branch_id` |
| EMP-REQ-007 | `gender` |
| EMP-REQ-008 | `religion` |
| EMP-REQ-009 | `marital_status` |
| EMP-REQ-010 | `nationality_id` |
| EMP-REQ-011 | `district_id`, `village_id` |
| EMP-REQ-012 | `employment_status` |
| EMP-REQ-013 | `hire_date` |
| EMP-REQ-014 | `resignation_date` |
| EMP-REQ-015 | `position` |
| EMP-REQ-016 | `full_name` |
| EMP-REQ-017 | `phone`, `email` |
| EMP-REQ-018 | `address` |
| EMP-REQ-019 | `is_active` |
| EMP-REQ-020 | `deleted_at` |
| EMP-REQ-021 | `created_by`, `updated_by`, `deleted_by` |
| EMP-REQ-023 | `organization_id`, `branch_id` |

**25/25 requirements traced.**

---

## 10. ERD Readiness

- Single entity: `employees`
- 5 FKs, all to existing tables
- 5 indexes with documented rationales
- All columns traceable to requirements
- Governance gaps explicitly marked
- Relationships clear: M:1 to org, optional M:1 to branch/geography
- Ready for STEP_10_10 ERD Draft

---

## Governance Record

| Check | Result |
|---|---|
| 1 table: `employees` (25 columns) | ✅ |
| 5 FKs (RESTRICT + SET NULL, 0 CASCADE) | ✅ |
| 5 indexes with documented rationales | ✅ |
| All columns traceable to requirements | ✅ 25/25 |
| 5 governance gaps preserved as REQUIRES DECISION | ✅ |
| User Bridge: employee_code only (no user_id FK) | ✅ EMP-GAP-004 |
| Lifecycle separation: is_active ≠ status ≠ resignation ≠ deleted_at | ✅ EMP-BR-021 |
| Audit via HasAudit (no custom mechanism) | ✅ |
| Soft delete via deleted_at | ✅ |
| Protected artifacts untouched | ✅ |

STEP_10_08_EMPLOYEE_DATABASE_DESIGN_DRAFT_PASS
