# Phase 27 — Integration Hub Requirements

**Date:** 2026-08-17 | **Phase:** 27 — Integration Hub | **Status:** STEP_27_01_DRAFT

## Requirements (INT-REQ-001 through INT-REQ-025)

Integration Hub domain manages third-party integration configurations and credentials for external service providers (e.g., SATUSEHAT, BPJS, payment gateways, SMS gateways). Each integration config is scoped to an organization, stores provider-specific configuration and sensitive credentials, and tracks synchronization status.

### 1. Purpose

Provide a centralized credential and configuration management system for external service integrations. Credentials are sensitive and must be encrypted at rest and never exposed in API responses.

### 2. Scope

**In scope:**
- Integration configuration CRUD per organization
- Provider-specific configuration (JSONB)
- Sensitive credential storage (encrypted, write-only)
- Active/inactive toggle per integration
- Last sync timestamp tracking
- Provider uniqueness enforcement per organization
- Audit trail

**Out of scope:**
- Actual sync execution logic
- Provider-specific API clients
- OAuth token refresh flows
- Webhook event handling
- Integration health monitoring

### 3. Actors

| Actor | Role |
|---|---|
| Super Admin | Full access — create, read, update, delete |
| Organization Admin | Organization-scoped CRUD |
| Integration Manager | Create, read, update integrations |

### 4. Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| INT-REQ-001 | Identity | UUID PK per platform convention |
| INT-REQ-002 | Tenant | Must belong to one Organization |
| INT-REQ-003 | Provider | Provider name — required, max 50 chars |
| INT-REQ-004 | Name | Integration name — required, max 100 chars |
| INT-REQ-005 | Config | Provider-specific configuration as JSONB (nullable) |
| INT-REQ-006 | Credentials | Sensitive credentials as encrypted JSONB (nullable, write-only) |
| INT-REQ-007 | Active | is_active flag — defaults to false |
| INT-REQ-008 | Sync | last_sync_at timestamp — nullable |
| INT-REQ-009 | Provider Uniqueness | Provider name must be unique per organization |
| INT-REQ-010 | Credential Security | Credentials encrypted at rest via Laravel encrypted cast |
| INT-REQ-011 | Credential Exposure | Credentials field never returned in API responses |
| INT-REQ-012 | List | Organization-scoped listing with filter by provider, is_active |
| INT-REQ-013 | List | Search by name |
| INT-REQ-014 | List | Sort by name, provider, created_at |
| INT-REQ-015 | List | Pagination support |
| INT-REQ-016 | Create | Create integration config with provider, name, config, credentials |
| INT-REQ-017 | Update | Update integration config — all fields optional |
| INT-REQ-018 | Delete | Soft delete only |
| INT-REQ-019 | Toggle Active | Dedicated endpoint to toggle is_active boolean |
| INT-REQ-020 | Audit | Platform audit trail (created_by, updated_by, deleted_by) |
| INT-REQ-021 | Authorization | Admin-only access (Super Admin, Organization Admin) |
| INT-REQ-022 | Tenant | All queries scoped to organization_id |
| INT-REQ-023 | API | ApiResponse envelope |
| INT-REQ-024 | API | Versioned under /api/v1/integration-configs |
| INT-REQ-025 | Sync Tracking | last_sync_at updated when sync is triggered |

### 5. Non-Functional Requirements

| ID | Requirement |
|---|---|
| INT-NF-001 | Tenant isolation — no cross-organization data access |
| INT-NF-002 | Authorization — Policy-based access control, admin-only |
| INT-NF-003 | Audit — all mutations traceable via Audit Platform |
| INT-NF-004 | Security — credentials encrypted at rest (Laravel encrypted cast) |
| INT-NF-005 | Security — credentials never exposed in API responses |
| INT-NF-006 | Performance — composite index on (organization_id, provider) |
| INT-NF-007 | Data Integrity — provider uniqueness enforced per organization |

### 6. Out of Scope

- Actual sync execution logic
- Provider-specific API clients
- OAuth token refresh flows
- Webhook event handling
- Integration health monitoring
- Integration log/event history