# DD-AUTH-004

## Title

Change Password Session Behavior

## Status

Accepted

## Depends On

- `DD-AUTH-001` — Satisfied. The current Session and all other Sessions are identifiable through the approved `user_sessions` boundary.

## Problem

Apa yang terjadi terhadap session dan token setelah User berhasil mengganti password melalui `POST /api/v1/auth/change-password`?

Keputusan harus menetapkan apakah current Device tetap login, apakah semua Device lain dicabut, atau apakah perilaku dapat dikonfigurasi tanpa membuat security posture berbeda secara tidak terkendali.

## Current State

- Business Rules mengharuskan current password valid dan password baru berbeda.
- Reset Password secara eksplisit mencabut seluruh session.
- Change Password API tidak mendokumentasikan session revocation.
- Flow menyebut “revoke other sessions according to approved contract”, tetapi kontrak tersebut belum ada.

## Options

### Option A — Tetap Login di Semua Device

Password berubah tanpa mencabut token aktif.

Pertimbangan: pengalaman pengguna paling ringan, tetapi session yang mungkin telah dikompromikan tetap aktif.

### Option B — Pertahankan Current Session, Cabut Session Lain

Current Session tetap aktif. Semua Session lain dicabut. Device registrations tetap tersedia.

Pertimbangan: menjaga kelanjutan current session sambil mengurangi risiko session lama.

### Option C — Logout Semua Session

Semua access dan refresh token, termasuk current Device, dicabut. User harus login kembali.

Pertimbangan: posture paling ketat, tetapi menambah friksi.

### Option D — Konfigurabel

Perilaku ditentukan oleh security policy/configuration atau pilihan eksplisit request.

Pertimbangan: fleksibel, tetapi meningkatkan kompleksitas kontrak, testing, dan konsistensi antar deployment.

## Decision

Select Option B — preserve the current Session and revoke every other active Session.

After password is successfully changed:

- Current Session remains active.
- The current Session's active Sanctum Access Token remains active until normal expiry.
- The current Session's active Refresh Token family remains active, so the current Session can continue normally.
- Every other active Session owned by the User is immediately revoked.
- Every descendant Access Token and Refresh Token family of revoked Sessions is revoked through Session ownership.
- Registered Devices remain registered and are not revoked by Change Password.
- Emit an immutable `PASSWORD_CHANGED` audit event.
- Login History records are not modified.

Current Session continuity is preserved on the current Device. Other Devices remain registered but no longer have an active Session after revocation.

Reason:

Balance usability and security by preserving the verified current Session while invalidating every other active Session and descendant token pair.

## Consequences

- SessionService resolves the current Session deterministically through DD-AUTH-001.
- Current Session and its active Access/Refresh Token pair remain active.
- Every other active Session is revoked.
- Descendant Access Tokens and Refresh Token families of revoked Sessions are revoked.
- Registered Devices remain available for future login.
- The operation emits `PASSWORD_CHANGED` without password or hash values.
- Login History remains immutable and unchanged; the event belongs to Audit Platform records.
- `PASSWORD_CHANGED` audit payload includes `user_id`, `initiated_by`, timestamp, current `session_id`, and `revoked_session_count`; it excludes password and hash values.
- Feature Tests must prove current-session continuity, current refresh success, other-session revocation, Device preservation, audit emission, and no Login History mutation.

## Affected Documents

- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/SequenceDiagram.md`
- `docs/Authentication/Flowchart.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- ADR-002 Authentication Token Strategy.
- Future Authentication Service and Feature Tests.

## Review Status

Architecture Review: PASS.

Security Review: PASS.

Audit Review: PASS.

Final Review Status: Accepted.

Implementation Status: Not started. Downstream documents and code must be synchronized through their own SDLC/Quality Gate steps.
