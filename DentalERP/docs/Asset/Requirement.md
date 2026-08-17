# Phase 22 — Asset Requirements

**Date:** 2026-08-17 | **Phase:** 22 — Asset | **Status:** STEP_22_01_DRAFT

## Requirements (ASST-REQ-001 through ASST-REQ-025)

Asset domain manages fixed assets, equipment, and clinic property. Each asset is scoped to an organization, may be linked to a branch and category, and tracks lifecycle status from active through to disposed.

### 1. Purpose

Provide a centralized asset management system for dental clinic equipment, furniture, IT hardware, and other fixed assets. Each asset is scoped to an organization, may be assigned to a branch, categorized for accounting, and tracked through its lifecycle.

### 2. Scope

**In scope:**
- Asset creation and management
- Asset lifecycle (active, maintenance, retired, disposed)
- Asset categorization (asset_categories)
- Branch assignment (optional)
- Asset code tracking (unique identifier)
- Purchase date and price recording
- Warranty expiry tracking
- Organization/branch tenancy
- Audit trail

**Out of scope:**
- Asset depreciation calculation
- Asset maintenance scheduling
- Asset transfer history
- Asset insurance tracking
- Asset disposal approval workflow

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Asset Manager | Create, read, update assets |
| Doctor | Read assets |
| Staff | Read assets |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| ASST-REQ-001 | Identity | UUID PK per platform convention |
| ASST-REQ-002 | Tenant | Must belong to one Organization |
| ASST-REQ-003 | Branch | May reference a Branch (optional) |
| ASST-REQ-004 | Category | May reference an Asset Category (optional) |
| ASST-REQ-005 | Code | Asset code — unique within system |
| ASST-REQ-006 | Name | Asset name — required |
| ASST-REQ-007 | Description | Free-text description (optional) |
| ASST-REQ-008 | Purchase Date | Purchase date (optional) |
| ASST-REQ-009 | Purchase Price | Purchase price (optional) |
| ASST-REQ-010 | Status | Status lifecycle: active, maintenance, retired, disposed |
| ASST-REQ-011 | Warranty | Warranty expiry date (optional) |
| ASST-REQ-012 | Notes | Free-text notes (optional) |
| ASST-REQ-013 | List | Organization-scoped listing with filter by status |
| ASST-REQ-014 | List | Search by name, asset_code, description |
| ASST-REQ-015 | List | Sort by name, created_at, purchase_date |
| ASST-REQ-016 | List | Pagination support |
| ASST-REQ-017 | Create | Create asset with code, name, category, branch, purchase details |
| ASST-REQ-018 | Update | Update asset details, status, lifecycle |
| ASST-REQ-019 | Delete | Soft delete only |
| ASST-REQ-020 | Status Flow | Validate status transitions |
| ASST-REQ-021 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| ASST-REQ-022 | Authorization | Read=authenticated, Write=Asset Manager/Admin |
| ASST-REQ-023 | Tenant | All queries scoped to organization_id |
| ASST-REQ-024 | API | ApiResponse envelope |
| ASST-REQ-025 | API | Versioned under /api/v1/assets |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| ASST-NF-001 | Tenant isolation — no cross-organization data access |
| ASST-NF-002 | Authorization — Policy-based access control |
| ASST-NF-003 | Audit — all mutations traceable via Audit Platform |
| ASST-NF-004 | Performance — composite indexes on (organization_id, status) and category_id |
| ASST-NF-005 | Security — organization-level data isolation |
| ASST-NF-006 | Consistency — purchase_price stored as decimal for precision |

### 6. Out of Scope

- Asset depreciation calculation
- Asset maintenance scheduling
- Asset transfer history
- Asset insurance tracking
- Asset disposal approval workflow