# Phase 17 — Billing Requirements

**Date:** 2026-08-17 | **Phase:** 17 — Billing | **Status:** STEP_17_01_DRAFT

## Requirements (BILL-REQ-001 through BILL-REQ-025)

Billing domain manages invoice creation, payment tracking, status lifecycle, and financial record keeping. Each invoice is scoped to an organization, may be linked to a patient, and carries a structured status lifecycle from draft through payment.

### 1. Purpose

Provide a centralized billing record for every financial transaction. Each invoice is scoped to an organization, may reference a patient, supports line-item tracking via JSONB, and enforces a strict status lifecycle.

### 2. Scope

**In scope:**
- Invoice creation and management
- Invoice status lifecycle (draft → sent → paid; draft → cancelled; sent → overdue → paid/cancelled)
- Line item tracking via JSONB
- Payment amount tracking (total_amount, paid_amount)
- Patient ↔ Invoice relationship (optional)
- Organization tenancy
- Audit trail

**Out of scope:**
- Payment gateway integration
- Insurance claim processing
- Tax calculation
- Partial payment workflows (single paid_amount only)
- Recurring billing

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Finance Staff | Create, read, update invoices |
| Doctor | Read own patient invoices |
| Patient | Read own invoices |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| BILL-REQ-001 | Identity | UUID PK per platform convention |
| BILL-REQ-002 | Tenant | Must belong to one Organization |
| BILL-REQ-003 | Patient | May reference a Patient (optional) |
| BILL-REQ-004 | Number | Invoice number — unique, auto-generated INV-YYYYMMDD-XXXXX |
| BILL-REQ-005 | Amount | Total amount — required, non-negative |
| BILL-REQ-006 | Paid | Paid amount — default 0, non-negative, ≤ total_amount |
| BILL-REQ-007 | Status | Status lifecycle: draft → sent → paid; draft → cancelled; sent → overdue → paid/cancelled |
| BILL-REQ-008 | Due Date | Due date (optional) — used for overdue detection |
| BILL-REQ-009 | Items | Line items as JSONB (optional) — item_code, description, quantity, unit_price, subtotal |
| BILL-REQ-010 | Notes | Free-text notes (optional) |
| BILL-REQ-011 | List | Organization-scoped listing with filter by status, patient_id |
| BILL-REQ-012 | List | Search by invoice_number, notes |
| BILL-REQ-013 | List | Sort by created_at, due_date |
| BILL-REQ-014 | List | Pagination support |
| BILL-REQ-015 | Create | Create invoice with patient, total_amount, items, due_date, notes |
| BILL-REQ-016 | Update | Update invoice details, status, paid_amount, items, notes |
| BILL-REQ-017 | Delete | Soft delete only |
| BILL-REQ-018 | Lifecycle | Status transition validation |
| BILL-REQ-019 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| BILL-REQ-020 | Authorization | Read=authenticated, Write=Finance/Admin |
| BILL-REQ-021 | Tenant | All queries scoped to organization_id |
| BILL-REQ-022 | API | ApiResponse envelope |
| BILL-REQ-023 | API | Versioned under /api/v1/invoices |
| BILL-REQ-024 | DELETE | Soft delete only — no hard delete |
| BILL-REQ-025 | Patient FK | ON DELETE SET NULL — preserves invoice record |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| BILL-NF-001 | Tenant isolation — no cross-organization data access |
| BILL-NF-002 | Authorization — Policy-based access control |
| BILL-NF-003 | Audit — all mutations traceable via Audit Platform |
| BILL-NF-004 | Performance — composite indexes on (organization_id, status) and (organization_id, created_at) |
| BILL-NF-005 | Security — organization-level data isolation |
| BILL-NF-006 | Consistency — items stored as JSONB for flexible line-item schema |

### 6. Out of Scope

- Payment gateway integration
- Insurance claims processing
- Tax calculation (VAT/GST)
- Partial payment installment tracking
- Recurring/subscription billing
- Credit note management