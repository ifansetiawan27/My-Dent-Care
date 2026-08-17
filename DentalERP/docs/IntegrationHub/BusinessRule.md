# Phase 27 — Integration Hub Business Rules

**Date:** 2026-08-17 | **Phase:** 27 — Integration Hub | **Status:** STEP_27_02_DRAFT

## Business Rules

### Rule Inventory

| ID | Rule | Source |
|---|---|---|
| BR-INT-001 | Integration must belong to an Organization | INT-REQ-002 |
| BR-INT-002 | Provider name is required and max 50 characters | INT-REQ-003 |
| BR-INT-003 | Integration name is required and max 100 characters | INT-REQ-004 |
| BR-INT-004 | Provider name must be unique per organization | INT-REQ-009 |
| BR-INT-005 | Credentials are encrypted at rest via Laravel encrypted cast | INT-REQ-010 |
| BR-INT-006 | Credentials field is write-only — never exposed in API responses | INT-REQ-011 |
| BR-INT-007 | is_active defaults to false on create | INT-REQ-007 |
| BR-INT-008 | Toggle active is a dedicated endpoint — only flips is_active boolean | INT-REQ-019 |
| BR-INT-009 | List queries are organization-scoped | INT-REQ-022 |
| BR-INT-010 | Organization-scoped listing — user cannot see integrations from other orgs | INT-REQ-022 |
| BR-INT-011 | Soft delete only — no hard delete | INT-REQ-018 |
| BR-INT-012 | Audit trail auto-populated via HasAudit trait | INT-REQ-020 |
| BR-INT-013 | Authorization: Admin-only access (Super Admin, Organization Admin) | INT-REQ-021 |
| BR-INT-014 | API response uses ApiResponse envelope | INT-REQ-023 |
| BR-INT-015 | Routes versioned under /api/v1/integration-configs | INT-REQ-024 |
| BR-INT-016 | Provider duplication check on create (per organization) | INT-REQ-009 |
| BR-INT-017 | Provider duplication check on update (excluding self, per organization) | INT-REQ-009 |
| BR-INT-018 | Config and credentials are optional — nullable JSONB | INT-REQ-005, INT-REQ-006 |
| BR-INT-019 | last_sync_at is nullable and updated on sync trigger | INT-REQ-008, INT-REQ-025 |
| BR-INT-020 | Organization FK uses RESTRICT — cannot delete org with active integrations | INT-REQ-002 |

### Duplicate Provider Rules

| Operation | Behavior |
|---|---|
| Create with existing provider in same org | BusinessException: "Provider already exists for this organization." |
| Update with existing provider in same org (different config) | BusinessException: "Provider already exists for this organization." |
| Update with same provider (same config) | Allowed — no change |
| Create with same provider in different org | Allowed — uniqueness is per organization |

### Credential Security Rules

| Rule | Enforcement |
|---|---|
| Encrypted at rest | Laravel `encrypted` cast on Model |
| Write-only API | IntegrationHubResource excludes `credentials` field |
| Never in list responses | Resource excludes credentials |
| Never in show responses | Resource excludes credentials |
| Stored as encrypted JSONB | Database column is jsonb, but encrypted before storage |

### Immutable Fields

| Field | Immutable After Create | Reason |
|---|---|---|
| id | Yes | UUID PK |
| organization_id | Yes | Tenant ownership |
| created_at | Yes | Audit timestamp |
| created_by | Yes | Audit field |

### Deletion Rules

- **Soft delete only** — `deleted_at` populated, record remains in database
- No cascade delete to related records
- Soft-deleted integrations are excluded from list queries by default

### Authorization Matrix

| Action | Super Admin | Org Admin | Integration Manager |
|---|---|---|---|
| List | All | Org-scoped | Org-scoped |
| Show | All | Org-scoped | Org-scoped |
| Create | All | Org-scoped | Org-scoped |
| Update | All | Org-scoped | Org-scoped |
| Delete | All | Org-scoped | Org-scoped |
| Toggle Active | All | Org-scoped | Org-scoped |