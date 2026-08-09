# DD-AUTH-001

## Title

Access Token to Device Linkage

## Status

Accepted

## Problem

Bagaimana relasi antara Laravel Sanctum Personal Access Token dengan `user_devices` dan `refresh_tokens`?

Desain harus dapat menentukan secara aman dan deterministik:

- Access Token mana yang merupakan current session.
- Refresh Token mana yang berpasangan dengan current Access Token.
- Semua Access Token dan Refresh Token yang dimiliki sebuah Device.
- Token mana yang harus dicabut saat Logout, Logout All, atau Device Revocation.

## Current State

Desain Authentication saat ini menetapkan:

- Access Token dikelola oleh Laravel Sanctum melalui `personal_access_tokens`.
- Refresh Token disimpan sebagai hash pada tabel `refresh_tokens`.
- `refresh_tokens` memiliki relasi ke `user_devices` melalui `device_id`.
- `user_devices` memiliki relasi ke User, Organization, dan Branch.
- Logout harus mencabut current Access Token dan Refresh Token yang terkait dengan session yang sama (`AUTH-BR-008`).
- Logout All harus mencabut seluruh Access Token dan Refresh Token milik User (`AUTH-BR-009`).
- Device Revocation harus mencabut seluruh Access Token dan Refresh Token yang terkait dengan Device (`AUTH-BR-007`).

Gap saat ini:

- `personal_access_tokens` belum memiliki relasi eksplisit ke `user_devices`.
- `personal_access_tokens` belum memiliki relasi eksplisit ke `refresh_tokens` atau token family.
- User dapat memiliki lebih dari satu token pair pada Device yang sama.
- `user_id` saja tidak cukup untuk menentukan current session atau token milik Device tertentu.
- Mekanisme pemilihan Access Token saat Device Revocation belum dapat diimplementasikan secara deterministik.

## Options

### Option A — Token Berdiri Sendiri

Personal Access Token tidak memiliki relasi database langsung ke Device atau Refresh Token.

Kemungkinan identifikasi dilakukan melalui:

- Token name.
- Token abilities.
- Metadata serialized pada field yang tersedia.
- Query berbasis User dan convention aplikasi.

Hal yang perlu dievaluasi:

- Apakah convention tersebut dapat menjamin linkage yang unik.
- Kemudahan revocation per Device.
- Risiko ambiguity jika terdapat beberapa session pada Device yang sama.
- Compatibility dengan migration Sanctum default.

### Option B — Token Memiliki Relasi ke Device

Personal Access Token memiliki relasi eksplisit ke `user_devices`, dan dapat memiliki session/token-family identifier untuk menghubungkan Access Token dengan Refresh Token.

Kemungkinan perubahan:

- Tambahkan `device_id` pada `personal_access_tokens`.
- Tambahkan session identifier atau token-family identifier.
- Atau tambahkan `personal_access_token_id` pada `refresh_tokens`.

Hal yang perlu dievaluasi:

- Perubahan terhadap migration Sanctum.
- FK dan index untuk revocation cepat.
- Logout current session yang deterministik.
- Dampak pada compatibility upgrade Sanctum.

### Option C — Device Menjadi Root, Token Hanya Turunan

`user_devices` menjadi aggregate/session root. Access Token dan Refresh Token selalu dibuat dan dicabut melalui Device Session.

Kemungkinan perubahan:

- Session/device record menjadi pemilik token pair.
- Setiap login membuat atau memperbarui Device Session dan membuat session instance.
- Logout, Logout All, dan Device Revocation bekerja dari Device/Session ke token turunannya.

Hal yang perlu dievaluasi:

- Apakah satu Device dapat memiliki banyak session aktif.
- Perlu atau tidak tabel `user_sessions` terpisah dari `user_devices`.
- Lifecycle Device jangka panjang dibanding lifecycle Session jangka pendek.
- Kompleksitas tambahan dan kejelasan aggregate boundary.

## Decision

Select Option B with an explicit `user_sessions` aggregate.

Final relationship:

```text
user_devices
    └── user_sessions
            ├── personal_access_tokens
            └── refresh_tokens
```

Rules:

- `user_devices` represents a long-lived recognized physical/client device.
- `user_sessions` represents one authenticated login/session lifecycle on a Device.
- One Device may own multiple Sessions over time and may have more than one active Session when explicitly allowed.
- Every Sanctum Personal Access Token belongs to exactly one `user_session` through `session_id`.
- Every Refresh Token belongs to exactly one `user_session` through `session_id`.
- The currently authenticated Sanctum token resolves the current Session deterministically through `session_id`.
- Logout revokes the current Session and all Access/Refresh Tokens belonging to it.
- Device Revocation revokes all active Sessions for the Device and every descendant token.
- Logout All revokes all active Sessions for the User and every descendant token.
- Refresh Token rotation remains inside the same Session and token family.
- Plaintext tokens are never persisted.

Approved session structure:

