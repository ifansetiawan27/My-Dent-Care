# Phase 25 — Reporting Requirements

**Date:** 2026-08-17 | **Phase:** 25 — Reporting | **Status:** STEP_25_01_DRAFT

## Requirements (RPT-REQ-001 through RPT-REQ-025)

Reporting domain manages generated reports, report parameters, and report data storage. Each report is scoped to an organization, categorized by report type, and stores parameters and generated data as JSONB.

### 1. Purpose

Provide a centralized report management system for storing and retrieving generated reports. Each report is scoped to an organization, categorized by report type, stores parameters used for generation and the resulting data as JSONB, and tracks status.

### 2. Scope

**In scope:**
- Report creation and management
- Report type classification
- JSONB parameter and data storage
- Status lifecycle (generated, archived)
- Report date tracking
- Organization tenancy
- Audit trail

**Out of scope:**
- Report generation engine
- Report template management
- Scheduled report generation
- Report export/email delivery
- Interactive report visualization

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Report Manager | Create, read, update reports |
| Doctor | Read reports |
| Staff | Read reports |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| RPT-REQ-001 | Identity | UUID PK per platform convention |
| RPT-REQ-002 | Tenant | Must belong to one Organization |
| RPT-REQ-003 | Report Type | Report type classification — required |
| RPT-REQ-004 | Name | Report name — required |
| RPT-REQ-005 | Parameters | JSONB parameters for report generation (optional) |
| RPT-REQ-006 | Data | JSONB generated report data (optional) |
| RPT-REQ-007 | Status | Status: generated, archived |
| RPT-REQ-008 | Report Date | Report date — required |
| RPT-REQ-009 | List | Organization-scoped listing with filter by report_type, status |
| RPT-REQ-010 | List | Search by name |
| RPT-REQ-011 | List | Sort by report_date, created_at |
| RPT-REQ-012 | List | Pagination support |
| RPT-REQ-013 | Create | Create report with type, name, parameters, data, date |
| RPT-REQ-014 | Update | Update report details, status |
| RPT-REQ-015 | Delete | Soft delete only |
| RPT-REQ-016 | Status Flow | Simple: generated ↔ archived |
| RPT-REQ-017 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| RPT-REQ-018 | Authorization | Read=authenticated, Write=Report Manager/Admin |
| RPT-REQ-019 | Tenant | All queries scoped to organization_id |
| RPT-REQ-020 | API | ApiResponse envelope |
| RPT-REQ-021 | API | Versioned under /api/v1/reports |
| RPT-REQ-022 | Sort | Sort by report_date |
| RPT-REQ-023 | Filter | Filter by report_type |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| RPT-NF-001 | Tenant isolation — no cross-organization data access |
| RPT-NF-002 | Authorization — Policy-based access control |
| RPT-NF-003 | Audit — all mutations traceable via Audit Platform |
| RPT-NF-004 | Performance — composite indexes on (organization_id, report_type) and report_date |
| RPT-NF-005 | Security — organization-level data isolation |

### 6. Out of Scope

- Report generation engine
- Report template management
- Scheduled report generation
- Report export/email delivery
- Interactive report visualization