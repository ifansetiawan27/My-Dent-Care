# DD-AUTH-002

## Title

Password Hashing Strategy — Argon2id vs bcrypt

## Status

Accepted

## Problem

Algoritma password hashing apa yang menjadi standar final Dental ERP Enterprise, parameter cost apa yang digunakan, dan bagaimana strategi migrasi untuk password hash lama yang masih menggunakan algoritma berbeda?

Keputusan tidak cukup hanya memilih Argon2id atau bcrypt. Desain juga harus menentukan:

- Cara mendeteksi hash yang perlu di-rehash.
- Parameter cost yang aman dan sesuai kapasitas production.
- Strategi migrasi tanpa mengetahui plaintext password pengguna.
- Compatibility dengan Laravel 12 dan existing User artifacts.
- Dampak terhadap latency login dan horizontal scaling.

## Current State

Artefak Authentication saat ini menetapkan:

- Password baru harus disimpan menggunakan Argon2id.
- Requirement dan Business Rules menyebut Argon2id sebagai standar Authentication.
- Password tidak boleh masuk log atau audit payload.

Artefak User existing masih memiliki drift:

- Database Design User menyebut bcrypt.
- User Business Rules menyebut bcrypt.
- Migration User memiliki komentar bcrypt.
- Repository User memiliki dokumentasi yang mengasumsikan bcrypt hash.
- User Model memakai Laravel `hashed` cast, yang mengikuti konfigurasi hashing aplikasi dan tidak secara mandiri menjamin Argon2id.

Gap saat ini:

- Algoritma source of truth belum diselaraskan lintas platform.
- Parameter Argon2id/bcrypt belum ditetapkan.
- Belum ada strategi `needsRehash`.
- Belum ada strategi migrasi hash bcrypt existing.
- Belum ada performance baseline untuk target login kurang dari 500 ms.

## Options

### Option A — Tetap Menggunakan bcrypt

Semua password tetap menggunakan bcrypt melalui Laravel hashing configuration.

Hal yang perlu dievaluasi:

- Compatibility terbaik dengan artefak User existing.
- Cost factor yang aman untuk production.
- Apakah bcrypt masih memenuhi security baseline enterprise yang disetujui.
- Tidak ada migrasi algoritma, tetapi cost lama mungkin tetap perlu di-rehash.

### Option B — Argon2id untuk Semua Password Baru

Konfigurasi hashing default Laravel diubah ke Argon2id. Password baru dan password yang berubah menggunakan Argon2id.

Hash bcrypt lama tetap dapat diverifikasi oleh Laravel, lalu di-rehash ke Argon2id setelah login berhasil apabila `needsRehash()` mengembalikan true.

Hal yang perlu dievaluasi:

- Memory cost, time cost, dan thread count.
- Kapasitas server dan concurrency login.
- Rehash transparan dalam transaksi setelah credential verification.
- Rollout bertahap tanpa reset password massal.
- Monitoring login latency dan resource consumption.

### Option C — Argon2id dengan Forced Migration

Semua pengguna diwajibkan melakukan reset password atau password change untuk mengganti hash lama menjadi Argon2id dalam periode migrasi tertentu.

Hal yang perlu dievaluasi:

- Kecepatan penyelesaian migrasi.
- Dampak operasional dan user experience.
- Risiko account lockout atau support load.
- Kebutuhan komunikasi dan recovery flow.

### Option D — Hybrid Berdasarkan Policy

Argon2id menjadi default, tetapi beberapa deployment atau workload dapat menggunakan bcrypt melalui environment-backed policy.

Hal yang perlu dievaluasi:

- Fleksibilitas deployment.
- Risiko inkonsistensi security posture antar environment/organization.
- Kompleksitas support dan audit.
- Apakah per-deployment variability dapat diterima untuk platform enterprise.

## Production Parameters

Argon2id production baseline:

| Parameter | Description | Decision |
|---|---|---|
| Algorithm | Password hashing algorithm | Argon2id |
| Memory cost | Memory used per hash operation | 65,536 KiB (64 MiB) |
| Time cost | Iteration/passes | 4 |
| Threads | Parallelism | 1 |
| Target verification latency | Production-representative p95 verification latency | At or below 250 ms |
| Login latency budget | End-to-end login NFR | Less than 500 ms under normal production load |

Configuration requirements:

- Parameters are environment-backed through Laravel hashing configuration.
- Production deployment must benchmark the baseline on representative container/hardware before rollout.
- Increasing cost requires benchmark evidence and a controlled rollout.
- Lower environments may use lower cost only for automated tests; production defaults remain the approved baseline.

Legacy bcrypt hashes remain verifiable during the migration period; bcrypt is not used for new hashes after rollout.

## Rehash Strategy

After a password is successfully verified:

1. Always call Laravel `Hash::needsRehash()` against the stored hash.
2. When true, synchronously create a new Argon2id hash from the already verified plaintext password.
3. Persist the replacement hash atomically before completing login.
4. If persistence fails, fail the authentication transaction and do not issue tokens; the legacy hash remains unchanged.
5. A change to approved Argon2id cost parameters also triggers `needsRehash()`.
6. Algorithm/cost-only rehash does not revoke existing Sessions because the credential value did not change.
7. Emit an immutable `PASSWORD_REHASH` audit event without password, old hash, or new hash values.
8. Logging may record algorithm identifiers and operation outcome only; it must never record password material.

