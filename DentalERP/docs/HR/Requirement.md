# Phase 23 — HR Requirements

**Date:** 2026-08-17 | **Phase:** 23 — HR | **Status:** STEP_23_01_DRAFT

## Requirements (HR-REQ-001 through HR-REQ-025)

HR domain manages employee records, HR documents, and employment lifecycle events. Each HR record is scoped to an organization, may reference an employee, and tracks record type with status lifecycle.

### 1. Purpose

Provide a centralized HR record management system for employee documentation, employment changes, performance reviews, and other HR-related records. Each record is scoped to an organization, may reference an employee, stores structured data as JSONB, and tracks status.

### 2. Scope

**In scope:**
- HR record creation and management
- Record type classification
- Employee reference (optional)
- Status lifecycle (active, inactive, archived)
- JSONB data storage for flexible record types
- Effective date and end date tracking
- Organization tenancy
- Audit trail

**Out of scope:**
- Employee master data management (Phase 10 Employee)
- Payroll processing
- Leave management
- Attendance tracking
- Recruitment workflow

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| HR Manager | Create, read, update HR records |
| Doctor | Read HR records |
| Staff | Read own HR records |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| HR-REQ-001 | Identity | UUID PK per platform convention |
| HR-REQ-002 | Tenant | Must belong to one Organization |
| HR-REQ-003 | Employee | May reference an Employee (optional) |
| HR-REQ-004 | Record Type | Record type classification — required |
| HR-REQ-005 | Status | Status lifecycle: active, inactive, archived |
| HR-REQ-006 | Effective Date | Effective date — required |
| HR-REQ-007 | End Date | End date (optional) |
| HR-REQ-008 | Data | Flexible JSONB data storage (optional) |
| HR-REQ-009 | Notes | Free-text notes (optional) |
| HR-REQ-010 | List | Organization-scoped listing with filter by record_type, status |
| HR-REQ-011 | List | Search by notes, record_type |
| HR-REQ-012 | List | Sort by effective_date, created_at, status |
| HR-REQ-013 | List | Pagination support |
| HR-REQ-014 | Create | Create record with type, employee, dates, data |
| HR-REQ-015 | Update | Update record details, status transitions |
| HR-REQ-016 | Delete | Soft delete only |
| HR-REQ-017 | Status Flow | Validate status transitions |
| HR-REQ-018 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| HR-REQ-019 | Authorization | Read=authenticated, Write=HR Manager/Admin |
| HR-REQ-020 | Tenant | All queries scoped to organization_id |
| HR-REQ-021 | API | ApiResponse envelope |
| HR-REQ-022 | API | Versioned under /api/v1/hr-records |
| HR-REQ-023 | Filter | Filter by employee_id |
| HR-REQ-024 | Sort | Sort by effective_date |
| HR-REQ-025 | Archived | Archived is terminal state |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| HR-NF-001 | Tenant isolation — no cross-organization data access |
| HR-NF-002 | Authorization — Policy-based access control |
| HR-NF-003 | Audit — all mutations traceable via Audit Platform |
| HR-NF-004 | Performance — composite indexes on (organization_id, record_type) and employee_id |
| HR-NF-005 | Security — organization-level data isolation |
| HR-NF-006 | Flexibility — JSONB data allows heterogeneous record types |

### 6. Out of Scope

- Employee master data management (Phase 10 Employee)
- Payroll processing
- Leave management
- Attendance tracking
- Recruitment workflow