# Phase 18 — Inventory Requirements

**Date:** 2026-08-17 | **Phase:** 18 — Inventory | **Status:** STEP_18_01_DRAFT

## Requirements (INV-REQ-001 through INV-REQ-025)

Inventory domain manages stock items, quantity tracking, reorder thresholds, and item categorization. Each inventory item is scoped to an organization, may be linked to a branch and category, and tracks current stock levels with minimum quantity alerts.

### 1. Purpose

Provide a centralized inventory management system for dental supplies, materials, and equipment. Each inventory item is scoped to an organization, may be assigned to a branch, categorized for organization, and tracks quantity with minimum threshold alerts.

### 2. Scope

**In scope:**
- Inventory item creation and management
- Quantity tracking with minimum threshold alerts
- Item categorization (inventory_categories)
- Branch assignment (optional)
- Unit of measurement tracking
- Unit price recording
- Active/inactive status management
- Organization/branch tenancy
- Audit trail

**Out of scope:**
- Stock movement/transaction history (Phase 21 Procurement)
- Purchase order creation (Phase 21 Procurement)
- Supplier management (Phase 21 Procurement)
- Batch/lot tracking
- Expiry date tracking
- Barcode/QR code generation

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Inventory Staff | Create, read, update inventory items |
| Doctor | Read inventory items |
| Staff | Read inventory items |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| INV-REQ-001 | Identity | UUID PK per platform convention |
| INV-REQ-002 | Tenant | Must belong to one Organization |
| INV-REQ-003 | Branch | May reference a Branch (optional) |
| INV-REQ-004 | Category | May reference an Inventory Category (optional) |
| INV-REQ-005 | Code | Item code — unique within system |
| INV-REQ-006 | Name | Item name — required |
| INV-REQ-007 | Description | Free-text description (optional) |
| INV-REQ-008 | Unit | Unit of measurement — required |
| INV-REQ-009 | Quantity | Current quantity — default 0 |
| INV-REQ-010 | Min Quantity | Minimum quantity threshold — default 0, used for low-stock alerts |
| INV-REQ-011 | Unit Price | Unit price (optional) |
| INV-REQ-012 | Active | Active/inactive status — default true |
| INV-REQ-013 | List | Organization-scoped listing with filter by branch_id, category_id, is_active |
| INV-REQ-014 | List | Search by name, item_code, description |
| INV-REQ-015 | List | Sort by name, created_at, quantity |
| INV-REQ-016 | List | Pagination support |
| INV-REQ-017 | Create | Create inventory item with code, name, unit, category, branch |
| INV-REQ-018 | Update | Update item details, quantity, price, active status |
| INV-REQ-019 | Delete | Soft delete only |
| INV-REQ-020 | Toggle | Toggle active/inactive status |
| INV-REQ-021 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| INV-REQ-022 | Authorization | Read=authenticated, Write=Inventory Staff/Admin |
| INV-REQ-023 | Tenant | All queries scoped to organization_id |
| INV-REQ-024 | API | ApiResponse envelope |
| INV-REQ-025 | API | Versioned under /api/v1/inventory-items |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| INV-NF-001 | Tenant isolation — no cross-organization data access |
| INV-NF-002 | Authorization — Policy-based access control |
| INV-NF-003 | Audit — all mutations traceable via Audit Platform |
| INV-NF-004 | Performance — composite indexes on (organization_id, is_active) and (organization_id, branch_id) |
| INV-NF-005 | Security — organization-level data isolation |
| INV-NF-006 | Consistency — unit_price stored as decimal for precision |

### 6. Out of Scope

- Stock movement tracking (Phase 21 Procurement)
- Purchase order creation (Phase 21 Procurement)
- Supplier management (Phase 21 Procurement)
- Batch/lot tracking
- Expiry date management
- Barcode generation