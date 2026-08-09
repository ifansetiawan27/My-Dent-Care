# Phase 10 — Employee Requirements

**Date:** 2026-08-09
**Phase:** 10 — Employee
**SDLC Stage:** 01 — Requirement
**Status:** `STEP_10_02_EMPLOYEE_REQUIREMENTS_DRAFT`

**Traceability:**
- Roadmap: `AGENTS.md` FINAL LOCKED (line 333)
- Existing User: `app/Domains/User/` (Phase 05)
- Core Enums: `app/Core/Enums/`
- Master Data: Phase 09 (commit `8be7b26`)
- Platform: Phase 07 (commit `99ad776`)
- Auth: Phase 08 (frozen, commit `435c9f9`)

---

## 1. Document Control

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-08-09 | Platform Architect | Initial Requirements Draft |

---

## 2. Purpose

Define the Employee domain for Phase 10 — managing clinic employee/staff records, their organizational assignments, personal and employment data, and the bridge to the existing User (authentication) domain.

---

## 3. Scope

### In Scope
- Employee personal information (name, demographic references)
- Employee employment data (status, hire date, position)
- Organization and Branch assignment
- Bridge to existing `users` table via `employee_code`
- Reference to Master Data (gender, religion, marital status, nationality, geography)
- Employee lifecycle (active/inactive)
- Audit trail via Platform Services
- API for CRUD operations (admin access)

### Out of Scope
- Payroll processing (Phase 23 — HR)
- Attendance tracking (Phase 23 — HR)
- Leave management (Phase 23 — HR)
- Recruitment and onboarding workflows (Phase 23 — HR)
- Performance management (Phase 23 — HR)
- Credential/authentication lifecycle (Phase 08 — frozen)
- Employee medical records (Phase 14 — EMR)
- Doctor-specific clinical profile (Phase 11 — Doctor)

---

## 4. Authoritative Sources

| Source | Relevance |
|---|---|
| `AGENTS.md` line 333 | Phase 10 Employee — next phase |
| `users.employee_code` | Existing UNIQUE bridge column |
| `app/Core/Enums/Gender.php` | Pre-designated for Employee (HR) |
| `app/Core/Enums/MaritalStatus.php` | Pre-designated for Employee (HR) |
| `app/Core/Enums/Religion.php` | Pre-designated for Employee (HR) |
| `app/Core/Enums/BloodType.php` | Available if needed |
| Master Data Phase 09 | genders, religions, marital_statuses, nationalities, geography |
| Architecture Standards | Tenant isolation, audit trail, soft delete, API conventions |

---

## 5. Domain Context

Employee manages **staff records** for dental clinic organizations. Each employee is assigned to an organization and optionally a branch, has employment details, and is optionally linked to a User account via `employee_code`.

---

## 6. Employee Identity

An Employee is identified by a system-generated UUID (`id`) and a business identifier (`employee_code`) that serves as the bridge to the `users` table.

### EMP-REQ-001 — Employee Code as Business Identifier

**Statement:** Each employee MUST have a unique `employee_code` that serves as the human-readable business identifier. This code is also the bridge to the `users` table.

**Category:** EMPLOYEE-IDENTITY
**Priority:** CRITICAL
**Source:** `users.employee_code` UNIQUE column (Phase 05), `DD-AUTH-007.md` lines 647, 663
**Dependency:** `users.employee_code` already exists — must not duplicate
**Acceptance intent:** Employee records created with unique employee_code; code lookup returns Employee + linked User if applicable

### EMP-REQ-002 — UUID Primary Key

**Statement:** Every Employee record MUST use an ordered UUID as primary key, following established platform conventions.

**Category:** EMPLOYEE-IDENTITY
**Priority:** CRITICAL
**Source:** `AGENTS.md` — `use UUID (ordered) as primary key`
**Dependency:** None
**Acceptance intent:** Employee model uses `HasUuid` trait with `Str::orderedUuid()`

---

## 7. User ↔ Employee Bridge

### EMP-REQ-003 — Optional 1:1 Link to User

**Statement:** An Employee MAY be linked to exactly one User account via `employee_code`. The link is optional — employees may exist without a User account (e.g., staff not using the system), and Users may exist without an Employee record (e.g., Super Admins).

**Category:** EMPLOYEE-USER-BRIDGE
**Priority:** HIGH
**Source:** `users.employee_code` — nullable column; existing User domain does not require it
**Dependency:** `users` table (Phase 05)
**Acceptance intent:** Employee creation may include or omit `employee_code`; when code matches a User, the link is established

### EMP-REQ-004 — Employee Code Uniqueness

**Statement:** `employee_code` MUST be globally unique across employees and users. The existing `users.employee_code` UNIQUE constraint already enforces this at the database level. The Employee domain must validate uniqueness before persistence.