```text
users
│
├── user_devices
│      id PK
│      user_id FK
│      organization_id FK
│      branch_id FK
│      device_uuid
│      device_name
│      platform
│      browser
│      operating_system
│      is_trusted
│      last_activity_at
│      revoked_at
│
└── user_sessions
       id PK
       user_id FK
       organization_id FK
       branch_id FK
       user_device_id FK
       login_history_id FK nullable
       started_at
       expires_at
       revoked_at nullable
       revoke_reason nullable

personal_access_tokens
    session_id FK

refresh_tokens
    session_id FK
```

`login_history_id` is nullable. It links a Session to its successful login record when available and uses `SET NULL` so immutable Login History retention is not coupled to Session cleanup.

Approved revocation behavior:

| Action | Device | Session | Access Token | Refresh Token |
|---|---|---|---|---|
| Logout | Remains active | Revoke current Session | Revoke current Session tokens | Revoke current Session tokens |
| Logout All | Remains active | Revoke all User Sessions | Revoke all User Access Tokens | Revoke all User Refresh Tokens |
| Revoke Device | Mark Device revoked/inactive | Revoke all Sessions under Device | Revoke all Device Session tokens | Revoke all Device Session tokens |

Approved foreign keys:

```text
user_devices.user_id              -> users.id
user_sessions.user_id             -> users.id
user_sessions.organization_id     -> organizations.id
user_sessions.branch_id           -> branches.id
user_sessions.user_device_id      -> user_devices.id
user_sessions.login_history_id    -> login_histories.id (nullable, SET NULL)
personal_access_tokens.session_id -> user_sessions.id
refresh_tokens.session_id         -> user_sessions.id
```

Approved indexes:

```text
user_sessions (user_device_id, revoked_at)
user_sessions (user_id, revoked_at)
user_sessions (organization_id, branch_id, user_id, revoked_at)
user_sessions (expires_at)

refresh_tokens (session_id)
refresh_tokens (token_hash) UNIQUE
refresh_tokens (expires_at)
refresh_tokens (revoked_at)

personal_access_tokens (session_id)
```

## Decision Criteria

Keputusan final harus memenuhi:

1. Logout current session dapat memilih tepat satu token pair.
2. Logout All dapat mencabut seluruh token User secara efisien.
3. Device Revocation dapat mencabut seluruh token Device secara deterministik.
4. Token rotation dan reuse detection tetap dapat ditelusuri.
5. Organization dan Branch scope tidak hilang.
6. Query revocation efisien untuk skala 10–100 Branch.
7. Tidak menyimpan plaintext token.
8. Upgrade compatibility Laravel Sanctum dipertimbangkan.
9. Index, FK, dan delete behavior dapat didefinisikan jelas.
10. Feature Test dapat membuktikan isolation dan revocation behavior.

## Impact

Keputusan ini berdampak pada:

- ERD.
- Database Design.
- OpenAPI.
- Authentication Service.
- Token Service.
- Authentication Repository.
- Login Flow.
- Refresh Flow.
- Logout Flow.
- Logout All Flow.
- Device Revocation Flow.
- Migration Sanctum / Authentication.
- Feature Test dan Unit Test.
- ADR-002 Authentication Token Strategy.

## Consequences

- Add a `user_sessions` table with explicit User, Organization, Branch, and Device foreign keys.
- Extend Sanctum `personal_access_tokens` with a required `session_id` UUID foreign key for Authentication-issued tokens.
- Replace `refresh_tokens.device_id` and duplicated tenant ownership with `session_id`; tenant scope resolves through the Session aggregate.
- Token revocation queries become deterministic and indexed by Session, Device, and User.
- Sanctum migration customization must be isolated and documented for upgrade compatibility.
- DD-AUTH-004 may proceed to approval because current and other Sessions can now be identified.
- DD-AUTH-008 remains dependent on the approved DD-AUTH-004 behavior.
- Draft migrations created before this decision are obsolete references and must be regenerated only after Design Freeze PASS.

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
- `docs/Authentication/ArchitectureChecklist.md`
- `docs/Authentication/Decision/DependencyGraph.md`
- `docs/ADR/ADR-002-Authentication-Token.md`

## Review Status

Architecture Review: PASS.

Security Review: PASS for linkage design. Authorization and retention behavior remain governed by their separate decision records.

Final Review Status: Accepted.

## Traceability

- Business Rules: `AUTH-BR-002`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`.
- Endpoints:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `POST /api/v1/auth/logout-all`
  - `POST /api/v1/auth/refresh`
  - `DELETE /api/v1/auth/devices/{deviceId}`
- Drift finding: `DD-AUTH-001` in `docs/Authentication/DriftDetectionReport.md`.

## Post-Acceptance Governance Note

- The consequence stating that DD-AUTH-008 remained dependent on DD-AUTH-004 records the pre-DD-AUTH-008 decision state.
- Accepted DD-AUTH-008 selected removal of `remember_me` and is independent from DD-AUTH-004.
- DD-AUTH-001 remains the Session/token linkage authority and is not otherwise changed by this note.
