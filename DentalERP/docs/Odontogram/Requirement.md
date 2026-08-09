# Phase 15 — Odontogram Requirements
**Status:** ODONTO-REQ-001 through ODONTO-REQ-020

Odontogram domain manages dental tooth chart records — per-tooth condition tracking, surface mapping, findings documentation, and treatment planning reference. Each tooth record belongs to a patient within an organization.

| ID | Category | Requirement | Source |
|---|---|---|---|
| ODO-REQ-001 | Identity | UUID PK per platform convention | AGENTS.md |
| ODO-REQ-002 | Clinical | tooth_number (FDI notation 11-48 or primary 51-85) | Dental standard |
| ODO-REQ-003 | Clinical | tooth_type (permanent/deciduous) — Core Enum ToothType | app/Core/Enums/ToothType.php |
| ODO-REQ-004 | Clinical | surface tracking (buccal, lingual, mesial, distal, occlusal) | Dental standard |
| ODO-REQ-005 | Clinical | condition status per tooth (sound, caries, restored, missing, etc.) | Dental standard |
| ODO-REQ-006 | Clinical | findings as jsonb for flexible clinical data | Dental standard |
| ODO-REQ-007 | Clinical | notes for free-text clinical observations | Dental standard |
| ODO-REQ-008 | Organization | Must belong to one Organization | AGENTS.md |
| ODO-REQ-009 | Patient | Must reference a Patient record | Phase 12 |
| ODO-REQ-010 | Lifecycle | Soft delete only | AGENTS.md |
| ODO-REQ-011 | Lifecycle | All mutations audited via Platform Audit | Phase 07 |
| ODO-REQ-012 | Authorization | Read=authenticated, Write=Super Admin/Owner/Dentist | Phase 06/08 |
| ODO-REQ-013 | Tenant | Organization-scoped queries | AGENTS.md |
| ODO-REQ-014 | API | ApiResponse envelope, RESTful | AGENTS.md |
| ODO-REQ-015 | Validation | FormRequest whitelist | AGENTS.md |
| ODO-REQ-016 | Integration | Patient FK — RESTRICT on delete | ERD |
| ODO-REQ-017 | Uniqueness | Composite unique: patient_id + tooth_number | Dental standard |
| ODO-REQ-018 | Filtering | Filter by patient_id, tooth_type, condition | Flow |
| ODO-REQ-019 | Sorting | Sort by tooth_number ascending | Flow |
| ODO-REQ-020 | Bulk | Support bulk create/update for full tooth chart save | Dental standard |

**Governance Gaps:** 3 — ODO-GAP-001 (condition vocabulary — enum vs free-text), ODO-GAP-002 (tooth numbering standard — FDI vs Universal), ODO-GAP-003 (findings schema standardization)

STEP_15_02_ODONTOGRAM_REQUIREMENTS_DRAFT_PASS
