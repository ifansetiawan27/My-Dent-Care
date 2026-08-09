# DD-AUTH-006

## Title

Device List Pagination Strategy

## Status

Accepted

## Problem

Apakah `GET /api/v1/auth/devices` menggunakan pagination atau bounded list, dan berapa default page size, maximum page size, serta default sorting?

Requirement mengharuskan device list paginated atau bounded. Contract saat ini belum menetapkan keduanya.

## Current State

Saat decision issue dibuka:

- Endpoint mengembalikan array tanpa pagination metadata.
- Tidak ada maximum device count yang didokumentasikan.
- Tidak ada sorting default.
- User dapat memiliki history Device aktif dan revoked.

Resolved state setelah keputusan ini:

- Device list menggunakan page-based pagination.
- Default page size adalah 20.
- Maximum page size adalah 100.
- Default ordering adalah `last_activity_at DESC, id DESC`.
- Active dan revoked Devices termasuk dalam result set.
- Allowed sort fields adalah `last_activity_at`, `created_at`, dan `device_name`.
- Allowed filters adalah `platform`, `trusted`, `active`, dan `revoked`.

## Options

### Option A — Pagination Standar

Gunakan `page` dan `per_page`, response memiliki pagination metadata.

Parameter yang dievaluasi:

- Default page size.
- Maximum page size.
- Default sort dan stable tie-breaker.

### Option B — Bounded List

Kembalikan maksimum N Device terbaru tanpa pagination.

Keputusan yang dibutuhkan:

- Maximum records: TBD.
- Apakah revoked Device termasuk.
- Retention/cutoff policy.

### Option C — Active List + Paginated History

Endpoint utama mengembalikan bounded active Devices; endpoint/history terpisah untuk revoked/old Devices.

### Option D — Cursor Pagination

Gunakan cursor berdasarkan `last_activity_at` dan `id` untuk kestabilan pada dataset yang berubah.

## Decision

Select Option A — standard page-based pagination.

Rationale:

- Memberikan kontrak yang konsisten dengan endpoint list lain pada ERP.
- Membatasi response size untuk User dengan history Device panjang.
- Maximum 100 mencegah unbounded query dan oversized payload.
- Sort `last_activity_at DESC` menampilkan Device paling relevan terlebih dahulu.
- Tie-breaker `id DESC` memberikan ordering stabil saat timestamp sama.

Final contract:

- Default page size: 20.
- Maximum page size: 100.
- Default sort: `last_activity_at DESC`.
- Stable tie-breaker: `id DESC`.
- Allowed sort fields: `last_activity_at`, `created_at`, `device_name`.
- Allowed sort directions: `asc`, `desc`.
- Allowed filters: `platform`, `trusted`, `active`, `revoked`.
- Both active and revoked Devices are included unless a future approved filter explicitly narrows the result.
- The response uses the standard `meta.pagination` envelope.
- Unknown sort fields, directions, or filters must fail validation and must never be passed directly to Repository queries.

## Consequences

- API.md and OpenAPI must document `page` and `per_page`.
- Device list response must include `meta.pagination`.
- Repository must order by `last_activity_at DESC, id DESC`.
- Database Design index `(user_id, revoked_at, last_activity_at)` supports tenant/user filtering and activity order; query-plan validation remains required during implementation.
- Feature Tests must validate defaults, maximum page size, stable ordering, and ownership isolation.
- Requests with `per_page > 100` must fail validation instead of being silently accepted.
- Requests with a sort field outside the allowlist must fail validation.
- Requests with a filter outside the allowlist must fail validation.
- Repository query remains scoped to the authenticated User and active tenant context.
- Repository query maps `trusted` to `is_trusted`, `active` to `revoked_at IS NULL`, and `revoked` to `revoked_at IS NOT NULL`.
- Pagination metadata must follow the standard `ApiResponse.meta.pagination` schema.

## Affected Documents

- `docs/Authentication/Requirement.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/ERD.md`
- `database_design/007_Authentication.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- Future Repository, Resource, and Feature Tests.

## Review Status

Architecture Review: PASS.

Performance Review: PASS for contract design; implementation query plans must be validated against PostgreSQL during Repository testing.

Final Review Status: Accepted.

Implementation Status: Not started. Downstream documents and code must be synchronized through their own SDLC/Quality Gate steps.
