# Phase 21 — Procurement Requirements

**Date:** 2026-08-17 | **Phase:** 21 — Procurement | **Status:** STEP_21_01_DRAFT

## Requirements (PROC-REQ-001 through PROC-REQ-025)

Procurement domain manages purchase orders, supplier references, order tracking, and procurement lifecycle. Each procurement order is scoped to an organization, may be linked to a branch and supplier, and tracks order status through its lifecycle.

### 1. Purpose

Provide a centralized procurement management system for dental clinic supplies, equipment, and materials ordering. Each order is scoped to an organization, may be assigned to a branch, and tracks procurement status from pending through to received or cancelled.

### 2. Scope

**In scope:**
- Procurement order creation and management
- Order status lifecycle (pending, approved, ordered, received, cancelled)
- Supplier reference (optional)
- Branch assignment (optional)
- Order items tracking (JSONB)
- Order date and expected date tracking
- Total amount tracking
- Organization/branch tenancy
- Audit trail

**Out of scope:**
- Inventory stock level updates on order receipt (handled via integration)
- Purchase requisition workflow
- Supplier management (separate domain)
- Payment processing
- Invoice matching

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Procurement Staff | Create, read, update procurement orders |
| Doctor | Read procurement orders |
| Staff | Read procurement orders |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| PROC-REQ-001 | Identity | UUID PK per platform convention |
| PROC-REQ-002 | Tenant | Must belong to one Organization |
| PROC-REQ-003 | Branch | May reference a Branch (optional) |
| PROC-REQ-004 | Supplier | May reference a Supplier (optional) |
| PROC-REQ-005 | Order Number | Order number — unique within system |
| PROC-REQ-006 | Status | Status lifecycle: pending, approved, ordered, received, cancelled |
| PROC-REQ-007 | Order Date | Order date — required |
| PROC-REQ-008 | Expected Date | Expected delivery date (optional) |
| PROC-REQ-009 | Total Amount | Total order amount — default 0 |
| PROC-REQ-010 | Items | Order items stored as JSONB (optional) |
| PROC-REQ-011 | Notes | Free-text notes (optional) |
| PROC-REQ-012 | List | Organization-scoped listing with filter by status |
| PROC-REQ-013 | List | Search by order_number, notes |
| PROC-REQ-014 | List | Sort by order_date, created_at, status |
| PROC-REQ-015 | List | Pagination support |
| PROC-REQ-016 | Create | Create order with number, supplier, branch, items, dates |
| PROC-REQ-017 | Update | Update order details, status transitions, items |
| PROC-REQ-018 | Delete | Soft delete only |
| PROC-REQ-019 | Status Flow | Validate status transitions (pending→approved→ordered→received) |
| PROC-REQ-020 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| PROC-REQ-021 | Authorization | Read=authenticated, Write=Procurement Staff/Admin |
| PROC-REQ-022 | Tenant | All queries scoped to organization_id |
| PROC-REQ-023 | API | ApiResponse envelope |
| PROC-REQ-024 | API | Versioned under /api/v1/procurement-orders |
| PROC-REQ-025 | Cancel | Order can be cancelled from any non-terminal state |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| PROC-NF-001 | Tenant isolation — no cross-organization data access |
| PROC-NF-002 | Authorization — Policy-based access control |
| PROC-NF-003 | Audit — all mutations traceable via Audit Platform |
| PROC-NF-004 | Performance — composite indexes on (organization_id, status) and order_date |
| PROC-NF-005 | Security — organization-level data isolation |
| PROC-NF-006 | Consistency — total_amount stored as decimal for precision |

### 6. Out of Scope

- Inventory stock level updates on order receipt
- Purchase requisition workflow
- Supplier management (separate domain)
- Payment processing
- Invoice matching