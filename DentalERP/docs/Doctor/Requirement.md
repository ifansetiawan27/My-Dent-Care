# Phase 11 — Doctor Requirements

**Date:** 2026-08-10 | **Phase:** 11 — Doctor | **Status:** STEP_11_02_DRAFT

## Requirements (DOC-REQ-001 through DOC-REQ-025)

Doctor domain manages dental professional records — identity, clinical specialization, license, organization/branch assignment, and bridge to User accounts. Structurally mirrors Employee domain with clinical specialization additions.

| ID | Category | Requirement |
|---|---|---|
| DOC-REQ-001 | Identity | Doctor code as business identifier — UNIQUE |
| DOC-REQ-002 | Identity | UUID PK per platform convention |
| DOC-REQ-003 | User Bridge | Optional 1:1 link to User via code |
| DOC-REQ-004 | Uniqueness | Code globally unique across Doctor + User |
| DOC-REQ-005 | Organization | Must belong to one Organization |
| DOC-REQ-006 | Branch | May be assigned to Branch (optional) |
| DOC-REQ-007 | Master Data | Reference doctor_specialties table |
| DOC-REQ-008 | Clinical | License number — UNIQUE within org |
| DOC-REQ-009 | Clinical | Consultation fee (optional) |
| DOC-REQ-010 | Personal | Full name required |
| DOC-REQ-011 | Contact | Phone, email (optional) |
| DOC-REQ-012 | Lifecycle | Active/inactive toggle |
| DOC-REQ-013 | Lifecycle | Soft delete only |
| DOC-REQ-014 | Employment | Hire date |
| DOC-REQ-015 | Employment | Resignation date (optional) |
| DOC-REQ-016–020 | Master Data | Gender, religion, marital, nationality, geography references |
| DOC-REQ-021 | Audit | Platform audit trail |
| DOC-REQ-022 | Authorization | Read=authenticated, Write=Super Admin/Owner |
| DOC-REQ-023 | Tenant | Organization-scoped queries |
| DOC-REQ-024 | API | ApiResponse envelope |
| DOC-REQ-025 | Validation | FormRequest whitelist |

**Source:** AGENTS.md Phase 11, Phase 10 Employee pattern, Phase 09 Master Data (doctor_specialties), Phase 07 Platform Services.

**Governance Gaps (5):** DOC-GAP-001 (code generation), DOC-GAP-002 (specialty — enum vs table reference), DOC-GAP-003 (fee model), DOC-GAP-004 (user_id FK), DOC-GAP-005 (status transitions).

STEP_11_02_DOCTOR_REQUIREMENTS_DRAFT_PASS
