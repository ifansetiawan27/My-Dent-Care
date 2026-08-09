# DD-AUTH-008

## Title

Remember Me Behavior

## Status

Accepted

## Depends On

- None. Option A removes `remember_me`, so it has no effect on Refresh Token lifetime, Device trust, or post-password-change Session behavior and is independent from DD-AUTH-004.

## Problem

Apa pengaruh `remember_me` pada login Authentication?

Harus diputuskan apakah `remember_me`:

- Memperpanjang Access Token.
- Hanya memperpanjang Refresh Token.
- Membuat Device trusted.
- Tidak memiliki efek dan harus dihapus dari kontrak.

## Current State

Saat decision issue dibuka:

- Login Request menerima `remember_me` dengan default `false`.
- Access Token TTL ditetapkan 60 menit.
- Refresh Token TTL ditetapkan 30 hari.
- Device memiliki field `is_trusted`.
- Belum ada Business Rule yang mendefinisikan hubungan `remember_me` dengan TTL atau trusted Device.

Resolved state setelah keputusan ini:

- `remember_me` bukan bagian dari Authentication Login contract.
- Token TTL tetap seragam untuk seluruh login.
- Device trust tidak diturunkan dari input login.

## Options

### Option A — Hapus `remember_me`

Gunakan TTL tetap untuk semua login dan kelola trust Device melalui capability terpisah.

### Option B — Perpanjang Refresh Token Saja

Access Token tetap 60 menit. Refresh Token mendapatkan TTL berbeda saat `remember_me = true`.

### Option C — Tandai Device sebagai Trusted

`remember_me = true` mengubah `is_trusted`; TTL token tetap sama.

Pertimbangan: trust Device mungkin membutuhkan verifikasi tambahan, bukan hanya checkbox login.

### Option D — Perpanjang Refresh Token dan Trusted Device

Menggabungkan longer session dengan trusted-device state.

### Option E — Konfigurabel per Organization

Policy Organization menentukan efek dan TTL.

Pertimbangan: risiko security posture berbeda antar tenant dan kompleksitas support.

## Decision

Select Option A — remove `remember_me` from the Authentication Login contract.

Rationale:

- Fixed TTL menghasilkan security posture yang konsisten untuk seluruh Organization.
- Checkbox login tidak cukup untuk membuktikan bahwa Device dapat dipercaya.
- Device trust membutuhkan verification, authorization, audit, dan lifecycle contract tersendiri.
- Menghapus `remember_me` menghindari coupling dengan DD-AUTH-004 dan mencegah variasi Session lifetime yang tidak terdokumentasi.

Final rules:

- Login Request does not accept `remember_me`.
- Access Token TTL remains fixed at 60 minutes.
- Refresh Token TTL remains fixed at 30 days.
- Device trust is not granted implicitly during login.
- `user_devices.is_trusted` can only be changed through a separate trusted-device capability with its own verification and authorization rules.
- `remember_me` is not stored in `user_devices`, `user_sessions`, or token records.
- Session lifecycle remains governed by DD-AUTH-001 and password-change behavior remains governed independently by DD-AUTH-004.

## Consequences

- Remove `remember_me` from API.md Login request payload and field documentation.
- Remove `remember_me` from OpenAPI `LoginRequest` schema and examples.
- Add an explicit Business Rule that token TTL is fixed and Device trust is a separate capability.
- Client applications must not render a Remember Me option for this API contract.
- No database column or Session attribute is required for Remember Me.
- Login tests must reject unknown `remember_me` input because the request schema disallows additional properties.

## Affected Documents

- `docs/Authentication/Requirement.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/ERD.md`
- `docs/Authentication/Flow.md`
- `docs/Authentication/Architecture.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- Future LoginDTO, TokenService, DeviceService, and tests.

## Review Status

Architecture Review: PASS.

Security Review: PASS.

Final Review Status: Accepted.

Implementation Status: Not started. Downstream documents and code must be synchronized through their own SDLC/Quality Gate steps.