**Category:** EMPLOYEE-USER-BRIDGE
**Priority:** HIGH
**Source:** `users.employee_code` UNIQUE constraint; `UserService::isEmployeeCodeTaken()` (Phase 05)
**Dependency:** `users` table UNIQUE constraint
**Acceptance intent:** Creating an employee with an already-used code returns 422

---

## 8. Organization Dependency

### EMP-REQ-005 — Organization Assignment

**Statement:** Every Employee MUST belong to exactly one Organization. An employee cannot exist without an organization.

**Category:** EMPLOYEE-ORGANIZATION
**Priority:** CRITICAL
**Source:** AGENTS.md multi-organization isolation standard; Phase 03 Organization
**Dependency:** `organizations` table (Phase 03)
**Acceptance intent:** `organization_id` NOT NULL on employees table; FK RESTRICT

---

## 9. Branch Dependency

### EMP-REQ-006 — Branch Assignment

**Statement:** An Employee MAY be assigned to a Branch. Branch assignment is optional — some employees may work organization-wide.

**Category:** EMPLOYEE-BRANCH
**Priority:** MEDIUM
**Source:** Phase 04 Branch; AGENTS.md multi-branch standard
**Dependency:** `branches` table (Phase 04)
**Acceptance intent:** `branch_id` NULL on employees table; FK SET NULL

---

## 10. Master Data Dependencies

### EMP-REQ-007 — Gender Reference

**Statement:** Employee MUST reference gender via `gender` field using Core Enum `Gender` and Master Data `genders` table for dropdown display.

**Category:** EMPLOYEE-MASTER-DATA
**Priority:** HIGH
**Source:** `app/Core/Enums/Gender.php` — pre-designated for Employee (HR); Master Data `genders` table
**Dependency:** Core Enum `Gender`, Master Data `genders`
**Acceptance intent:** Employee gender field validates against `Gender` enum; dropdown uses Master Data table

### EMP-REQ-008 — Religion Reference

**Statement:** Employee MAY reference religion via Core Enum `Religion` and Master Data `religions` table.

**Category:** EMPLOYEE-MASTER-DATA
**Priority:** MEDIUM
**Source:** `app/Core/Enums/Religion.php` — pre-designated for Employee (HR)
**Dependency:** Core Enum `Religion`, Master Data `religions`

### EMP-REQ-009 — Marital Status Reference

**Statement:** Employee MAY reference marital status via Core Enum `MaritalStatus` and Master Data `marital_statuses` table.

**Category:** EMPLOYEE-MASTER-DATA
**Priority:** MEDIUM
**Source:** `app/Core/Enums/MaritalStatus.php` — pre-designated for Employee (HR)
**Dependency:** Core Enum `MaritalStatus`, Master Data `marital_statuses`

### EMP-REQ-010 — Nationality Reference

**Statement:** Employee MAY reference nationality via Master Data `nationalities` table.

**Category:** EMPLOYEE-MASTER-DATA
**Priority:** LOW
**Source:** Master Data `nationalities` — Employee listed as downstream consumer
**Dependency:** Master Data `nationalities`

### EMP-REQ-011 — Geographic References

**Statement:** Employee MAY reference geographic data (district, village) for address via Master Data tables.

**Category:** EMPLOYEE-MASTER-DATA
**Priority:** LOW
**Source:** Master Data geography — Employee listed as downstream consumer
**Dependency:** Master Data `districts`, `villages`

---

## 11. Employment Information

### EMP-REQ-012 — Employment Status

**Statement:** Every Employee MUST have an employment status indicating their current state (e.g., probation, permanent, contract, terminated).

**Category:** EMPLOYEE-EMPLOYMENT
**Priority:** HIGH
**Source:** `DD-AUTH-007.md` — Employee lifecycle; standard ERP requirement
**Dependency:** New `EmploymentStatus` enum to be created
**Acceptance intent:** Status field validates against EmploymentStatus enum; terminated employees excluded from active lists

### EMP-REQ-013 — Hire Date

**Statement:** Every Employee MUST have a `hire_date` indicating when they started employment.

**Category:** EMPLOYEE-EMPLOYMENT
**Priority:** HIGH
**Source:** Standard ERP employee record
**Dependency:** None
**Acceptance intent:** `hire_date` NOT NULL; validated as valid date in the past or present

### EMP-REQ-014 — Resignation Date

**Statement:** Employee MAY have a `resignation_date` indicating when employment ended.

**Category:** EMPLOYEE-EMPLOYMENT
**Priority:** MEDIUM
**Source:** Standard ERP employee record
**Dependency:** None
**Acceptance intent:** `resignation_date` NULL; must be after `hire_date` when set

### EMP-REQ-015 — Position / Job Title

**Statement:** Employee MAY have a `position` field indicating their job title or role within the clinic.

