# DD-AUTH-010

## Title

Login History Index Strategy

## Status

Accepted — Architecture, Data, API, and Performance Reviews PASS; Final Quality Gate PASS

## Problem

Index apa yang digunakan untuk pola query Login History, termasuk arah urutan timestamp?

ERD saat ini menyatakan index seperti `(user_id, login_at DESC)`, sementara draft migration menggunakan index Laravel biasa tanpa explicit `DESC`.

## Current State

Pola query yang diperkirakan:

- Riwayat User sendiri, terbaru lebih dulu.
- Riwayat Organization/Branch, terbaru lebih dulu untuk audit/admin.
- Filter `login_status` dan date range.
- Pagination berdasarkan `login_at`.

Index desain saat ini:

- `(user_id, login_at DESC)`.
- `(organization_id, branch_id, login_at DESC)`.
- `(identifier, login_status, login_at DESC)`.

Draft migration belum mengimplementasikan arah `DESC` secara eksplisit.

## Options

### Option A — Explicit Descending Composite Indexes

Gunakan SQL PostgreSQL explicit `DESC` sesuai ERD.

### Option B — Direction-Agnostic B-tree Indexes

Gunakan index Laravel biasa dan mengandalkan backward index scan PostgreSQL.

Jika dipilih, ERD harus menghapus explicit `DESC` agar tidak drift.

### Option C — Cursor-Oriented Index

Gunakan `(user_id, login_at DESC, id DESC)` dan equivalent tenant index untuk stable cursor pagination.

### Option D — Partial Indexes

Tambahkan partial index berdasarkan status atau recency jika query profile menunjukkan kebutuhan.

Pertimbangan: jangan over-index sebelum query/load evidence tersedia.

## Decision

Select Option C — stable newest-first composite indexes with UUID tie-breakers:

- `(user_id, login_at DESC, id DESC)`.
- `(organization_id, branch_id, login_at DESC, id DESC)`.
- `(identifier, login_status, login_at DESC, id DESC)`.

The tie-breaker makes ordering deterministic when multiple records share the same `login_at`. The public API remains page-based; this strategy permits a future cursor contract but does not introduce one. Partial indexes are excluded until approved query/load evidence demonstrates a need.

## Consequences

### Positive

- Newest-first ordering is deterministic through `login_at DESC, id DESC`.
- User, tenant, and identifier/status query families have aligned index prefixes.
- ERD, Database Design, API ordering, future Repository queries, and performance tests share one authority.

### Costs and Constraints

- Composite indexes increase insert and storage cost and are limited to the three approved query families.
- Migration SQL must preserve explicit direction and column order after Design Freeze.
- Public page-based pagination remains unchanged; no cursor field or response schema is added.
- Additional or partial indexes require query/load evidence and a separately reviewed change.

## Affected Documents

- `docs/Authentication/ERD.md`
- `database_design/007_Authentication.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- Draft `create_login_histories` migration.
- Future Repository query and performance tests.

## Review Status

Architecture Review: PASS — structure, deterministic ordering, dependency, and scope are coherent.

Data Review: PASS — index columns map to documented Login History fields and preserve ownership/nullability.

API Review: PASS — newest-first ordering remains compatible with the page-based contract and introduces no request/response change.

Performance Review: PASS — indexes cover documented query families without speculative partial indexes; insert/storage cost and future query-plan validation are explicit.

Final Quality Gate: PASS (`DD_AUTH_010_FINAL_QUALITY_GATE_PASS`).

Final Review Status: Accepted.

## Traceability

- Requirements: `AUTH-REQ-010`, `AUTH-REQ-016`.
- Business Rule: `AUTH-BR-005`.
- Data: Login History composite indexes in Database Design and ERD.
- API/OpenAPI: deterministic newest-first ordering without contract-shape change.
- Migration, Repository query, and performance tests: `PLANNED` after Design Freeze.

## Superseded By

None.
