# DD-AUTH-005

## Title

OpenAPI and ERD Nullability Strategy

## Status

Superseded

## Superseded By

`DD-AUTH-017`

## Problem

Bagaimana nullability field Authentication diselaraskan antara PostgreSQL ERD, OpenAPI response schemas, lifecycle events, dan data-source guarantees?

Database nullability dan OpenAPI nullability harus konsisten. Field tidak boleh diwajibkan memiliki nilai non-null pada API apabila database atau proses enrichment dapat menghasilkan `NULL`.

## Architecture Policy

Select Option D — separate required core identity fields, nullable enrichment metadata, and lifecycle-generated fields.

### Required Core Identity Fields

Core identity fields are `NOT NULL` because records cannot be identified or scoped safely without them.

Examples:

- Primary key UUID.
- User ownership.
- Organization and Branch ownership.
- Device UUID.
- Login status.
- Session ownership.
- Token ownership.

### Nullable Enrichment Metadata

Enrichment metadata is nullable because parsing, geolocation, or client information may be unavailable or fail.

Rules:

- Do not replace missing enrichment data with misleading values such as `Unknown` or empty strings.
- OpenAPI must permit `null`.
- API Resources must return the field consistently with a `null` value when unavailable.

### Lifecycle-Generated Fields

Lifecycle fields remain nullable until the corresponding system event occurs.

Examples:

- `logout_at` is `NULL` while the Session is active.
- `logout_at` is populated when the Session is logged out or revoked.
- `last_login_at` is `NULL` until the first successful login is recorded.
- `last_activity_at` is `NULL` until the first authenticated activity is tracked.
- `failure_reason` is `NULL` for successful authentication and populated only for failed authentication.

## Final Field Classification

### Login History

| Field | Classification | Database | OpenAPI | Rule |
|---|---|---|---|---|
| `ip_address` | Nullable enrichment metadata | Nullable | Nullable | May be unavailable because of proxy or network conditions |
| `browser` | Nullable enrichment metadata | Nullable | Nullable | Best-effort User-Agent parsing |
| `operating_system` | Nullable enrichment metadata | Nullable | Nullable | Best-effort User-Agent parsing |
| `device_name` | Nullable enrichment metadata | Nullable | Nullable | Client-provided metadata may be absent |
| `country` | Nullable enrichment metadata | Nullable | Nullable | Geolocation may be unavailable |
| `city` | Nullable enrichment metadata | Nullable | Nullable | Geolocation may be unavailable |
| `logout_at` | Lifecycle-generated | Nullable | Nullable | `NULL` until Session logout or revocation |
| `failure_reason` | Conditional lifecycle field | Nullable | Nullable | `NULL` on success; populated on failed authentication |

Required Login History fields remain:

- `id`
- `login_at`
- `login_status`

### Device

| Field | Classification | Database | OpenAPI | Rule |
|---|---|---|---|---|
| `device_name` | Nullable enrichment metadata | Nullable | Nullable | Client-provided name may be absent |
| `platform` | Nullable enrichment metadata | Nullable | Nullable | Platform detection may be unavailable |
| `browser` | Nullable enrichment metadata | Nullable | Nullable | Best-effort User-Agent parsing |
| `operating_system` | Nullable enrichment metadata | Nullable | Nullable | Best-effort User-Agent parsing |
| `last_login_at` | Lifecycle-generated | Nullable | Nullable | `NULL` until first successful Device login |
| `last_activity_at` | Lifecycle-generated | Nullable | Nullable | `NULL` until first tracked authenticated activity |

Required Device fields remain:

- `id`
- `device_uuid`
- `is_trusted`
- `is_active`

## OpenAPI Presence Policy

OpenAPI property presence and value nullability are separate concerns.

The API uses a stable response shape:

- Documented fields remain present in the response.
- Nullable fields use OpenAPI 3.1 nullable unions, for example:

```yaml
browser:
  type:
    - string
    - "null"
```

- A property may appear in the OpenAPI `required` list while its value permits `null`.
- `required` means the property must be present.
- Nullable type means the property value may be `null`.

This preserves a predictable API response without fabricating enrichment values.

## Decision

Adopt the following policy:

1. Core identity fields are required and `NOT NULL`.
2. Enrichment metadata is nullable.
3. Lifecycle-generated fields are nullable until their system event occurs.
4. OpenAPI nullability must match ERD/database nullability.
5. API Resources must preserve a stable response shape by returning nullable fields as `null`.
6. Missing enrichment metadata must not be converted into misleading placeholder values.
7. Validation and tests must verify null behavior explicitly.
8. Any nullability change requires synchronized review of ERD, Database Design, API.md, OpenAPI, Resources, examples, and tests.

## Consequences

- `login_histories` enrichment and conditional fields remain nullable.
- `user_devices` enrichment and lifecycle fields remain nullable.
- OpenAPI schemas must use nullable unions for every nullable database field.
- OpenAPI examples must include realistic `null` cases.
- API Resources must return nullable properties consistently.
- Models must cast nullable timestamps without generating artificial defaults.
- Repository queries must not assume enrichment fields are populated.
- Feature Tests must cover responses where browser, operating system, geolocation, Device name, and lifecycle timestamps are `null`.
- Database migrations must match the approved nullability exactly.
- Drift Detection must compare database nullability, ERD nullability, OpenAPI type unions, Resource output, and test expectations.

## Affected Documents

- `docs/Authentication/Requirement.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/ERD.md`
- `database_design/007_Authentication.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/Architecture.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- Future Authentication Migrations.
- Future Authentication Models.
- Future Authentication Resources.
- Future Feature and Unit Tests.

## Review Status

Architecture Review: PASS.

API Contract Review: PASS.

Database Design Review: PASS.

Final Review Status: Accepted.

Implementation Status: Not started. Downstream artifacts and implementation must be synchronized through their respective SDLC and Quality Gate steps.