**Category:** EMPLOYEE-EMPLOYMENT
**Priority:** MEDIUM
**Source:** Standard ERP requirement
**Dependency:** None
**Acceptance intent:** Free-text `position` field

---

## 12. Personal Information

### EMP-REQ-016 — Full Name

**Statement:** Every Employee MUST have a `full_name` field.

**Category:** EMPLOYEE-PERSONAL
**Priority:** CRITICAL
**Source:** Standard identity requirement
**Dependency:** None
**Acceptance intent:** `full_name` NOT NULL

### EMP-REQ-017 — Contact Information

**Statement:** Employee MAY have `phone` and `email` contact fields.

**Category:** EMPLOYEE-PERSONAL
**Priority:** MEDIUM
**Source:** Standard ERP requirement
**Dependency:** None

### EMP-REQ-018 — Address

**Statement:** Employee MAY have `address` text field and geographic references.

**Category:** EMPLOYEE-PERSONAL
**Priority:** LOW
**Source:** Standard ERP requirement
**Dependency:** Master Data geography

---

## 13. Employee Lifecycle

### EMP-REQ-019 — Active/Inactive Status

**Statement:** Employee MUST support activation and deactivation. Deactivated employees are excluded from active employee lists and dropdowns.

**Category:** EMPLOYEE-LIFECYCLE
**Priority:** HIGH
**Source:** AGENTS.md — active/inactive lifecycle; `is_active` pattern from Master Data
**Dependency:** None
**Acceptance intent:** `is_active` boolean; toggle via dedicated endpoint; deactivated employees preserved for historical records

### EMP-REQ-020 — Soft Delete

**Statement:** Employee records MUST use soft delete — never hard deleted.

**Category:** EMPLOYEE-LIFECYCLE
**Priority:** HIGH
**Source:** AGENTS.md — Business Records use soft delete by default; ADR-005
**Dependency:** `SoftDeletes` trait
**Acceptance intent:** `deleted_at` column; soft-deleted employees excluded from default queries

---

## 14. Cross-Cutting Requirements

### EMP-REQ-021 — Audit Trail

**Statement:** All Employee create, update, and delete operations MUST record audit events via Phase 07 Platform Services.

**Category:** EMPLOYEE-AUDIT
**Priority:** HIGH
**Source:** AGENTS.md audit trail standard; Phase 07 Platform Services
**Dependency:** `AuditServiceInterface` (Phase 07)
**Acceptance intent:** Audit columns (`created_by`, `updated_by`, `deleted_by`) populated via `HasAudit`

### EMP-REQ-022 — Authorization

**Statement:** Employee read operations are available to authenticated users. Write operations are restricted to Super Admin and Owner roles.

**Category:** CROSS-CUTTING
**Priority:** HIGH
**Source:** Phase 06 Role & Permission; Phase 08 Authentication (frozen consumer)
**Dependency:** Spatie permissions
**Acceptance intent:** Policy enforces read/write boundaries; 403 on unauthorized mutations

### EMP-REQ-023 — Tenant Isolation

**Statement:** Employee queries MUST be scoped by `organization_id` and optionally `branch_id`. Cross-organization access is forbidden.

**Category:** CROSS-CUTTING
**Priority:** CRITICAL
**Source:** AGENTS.md multi-tenant isolation standard
**Dependency:** Phase 03 Organization, Phase 04 Branch
**Acceptance intent:** Repository enforces tenant scoping on all queries

### EMP-REQ-024 — API Convention

**Statement:** All Employee endpoints MUST follow existing DentalERP conventions: `ApiResponse` envelope, RESTful plural resources, UUID identifiers, OpenAPI 3.1 documentation.

**Category:** CROSS-CUTTING
**Priority:** HIGH
**Source:** AGENTS.md API First standard
**Dependency:** None
**Acceptance intent:** Endpoints under `/api/v1/employees`; `ApiResponse` envelope

### EMP-REQ-025 — Validation

**Statement:** All Employee input MUST be validated via FormRequest. Validation rules must be whitelisted and consistent with business rules.

**Category:** CROSS-CUTTING
**Priority:** HIGH
**Source:** AGENTS.md validation standard
**Dependency:** None

---

## 15. Data Ownership

| Domain | Owns |
|---|---|
| **Employee** | `employees` table (id, employee_code, full_name, phone, email, address, position, hire_date, resignation_date, employment_status, is_active) |
| **User** | `users` table (authentication identity, credentials) |
| **Organization** | `organizations` table |
| **Branch** | `branches` table |
| **Master Data** | genders, religions, marital_statuses, nationalities, geography |
| **Authentication** | Identity, tokens, sessions |
| **Platform Services** | Audit log, operational log |

---

## 16. Out of Scope

