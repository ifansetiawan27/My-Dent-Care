# Phase 20 — Laboratory Requirements

**Date:** 2026-08-17 | **Phase:** 20 — Laboratory | **Status:** STEP_20_01_DRAFT

## Requirements (LAB-REQ-001 through LAB-REQ-025)

Laboratory domain manages lab orders for dental procedures — ordering, tracking, status lifecycle, results recording, and clinical notes. It bridges Patient, Doctor, and Laboratory Category domains.

### 1. Purpose

Provide a centralized laboratory order record for every diagnostic test ordered and/or performed for a patient. Each lab order is scoped to an organization, may be linked to a specific doctor and lab category, and carries a structured status lifecycle from pending through completion.

### 2. Scope

**In scope:**
- Lab order creation and management
- Lab order status lifecycle (pending → in_progress → completed → cancelled)
- Lab results recording (JSONB)
- Patient ↔ Lab Order relationship
- Doctor ↔ Lab Order relationship
- Laboratory Category ↔ Lab Order relationship
- Organization tenancy
- Audit trail

**Out of scope:**
- Billing/invoicing (Phase 17 manages billing)
- External lab integration (Phase 27 Integration Hub)
- Lab equipment management
- Lab inventory/stock management
- Insurance claims processing

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Doctor | Create, read, update own lab orders |
| Lab Technician | Read, update lab orders (status, results) |
| Staff | Read lab orders |
| Patient | Read own lab orders |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| LAB-REQ-001 | Identity | UUID PK per platform convention |
| LAB-REQ-002 | Tenant | Must belong to one Organization |
| LAB-REQ-003 | Patient | Must reference a valid Patient |
| LAB-REQ-004 | Doctor | May reference a Doctor (optional) |
| LAB-REQ-005 | Order Number | Unique order number per organization |
| LAB-REQ-006 | Category | May reference a Laboratory Category (optional) |
| LAB-REQ-007 | Status | Status lifecycle: pending → in_progress → completed; pending → cancelled |
| LAB-REQ-008 | Description | Free-text description (optional) |
| LAB-REQ-009 | Results | Results as JSONB (optional) — test values, findings, attachments |
| LAB-REQ-010 | Ordered At | Date when the order was placed |
| LAB-REQ-011 | Completed At | Date when the order was completed (optional) |
| LAB-REQ-012 | Notes | Free-text notes (optional) |
| LAB-REQ-013 | List | Organization-scoped listing with filter by patient, doctor, category, status |
| LAB-REQ-014 | List | Search by description, order_number, notes |
| LAB-REQ-015 | List | Sort by created_at, status, ordered_at |
| LAB-REQ-016 | List | Pagination support |
| LAB-REQ-017 | Create | Create lab order with patient, order_number, doctor, category |
| LAB-REQ-018 | Update | Update lab order details, status, results, notes |
| LAB-REQ-019 | Delete | Soft delete only |
| LAB-REQ-020 | Lifecycle | Status transition validation |
| LAB-REQ-021 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| LAB-REQ-022 | Authorization | Read=authenticated, Write=Doctor/LabTech/Admin |
| LAB-REQ-023 | Tenant | All queries scoped to organization_id |
| LAB-REQ-024 | API | ApiResponse envelope |
| LAB-REQ-025 | API | Versioned under /api/v1/lab-orders |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| LAB-NF-001 | Tenant isolation — no cross-organization data access |
| LAB-NF-002 | Authorization — Policy-based access control |
| LAB-NF-003 | Audit — all mutations traceable via Audit Platform |
| LAB-NF-004 | Performance — composite indexes on (organization_id, status) and patient_id |
| LAB-NF-005 | Security — organization-level data isolation |
| LAB-NF-006 | Consistency — results stored as JSONB for flexible schema |

### 6. Out of Scope

- Billing integration (Phase 17)
- External lab integration (Phase 27)
- Lab equipment management
- Lab inventory management
- Insurance/claim processing
- Result auto-import from external systems