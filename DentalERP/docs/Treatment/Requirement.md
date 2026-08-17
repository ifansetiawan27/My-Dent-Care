# Phase 16 — Treatment Requirements

**Date:** 2026-08-17 | **Phase:** 16 — Treatment | **Status:** STEP_16_01_DRAFT

## Requirements (TREAT-REQ-001 through TREAT-REQ-025)

Treatment domain manages dental treatment procedures — treatment planning, execution tracking, status lifecycle, cost recording, and clinical procedure data. It bridges Patient, Doctor, and Appointment domains.

### 1. Purpose

Provide a centralized clinical treatment record for every dental procedure planned and/or performed on a patient. Each treatment is scoped to an organization, may be linked to a specific doctor and appointment, and carries a structured status lifecycle from planning through completion.

### 2. Scope

**In scope:**
- Treatment planning and creation
- Treatment status lifecycle (planned → in_progress → completed → cancelled)
- Treatment cost recording
- Clinical procedure data (teeth involved, procedure notes, materials)
- Patient ↔ Treatment relationship
- Doctor ↔ Treatment relationship
- Appointment ↔ Treatment relationship
- Organization/branch tenancy
- Audit trail

**Out of scope:**
- Billing/invoicing (Phase 17 manages billing)
- Treatment catalog/master data (Phase 09 Master Data manages treatment categories)
- Odontogram integration (Phase 15 links to odontogram separately)
- EMR integration (Phase 14 links to EMR separately)
- Insurance claims processing

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Doctor | Create, read, update own treatments |
| Staff | Read treatments |
| Patient | Read own treatments |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| TREAT-REQ-001 | Identity | UUID PK per platform convention |
| TREAT-REQ-002 | Tenant | Must belong to one Organization |
| TREAT-REQ-003 | Patient | Must reference a valid Patient |
| TREAT-REQ-004 | Doctor | May reference a Doctor (optional) |
| TREAT-REQ-005 | Appointment | May reference an Appointment (optional) |
| TREAT-REQ-006 | Type | Treatment type code — required, references treatment categories |
| TREAT-REQ-007 | Status | Status lifecycle: planned → in_progress → completed; planned → cancelled |
| TREAT-REQ-008 | Cost | Treatment cost (optional) |
| TREAT-REQ-009 | Description | Free-text description (optional) |
| TREAT-REQ-010 | Procedure | Procedure data as JSONB (optional) — teeth, surfaces, materials, notes |
| TREAT-REQ-011 | List | Organization-scoped listing with filter by patient, doctor, status, type |
| TREAT-REQ-012 | List | Search by description |
| TREAT-REQ-013 | List | Sort by created_at, status |
| TREAT-REQ-014 | List | Pagination support |
| TREAT-REQ-015 | Create | Create treatment with patient, type, doctor, appointment |
| TREAT-REQ-016 | Update | Update treatment details, status, cost, procedure data |
| TREAT-REQ-017 | Delete | Soft delete only |
| TREAT-REQ-018 | Lifecycle | Status transition validation |
| TREAT-REQ-019 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| TREAT-REQ-020 | Authorization | Read=authenticated, Write=Doctor/Admin |
| TREAT-REQ-021 | Tenant | All queries scoped to organization_id |
| TREAT-REQ-022 | API | ApiResponse envelope |
| TREAT-REQ-023 | API | Versioned under /api/v1/treatments |
| TREAT-REQ-024 | DELETE | Soft delete only — no hard delete |
| TREAT-REQ-025 | Doctor FK | ON DELETE SET NULL — preserves treatment record |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| TREAT-NF-001 | Tenant isolation — no cross-organization data access |
| TREAT-NF-002 | Authorization — Policy-based access control |
| TREAT-NF-003 | Audit — all mutations traceable via Audit Platform |
| TREAT-NF-004 | Performance — composite indexes on (organization_id, patient_id) and (organization_id, status) |
| TREAT-NF-005 | Security — organization-level data isolation |
| TREAT-NF-006 | Consistency — procedure_data stored as JSONB for flexible schema |

### 6. Out of Scope

- Billing integration (Phase 17)
- Treatment catalog management (Phase 09 Master Data)
- Odontogram direct linkage (Phase 15)
- EMR direct linkage (Phase 14)
- Insurance/claim processing
- Prescription management