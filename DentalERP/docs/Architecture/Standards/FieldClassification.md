# Field Classification Standard

## Purpose

Provide a canonical classification vocabulary for every persistent, transient, derived, and API field across Dental ERP Enterprise.

## Classifications

| Classification | Definition | Typical Examples |
|---|---|---|
| Core Identity | Required field that uniquely identifies or safely scopes a record. | `id`, `code`, `device_uuid` |
| Tenant Ownership | Organization/Branch/User ownership needed for isolation. | `organization_id`, `branch_id`, `user_id` |
| Business Data | Domain value used by business workflows. | patient name, invoice amount |
| Enrichment Metadata | Best-effort metadata that may be unavailable. | browser, OS, geolocation |
| Lifecycle Generated | Created or changed only by a lifecycle event. | `revoked_at`, `logout_at`, `last_activity_at` |
| Audit Metadata | Actor and timestamp evidence. | `created_by`, correlation ID |
| Sensitive | Restricted value whose disclosure creates privacy/security risk. | email, identifier, clinical notes |
| Secret | Value that must never be exposed or logged. | password, access token, refresh token |
| Derived | Computed from canonical source fields and not independently authoritative. | `is_active = revoked_at IS NULL` |

## Required Matrix

Every Decision Record that affects data fields must include:

| Field | Classification | Exposure | Nullability | Lifecycle | Ownership Exception | Derived Formula |
|---|---|---|---|---|---|---|

`Derived Formula` is mandatory for Derived fields and `Not Applicable` for non-derived fields.

## Rules

1. One field may have one primary classification and optional secondary sensitivity labels.
2. Classification must be consistent in Database Design, ERD, API, OpenAPI, Resource, and tests.
3. Core Identity and resolved Tenant Ownership fields are normally `NOT NULL`.
4. Enrichment Metadata is nullable unless a reliable source guarantee is documented.
5. Secret fields are never returned, logged, audited, or included in examples.
6. Derived fields must identify their formula and canonical source fields.
7. Missing classifications block Design Freeze.
