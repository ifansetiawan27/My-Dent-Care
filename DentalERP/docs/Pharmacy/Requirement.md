# Phase 19 — Pharmacy Requirements

**Date:** 2026-08-17 | **Phase:** 19 — Pharmacy | **Status:** STEP_19_01_DRAFT

## Requirements (PHARM-REQ-001 through PHARM-REQ-025)

Pharmacy domain manages pharmaceutical items, drug tracking, expiry date monitoring, and batch management. Each pharmacy item is scoped to an organization, may be linked to a branch, and tracks stock levels with expiry date and batch number metadata.

### 1. Purpose

Provide a centralized pharmacy management system for dental medications, drugs, and pharmaceutical supplies. Each pharmacy item is scoped to an organization, may be assigned to a branch, categorized by drug type, and tracks quantity with expiry date and batch number for compliance and safety.

### 2. Scope

**In scope:**
- Pharmacy item creation and management
- Drug code uniqueness tracking
- Category classification (optional)
- Branch assignment (optional)
- Quantity tracking with default 0
- Unit of measurement tracking
- Unit price recording
- Expiry date monitoring
- Batch number tracking
- Active/inactive status management
- Organization/branch tenancy
- Audit trail

**Out of scope:**
- Prescription management
- Drug dispensing workflow
- Stock movement/transaction history
- Supplier management
- Barcode/QR code generation
- Drug interaction checks

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Pharmacy Staff | Create, read, update pharmacy items |
| Doctor | Read pharmacy items |
| Staff | Read pharmacy items |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| PHARM-REQ-001 | Identity | UUID PK per platform convention |
| PHARM-REQ-002 | Tenant | Must belong to one Organization |
| PHARM-REQ-003 | Branch | May reference a Branch (optional) |
| PHARM-REQ-004 | Code | Drug code — unique within system |
| PHARM-REQ-005 | Name | Drug name — required |
| PHARM-REQ-006 | Category | Drug category (optional) |
| PHARM-REQ-007 | Quantity | Current quantity — default 0 |
| PHARM-REQ-008 | Unit | Unit of measurement (optional) |
| PHARM-REQ-009 | Unit Price | Unit price (optional) |
| PHARM-REQ-010 | Expiry Date | Expiry date tracking (optional) |
| PHARM-REQ-011 | Batch Number | Batch/lot number (optional) |
| PHARM-REQ-012 | Active | Active/inactive status — default true |
| PHARM-REQ-013 | List | Organization-scoped listing with filter by branch_id, category, is_active, expiry_date |
| PHARM-REQ-014 | List | Search by name, drug_code |
| PHARM-REQ-015 | List | Sort by name, drug_code, expiry_date |
| PHARM-REQ-016 | List | Pagination support |
| PHARM-REQ-017 | Create | Create pharmacy item with drug_code, name, category, branch, expiry_date, batch_number |
| PHARM-REQ-018 | Update | Update item details, quantity, price, active status, expiry, batch |
| PHARM-REQ-019 | Delete | Soft delete only |
| PHARM-REQ-020 | Toggle | Toggle active/inactive status |
| PHARM-REQ-021 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| PHARM-REQ-022 | Authorization | Read=authenticated, Write=Pharmacy Staff/Admin |
| PHARM-REQ-023 | Tenant | All queries scoped to organization_id |
| PHARM-REQ-024 | API | ApiResponse envelope |
| PHARM-REQ-025 | API | Versioned under /api/v1/pharmacy-items |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| PHARM-NF-001 | Tenant isolation — no cross-organization data access |
| PHARM-NF-002 | Authorization — Policy-based access control |
| PHARM-NF-003 | Audit — all mutations traceable via Audit Platform |
| PHARM-NF-004 | Performance — composite index on (organization_id, is_active), expiry_date, batch_number |
| PHARM-NF-005 | Security — organization-level data isolation |
| PHARM-NF-006 | Consistency — unit_price and quantity stored as decimal for precision |

### 6. Out of Scope

- Prescription management
- Drug dispensing workflow
- Stock movement tracking
- Supplier management
- Drug interaction checks
- Barcode generation