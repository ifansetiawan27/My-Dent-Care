# DD-AUTH-003

## Title

Super Admin Session Scope

## Status

Accepted

## Problem

Apa batas kewenangan Super Admin terhadap session dan device Authentication?

Keputusan harus menjawab secara eksplisit:

1. Apakah `POST /api/v1/auth/logout-all` hanya mencabut seluruh session milik User yang sedang terautentikasi?
2. Apakah Super Admin boleh mencabut session atau Device milik User lain?
3. Jika boleh, apakah tindakan tersebut dapat dilakukan lintas Organization?
4. Bagaimana tenant scope, authorization, audit, dan API contract membedakan self-service dan administrative session management?

## Current State

Requirement saat ini menyatakan:

- Super Admin dapat mengelola active sessions.
- Organization Owner dapat mengelola own sessions.
- Branch Manager dapat mengelola own sessions.
- Logout All mencabut seluruh Access Token dan Refresh Token milik authenticated User.

Business Rules saat ini menyatakan:

- User dapat melihat device mereka sendiri.
- User dapat mencabut device tertentu yang mereka miliki.
- Super Admin dapat mencabut seluruh device untuk authorized target User.

API Contract saat ini hanya menyediakan self-service endpoint:

- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/logout-all`
- `GET /api/v1/auth/devices`
- `DELETE /api/v1/auth/devices/{deviceId}`

Tidak ada endpoint dengan target `userId` untuk administrative revocation.

Gap saat ini:

- Requirement/Business Rules mengindikasikan kemungkinan cross-user authority.
- OpenAPI dan Flow hanya mendefinisikan operasi milik authenticated User.
- Batas lintas Organization belum didefinisikan.
- Permission dan Policy untuk administrative session revocation belum didefinisikan.
- Traceability Matrix belum memiliki administrative session endpoint.

## Options

### Option A — Self-Service Only

Seluruh endpoint Authentication session/device hanya bekerja pada authenticated User.

Konsekuensi:

- `logout-all` hanya mencabut seluruh session milik authenticated User.
- Super Admin tidak mendapatkan endpoint khusus untuk User lain pada module Authentication.
- Requirement dan Business Rules yang menyebut Super Admin manage/revoke target-user sessions harus direvisi.
- Administrative revocation dapat ditunda ke modul security operations terpisah di masa depan.

Hal yang perlu dievaluasi:

- Scope lebih sederhana dan risiko privilege abuse lebih rendah.
- Respons insiden akun kompromi memerlukan prosedur alternatif.
- Apakah kebutuhan operasional enterprise sudah terpenuhi.

### Option B — Super Admin Can Revoke Target User Sessions Within an Organization

Super Admin dapat mencabut seluruh session atau Device User lain, tetapi hanya dalam Organization yang sedang menjadi authorization context.

Kemungkinan endpoint tambahan:

```text
POST   /api/v1/users/{userId}/sessions/revoke-all
GET    /api/v1/users/{userId}/devices
DELETE /api/v1/users/{userId}/devices/{deviceId}
```

Konsekuensi:

- Memerlukan permission khusus dan Policy target-user ownership/tenant scope.
- Organization Owner atau role lain mungkin memerlukan capability terpisah.
- Audit wajib menyimpan actor User dan target User.
- Cross-organization access tetap dilarang.

Hal yang perlu dievaluasi:

- Kebutuhan incident response per Organization.
- Pemisahan endpoint Authentication self-service dan User Administration.
- Risk of privilege escalation.

### Option C — Super Admin Can Revoke Sessions Across Organizations

Platform-level Super Admin dapat mencabut session User di Organization manapun.

Konsekuensi:

- Membutuhkan platform-level permission yang tidak dibatasi Spatie team context biasa.
- Setiap request harus memiliki explicit target Organization dan target User.
- Audit wajib merekam actor, target, source Organization, target Organization, alasan, dan correlation ID.
- Endpoint harus dilindungi oleh break-glass / elevated-security policy.

Hal yang perlu dievaluasi:

- Apakah Super Admin adalah platform operator atau role tenant.
- Compliance dan segregation-of-duties.
- Approval workflow, reason requirement, and alerting.
- Risiko cross-tenant data access.

### Option D — Separate Self-Service and Security Administration APIs

Authentication API tetap self-service. Session revocation untuk User lain disediakan melalui Security Administration capability terpisah.

Konsekuensi:

- Authentication endpoints tetap sederhana dan tenant-safe.
- Administrative operations memiliki Contract, Policy, Audit, dan approval flow sendiri.
- Super Admin cross-organization authority dapat dikembangkan tanpa mencampur self-service Authentication.

Hal yang perlu dievaluasi:

- Tambahan boundary/module.
- Kapan capability tersebut masuk roadmap.
- Hubungan dengan User Administration dan Platform Authorization.

## Decision

Select Option D with the cross-tenant authority defined by Option C for Platform Super Admin.

Final boundary:

- Existing `/api/v1/auth/*` endpoints remain self-service and operate only on the authenticated User's own Sessions and Devices.
- Administrative Session/Device operations belong to a separate Security Administration capability and API contract.
- Platform Super Admin may manage Sessions, Devices, Access Tokens, and Refresh Tokens across all Organizations.
- Organization Admin is limited to target Users, Sessions, and Devices inside the Organization represented by the active Spatie team context.
- Organization Admin cannot act across Organizations.
- Branch-scoped administrators cannot act outside their authorized Branch scope.

Platform Super Admin may:

- View active Sessions for an authorized target User.
- View registered Devices for an authorized target User.
- Revoke a specific Session.
- Revoke all Sessions for a target User.
- Revoke a Device, all Sessions under it, and all descendant tokens.
- Force password reset.
- Lock or unlock an account.

Platform Super Admin may not:

- Read password hashes.
- Read Refresh Token plaintext values or hashes.
- Read Access Token plaintext values or hashes.
- Bypass audit logging.
- Modify immutable Login History or Audit records.

Token authority is revocation-only. Administrative views expose Session and Device metadata but never token secrets.

### Role Authority Matrix

| Action | User | Organization Admin | Platform Super Admin |
|---|---:|---:|---:|
| View own Sessions | Allowed | Not granted by administrative role | Allowed |
| Revoke own Session | Allowed | Not granted by administrative role | Allowed |
| Revoke all Sessions for a target User | Not allowed | Allowed within active Organization tenant | Allowed across Organizations |
| Revoke Device | Own Device only | Allowed within active Organization tenant | Allowed across Organizations |
| Force Password Reset | Not allowed | Allowed within active Organization tenant | Allowed across Organizations |
| Lock or Unlock Account | Not allowed | Allowed within active Organization tenant | Allowed across Organizations |
| Read Password Hash | Not allowed | Not allowed | Not allowed |
| Read Refresh Token value or hash | Not allowed | Not allowed | Not allowed |
| Read Access Token value or hash | Not allowed | Not allowed | Not allowed |

Matrix interpretation:

- `User` permissions are self-service capabilities exposed by Authentication endpoints.
- `Organization Admin` capabilities are administrative and require the active Organization team context, explicit permission, Policy approval, target validation, reason, and immutable audit.
- `Platform Super Admin` capabilities are platform-wide but still require explicit target tenant context, step-up authentication for high-risk actions, reason, and immutable audit.
- Administrative role assignment does not implicitly grant self-service endpoints; a principal may separately possess User self-service capability through the authenticated identity.
- No role can read password hashes or token values/hashes.

Administrative Session actions require:

- Explicit permission and Policy approval.
- Target User and tenant scope validation.
- A non-empty administrative reason.
- Actor User, target User, Organization, Branch when applicable, Device/Session identifiers, correlation ID, IP address, and user agent in the immutable audit event.
- Step-up authentication for cross-Organization actions and other high-risk operations.

Required immutable audit events:

- `SESSION_REVOKED`
- `DEVICE_REVOKED`
- `FORCE_PASSWORD_RESET`
- `ACCOUNT_LOCKED`
- `ACCOUNT_UNLOCKED`

## Decision Criteria

Keputusan final harus menentukan:

1. Arti tepat “Manage active sessions” untuk setiap actor.
2. Scope `logout-all`: authenticated User saja atau target User.
3. Apakah Super Admin merupakan platform role atau tenant-scoped role.
4. Apakah cross-organization revocation diperbolehkan.
5. Permission names untuk list/revoke target-user sessions.
6. Policy rules untuk actor, target User, Organization, Branch, dan Device.
7. Audit fields untuk actor/target/reason/correlation ID.
8. Apakah administrative action membutuhkan reason atau step-up authentication.
9. Endpoint placement: Authentication self-service, User Administration, atau Security Administration.
10. Feature Test untuk horizontal privilege escalation dan cross-tenant isolation.

## Impact

Keputusan ini berdampak pada:

- Authentication Requirement actor capabilities.
- Authentication Business Rules.
- OpenAPI endpoint inventory.
- Authentication and/or Security Administration Architecture.
- Authorization Policy.
- Spatie permissions.
- AuthenticationContext and team scope.
- Device and Session Services.
- Logout All Flow.
- Device List and Revocation Flow.
- Traceability Matrix.
- Audit Platform payload.
- Feature Tests and security tests.

## Traceability

- Drift finding: `DD-AUTH-003` in `docs/Authentication/DriftDetectionReport.md`.
- Business Rules: `AUTH-BR-006`, `AUTH-BR-007`, `AUTH-BR-009`.
- Existing self-service endpoints:
  - `POST /api/v1/auth/logout-all`
  - `GET /api/v1/auth/devices`
  - `DELETE /api/v1/auth/devices/{deviceId}`
- Related ADRs:
  - ADR-002 Authentication Token Strategy.
- ADR-004 User Authentication Audit Strategy.

## Consequences

- Self-service Authentication endpoints remain unchanged and cannot target another User.
- A separate Security Administration Requirement, Business Rules, OpenAPI contract, Policy, Service, and tests are required before administrative operations are implemented.
- Platform Super Admin must be modeled as a platform-level role/capability that is not restricted by ordinary tenant team scope, while every action still validates an explicit target tenant.
- Organization Admin permissions are evaluated inside the active Organization team context and cannot cross tenant boundaries.
- Branch scope is enforced in addition to Organization scope where the administrative role is branch-restricted.
- Administrative responses expose Session and Device metadata only; token values and password hashes are never returned.
- Revoking a Session revokes its descendant Access Token and active Refresh Token family.
- Revoking all Sessions for a User leaves registered Devices intact unless a Device is explicitly revoked.
- Revoking a Device revokes every Session and descendant token under the Device.
- Force Password Reset revokes every active Session according to the approved password-reset policy.
- Account lock/unlock updates transient Redis state and emits immutable audit events; durable Login History remains unchanged.
- Security tests must cover horizontal privilege escalation, cross-tenant denial, branch isolation, missing reason, step-up authentication, audit completeness, and secret redaction.

## Affected Documents

- `docs/Authentication/Requirement.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/Architecture.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/Decision/index.md`
- `docs/Authentication/DriftDetectionReport.md`
- `docs/Authentication/ArchitectureChecklist.md`
- Role and Permission design.
- Future Security Administration Requirement, Business Rules, ERD (if needed), API, OpenAPI, Flow, and Architecture.
- Future Policy and Feature Tests.

## Review Status

Architecture Review: PASS.

Security Review: PASS.

Tenant Isolation Review: PASS.

Audit Review: PASS.

Final Review Status: Accepted.

Implementation Status: Not started. No administrative endpoint or code may be created before the separate Security Administration capability completes its own SDLC and Quality Gates.