## Legacy bcrypt Migration Strategy

Karena plaintext password tidak tersedia, hash lama tidak dapat dikonversi langsung.

Selected migration strategy:

- Existing bcrypt hashes remain valid for verification during a configurable grace period.
- Successful authentication performs opportunistic rehash to Argon2id.
- No bulk conversion is attempted because plaintext passwords are unavailable.
- Default grace period is 180 days from production rollout and is environment-configurable.
- After the grace period, accounts still using legacy bcrypt must complete Forgot/Reset Password before receiving a Session.
- Dormant accounts retain their legacy hash until successful login during the grace period or password reset after the grace period.
- Migration telemetry records aggregate counts by algorithm only; it never records password hashes.

Legacy-hash recognition:

- Use Laravel Hash verification for compatibility and `Hash::needsRehash()` for migration decisions.
- Do not implement application-side parsing as the primary verification mechanism.
- Unknown or unsupported hash formats fail authentication safely and produce a redacted security log/audit outcome.

## Decision

Select Option B — Argon2id for all new and changed passwords, with legacy bcrypt verification and opportunistic rehash after successful authentication.

Final policy:

- Argon2id is the production password hashing algorithm.
- Approved baseline is 64 MiB memory, time cost 4, and one thread.
- Target p95 password verification latency is at or below 250 ms on production-representative infrastructure.
- Laravel `Hash::needsRehash()` is mandatory after every successful password verification.
- Existing bcrypt hashes migrate opportunistically during the 180-day configurable grace period.
- Dormant/unmigrated accounts require password reset after the grace period.
- `PASSWORD_REHASH` is audited without password/hash values.
- Rollback may reduce Argon2id cost parameters only; the algorithm remains Argon2id.
- Existing Argon2id hashes are never downgraded to bcrypt.

## Decision Criteria

Keputusan final harus memenuhi:

1. Security baseline enterprise yang disetujui.
2. Login p95 tetap berada dalam NFR kurang dari 500 ms pada production load.
3. Password verification aman terhadap timing attacks melalui Laravel Hash contract.
4. Legacy bcrypt hash tetap dapat diverifikasi selama masa migrasi bila Argon2id dipilih.
5. Rehash tidak memerlukan plaintext password disimpan atau dicatat.
6. Parameter dapat dikonfigurasi melalui environment-backed Laravel config.
7. Horizontal scaling dan peak login concurrency diperhitungkan.
8. Strategy dapat diuji dengan unit, integration, performance, dan migration tests.
9. Audit tidak pernah menyimpan password atau password hash.
10. Existing User artifacts dapat diselaraskan tanpa data loss.

## Impact

Keputusan ini berdampak pada:

- Authentication Requirement.
- Authentication Business Rules.
- User Business Rules.
- User Database Design.
- User Model password cast/configuration.
- Authentication Service.
- Password Service.
- Login Flow.
- Change Password Flow.
- Reset Password Flow.
- Hashing configuration.
- Performance testing.
- Migration and rollout documentation.
- Audit and Logging redaction rules.

## Traceability

- Drift finding: `DD-AUTH-002` in `docs/Authentication/DriftDetectionReport.md`.
- Business Rules: `AUTH-BR-001`, `AUTH-BR-011`, `AUTH-BR-012`.
- Endpoints:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/reset-password`
  - `POST /api/v1/auth/change-password`
- Related ADRs:
  - ADR-003 Password Reset Strategy.
- ADR-004 User Authentication Audit Strategy.

## Consequences

- Laravel hashing configuration must use Argon2id and the approved environment-backed cost baseline.
- User and Authentication documentation that still says bcrypt must be migrated to Argon2id terminology.
- Login Service must verify legacy bcrypt and Argon2id hashes through Laravel Hash, then run `Hash::needsRehash()`.
- Rehash requires a database write in the successful-login transaction before token issuance.
- A rehash persistence failure blocks that login attempt without invalidating the legacy hash.
- Existing Sessions remain active after an algorithm/cost-only rehash.
- Password change/reset continues to apply its independently approved Session-revocation policy.
- Production capacity planning must account for 64 MiB per concurrent hashing operation and monitor p95 latency.
- Aggregate migration telemetry and a 180-day grace-period deadline must be operationally monitored.
- After the grace period, unmigrated/dormant accounts must reset their password before receiving a Session.
- `PASSWORD_REHASH` audit events contain actor/user, timestamp, source algorithm identifier, target algorithm identifier, and outcome only; no hash values.
- Emergency rollback reduces Argon2id memory/time cost through configuration after benchmark/security approval; it never changes the algorithm to bcrypt and never downgrades stored Argon2id hashes.

## Affected Documents

- `docs/Authentication/Requirement.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/Flow.md`
- `docs/Authentication/Architecture.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- `database_design/003_User.md`
- `docs/UserBusinessRule.md`
- User Model and hashing configuration during implementation.
- Authentication and User tests during Stages 17–18.

## Review Status

Architecture Review: PASS.

Security Review: PASS.

Performance Review: PASS for the production baseline contract. Deployment remains gated by a production-representative benchmark confirming p95 verification latency at or below 250 ms and end-to-end login below 500 ms.

Final Review Status: Accepted.

Implementation Status: Not started. Downstream documents, configuration, code, and tests must be synchronized through their own SDLC/Quality Gate steps.
