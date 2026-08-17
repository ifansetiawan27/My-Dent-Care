# Phase 24 — CRM Requirements

**Date:** 2026-08-17 | **Phase:** 24 — CRM | **Status:** STEP_24_01_DRAFT

## Requirements (CRM-REQ-001 through CRM-REQ-025)

CRM domain manages patient communications, contact records, and follow-up tracking. Each contact is scoped to an organization, may reference a patient, and tracks contact type, channel, and resolution status.

### 1. Purpose

Provide a centralized CRM contact management system for patient communications, inquiries, complaints, and follow-ups. Each contact is scoped to an organization, may reference a patient, categorized by contact type and channel, and tracked through its resolution lifecycle.

### 2. Scope

**In scope:**
- CRM contact creation and management
- Contact type classification
- Channel tracking (phone, email, chat, etc.)
- Patient reference (optional)
- Status lifecycle (new, in_progress, resolved, closed)
- Follow-up date tracking
- Resolution notes
- Organization tenancy
- Audit trail

**Out of scope:**
- Email/SMS integration
- Ticket management system
- Automated follow-up scheduling
- Customer satisfaction surveys
- Marketing campaign management

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| CRM Staff | Create, read, update contacts |
| Doctor | Read contacts |
| Staff | Read contacts |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| CRM-REQ-001 | Identity | UUID PK per platform convention |
| CRM-REQ-002 | Tenant | Must belong to one Organization |
| CRM-REQ-003 | Patient | May reference a Patient (optional) |
| CRM-REQ-004 | Contact Type | Contact type — required |
| CRM-REQ-005 | Channel | Communication channel (optional) |
| CRM-REQ-006 | Subject | Contact subject (optional) |
| CRM-REQ-007 | Message | Contact message/content (optional) |
| CRM-REQ-008 | Status | Status lifecycle: new, in_progress, resolved, closed |
| CRM-REQ-009 | Follow-up | Follow-up date (optional) |
| CRM-REQ-010 | Resolution | Resolution notes (optional) |
| CRM-REQ-011 | List | Organization-scoped listing with filter by status, contact_type |
| CRM-REQ-012 | List | Search by subject, message |
| CRM-REQ-013 | List | Sort by follow_up_date, created_at, status |
| CRM-REQ-014 | List | Pagination support |
| CRM-REQ-015 | Create | Create contact with type, patient, channel, subject, message |
| CRM-REQ-016 | Update | Update contact details, status transitions, resolution |
| CRM-REQ-017 | Delete | Soft delete only |
| CRM-REQ-018 | Status Flow | Validate status transitions |
| CRM-REQ-019 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| CRM-REQ-020 | Authorization | Read=authenticated, Write=CRM Staff/Admin |
| CRM-REQ-021 | Tenant | All queries scoped to organization_id |
| CRM-REQ-022 | API | ApiResponse envelope |
| CRM-REQ-023 | API | Versioned under /api/v1/crm-contacts |
| CRM-REQ-024 | Filter | Filter by contact_type |
| CRM-REQ-025 | Closed | Closed is terminal state |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| CRM-NF-001 | Tenant isolation — no cross-organization data access |
| CRM-NF-002 | Authorization — Policy-based access control |
| CRM-NF-003 | Audit — all mutations traceable via Audit Platform |
| CRM-NF-004 | Performance — composite indexes on (organization_id, status), (organization_id, contact_type) and follow_up_date |
| CRM-NF-005 | Security — organization-level data isolation |

### 6. Out of Scope

- Email/SMS integration
- Ticket management system
- Automated follow-up scheduling
- Customer satisfaction surveys
- Marketing campaign management