| Area | Phase |
|---|---|
| Payroll processing | 23 — HR |
| Attendance tracking | 23 — HR |
| Leave management | 23 — HR |
| Recruitment workflows | 23 — HR |
| Performance management | 23 — HR |
| Credential/authentication lifecycle | 08 — FROZEN |
| Employee medical records | 14 — EMR |
| Doctor clinical profile | 11 — Doctor |
| Employee scheduling | 13 — Appointment (future) |

---

## 17. Requirement Traceability

| ID | Category | Source | Dependency | Status |
|---|---|---|---|---|
| EMP-REQ-001 | Identity | `users.employee_code` | users table | **DRAFT** |
| EMP-REQ-002 | Identity | AGENTS.md | None | **DRAFT** |
| EMP-REQ-003 | User Bridge | `users.employee_code` | users | **DRAFT** |
| EMP-REQ-004 | User Bridge | `users.employee_code` UNIQUE | users | **DRAFT** |
| EMP-REQ-005 | Organization | AGENTS.md tenant standard | organizations | **DRAFT** |
| EMP-REQ-006 | Branch | Phase 04 Branch | branches | **DRAFT** |
| EMP-REQ-007 | Master Data | Core Enum Gender | genders, Gender | **DRAFT** |
| EMP-REQ-008 | Master Data | Core Enum Religion | religions, Religion | **DRAFT** |
| EMP-REQ-009 | Master Data | Core Enum MaritalStatus | marital_statuses | **DRAFT** |
| EMP-REQ-010 | Master Data | Master Data Requirement | nationalities | **DRAFT** |
| EMP-REQ-011 | Master Data | Master Data Requirement | districts, villages | **DRAFT** |
| EMP-REQ-012 | Employment | DD-AUTH-007 | EmploymentStatus enum | **DRAFT** |
| EMP-REQ-013 | Employment | Standard ERP | None | **DRAFT** |
| EMP-REQ-014 | Employment | Standard ERP | None | **DRAFT** |
| EMP-REQ-015 | Employment | Standard ERP | None | **DRAFT** |
| EMP-REQ-016 | Personal | Standard identity | None | **DRAFT** |
| EMP-REQ-017 | Personal | Standard ERP | None | **DRAFT** |
| EMP-REQ-018 | Personal | Standard ERP | Master Data geography | **DRAFT** |
| EMP-REQ-019 | Lifecycle | AGENTS.md | None | **DRAFT** |
| EMP-REQ-020 | Lifecycle | AGENTS.md, ADR-005 | SoftDeletes | **DRAFT** |
| EMP-REQ-021 | Audit | AGENTS.md, Phase 07 | AuditServiceInterface | **DRAFT** |
| EMP-REQ-022 | Cross-cutting | Phase 06, 08 | Spatie permissions | **DRAFT** |
| EMP-REQ-023 | Cross-cutting | AGENTS.md tenant | Organization, Branch | **DRAFT** |
| EMP-REQ-024 | Cross-cutting | AGENTS.md API First | None | **DRAFT** |
| EMP-REQ-025 | Cross-cutting | AGENTS.md | None | **DRAFT** |

---

## 18. Requirement Summary

| Category | Count |
|---|---|
| Functional requirements (total) | **20** (EMP-REQ-001 through 020) |
| Cross-cutting requirements | **5** (EMP-REQ-021 through 025) |
| **Total** | **25** |

---

## 19. Open Questions / Governance Gaps

| # | Question | Status |
|---|---|---|
| 1 | Should `employee_code` be auto-generated or manually entered? | **REQUIRES BUSINESS DECISION** |
| 2 | Should `employment_status` use a Core Enum or Master Data table? | **REQUIRES ARCHITECTURE DECISION** |
| 3 | Should `position` be a free-text field or reference a Master Data table? | **REQUIRES BUSINESS DECISION** |
| 4 | Should Employee use `user_id` FK (redundant with employee_code lookup)? | **REQUIRES ARCHITECTURE DECISION** |
| 5 | What are the allowed EmploymentStatus values and transitions? | **REQUIRES BUSINESS DECISION** |

---

## 20. Draft Status

All 25 requirements are marked **DRAFT** pending STEP_10_03 validation.

---

## Governance Record

| Check | Result |
|---|---|
| Unique EMP-REQ-* IDs (001–025) | ✅ |
| Every requirement has source/evidence | ✅ |
| No unsupported functionality presented as fact | ✅ |
| User ↔ Employee boundary explicitly documented | ✅ §7 |
| Organization/Branch dependency documented | ✅ §8, §9 |
| Master Data dependency documented | ✅ §10 |
| Ownership boundaries documented | ✅ §15 |
| Out-of-scope documented | ✅ §16 |
| Governance gaps explicitly identified | ✅ §19 (5 items) |
| Implementation not started | ✅ |
| Protected artifacts untouched | ✅ |

STEP_10_02_EMPLOYEE_REQUIREMENTS_DRAFT_PASS
