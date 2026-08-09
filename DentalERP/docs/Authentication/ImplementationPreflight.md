# Authentication Implementation Preflight Report

**Date:** 2026-08-09
**Status:** `STEP_06_03_IMPLEMENTATION_PREFLIGHT_PASS`

## Prerequisites Verified

| Prerequisite | Status |
|---|---|
| DESIGN_FREEZE_DECLARATION_PASS | ✅ |
| MIGRATION_DRAFT_RECONCILIATION_PASS | ✅ |
| MIGRATION_QUALITY_GATE_PASS | ✅ |
| IMPLEMENTATION_READY | ✅ |

---

## 1. Frozen Design Verification

All implementation must use the following frozen artifacts as authoritative sources:

| Artifact | Path | Status |
|---|---|---|
| Requirement | `docs/Authentication/Requirement.md` | Frozen |
| Business Rules | `docs/Authentication/BusinessRule.md` | Frozen |
| API Contract | `docs/Authentication/API.md` | Frozen |
| OpenAPI | `docs/api/openapi.yaml` | Frozen |
| ERD | `docs/Authentication/ERD.md` | Frozen |
| Database Design | `database_design/007_Authentication.md` | Frozen |
| Accepted Decisions | `docs/Authentication/Decision/` | Frozen |
| Accepted ADRs | `docs/ADR/` | Frozen |

No implementation may silently diverge from these sources.

---

## 2. Implementation Scope

Per the frozen design, Authentication implementation covers 12 API operations plus architectural concerns:

| # | Area | Endpoint |
|---|---|---|
| 1 | Login and Session creation | `POST /auth/login` |
| 2 | Logout current Session | `POST /auth/logout` |
| 3 | Logout all User Sessions | `POST /auth/logout-all` |
| 4 | Refresh rotation and reuse detection | `POST /auth/refresh` |
| 5 | Forgot Password | `POST /auth/forgot-password` |
| 6 | Reset Password and Session revocation | `POST /auth/reset-password` |
| 7 | Change Password | `POST /auth/change-password` |
| 8 | Get Profile | `GET /auth/profile` |
| 9 | Update Profile | `PUT /auth/profile` |
| 10 | Login History | `GET /auth/login-history` |
| 11 | Device List | `GET /auth/devices` |
| 12 | Device Revocation | `DELETE /auth/devices/{deviceId}` |

Additional architectural concerns:
- Distributed lockout (Redis, 5 attempts, 15 min TTL, Super Admin exempt)
- Audit event emission (Append Only, Immutable)
- Login History projection (Operational History Projection, `logout_at` Controlled One-Time Mutation)
- Token lifecycle (rotation, reuse detection, family revocation)
- Session lifecycle (creation, expiration, revocation)
- Device lifecycle (registration, activity tracking, revocation)
- Asynchronous cleanup (retention, Legal Hold, archive, purge)
- Multi-tenant boundaries (org_id, branch_id scoping)

---

## 3. Existing Codebase Inspection

### 3.1 What EXISTS

| Component | Location | Assessment |
|---|---|---|
| User model | `app/Domains/User/Models/User.php` | HasApiTokens, HasRoles, MustVerifyEmail |
| UserService | `app/Domains/User/Services/UserService.php` | changePassword, resetPassword (domain-level) |
| UserRepository | `app/Domains/User/Repositories/UserRepository.php` | findByUsername, findByEmail, updateLastLogin |
| User enums | `app/Domains/User/Enums/` | UserStatus (canLogin), UserGender |
| Spatie config | `config/permission.php` | Sanctum guard, UUID, teams enabled |
| Role/Permission seeders | `app/Domains/RolePermission/Seeders/` | 15 roles, 77 permissions |
| BaseService | `app/Core/Base/BaseService.php` | Transaction, logging pattern |
| BaseRepository | `app/Core/Base/BaseRepository.php` | Whitelisted search/filter/sort |
| BaseController | `app/Core/Base/BaseController.php` | Empty stub |
| BaseRequest | `app/Core/Base/BaseRequest.php` | authorize() checks auth |
| BaseResource | `app/Core/Base/BaseResource.php` | auditFields helper |
| ApiResponse | `app/Core/Support/ApiResponse.php` | Standard envelope |
| HasAudit trait | `app/Core/Traits/HasAudit.php` | Auto-fill created_by, updated_by, deleted_by |
| HasUuid trait | `app/Core/Traits/HasUuid.php` | orderedUuid on create |
| Audit Platform contract | `app/Platform/Audit/` | AuditServiceInterface (no impl) |
| Notification Platform contract | `app/Platform/Notification/` | NotificationServiceInterface (no impl) |
| FileStorage Platform contract | `app/Platform/FileStorage/` | FileStorageServiceInterface (no impl) |
| Branch domain (reference) | `app/Domains/Branch/` | Full vertical slice — reference implementation |
| Users migration | `app/Domains/Authentication/Migrations/` | users table created |
| Organization model | `app/Domains/Organization/Models/Organization.php` | Basic model |
| Branch model | `app/Domains/Branch/Models/Branch.php` | Full model |
| Core exceptions | `app/Core/Exceptions/` | ApiException, BusinessException, NotFoundException |
| Core enums | `app/Core/Enums/` | 15 shared enums |

### 3.2 What is MISSING (Authentication-specific)

| Component | Priority |
|---|---|
| `config/auth.php` (sanctum guard definition) | Critical |
| `config/sanctum.php` | Critical |
| `personal_access_tokens` migration (Sanctum) | Critical |
| AuthController (all 12 endpoints) | Critical |
| AuthService / AuthServiceInterface | Critical |
| SessionService / SessionServiceInterface | Critical |
| TokenService / TokenServiceInterface | Critical |
| DeviceService / DeviceServiceInterface | Critical |
| LoginHistoryService / LoginHistoryServiceInterface | Critical |
| Auth repository interfaces + implementations | Critical |
| All request/DTO classes for auth endpoints | Critical |
| All resource classes for auth responses | Critical |
| Auth routes (`app/Domains/Authentication/Routes/api.php`) | Critical |
| Authentication domain models (Session, Device, RefreshToken, LoginHistory) | Critical |
| LockoutService (Redis integration) | High |
| AuditPlatform concrete implementation | High |
| NotificationPlatform concrete implementation | High |
| FileStoragePlatform concrete implementation | High |
| Migration files (6 drafts need conversion to executable) | High |
| All authentication tests | Critical |

### 3.3 PARTIALLY IMPLEMENTED

| Component | Current State | Gap |
|---|---|---|
| UserService.changePassword | Domain-level password change | Missing: session preservation, other-session revocation, DD-AUTH-018 exception, audit event |
| UserService.resetPassword | Domain-level password reset | Missing: all-session revocation, token consumption via Password Broker, audit |
| UserRepository.findByUsername | Exists | Needs integration into AuthService login flow |
| UserRepository.updateLastLogin | Exists | Needs integration into Login flow with device context |

### 3.4 CONFLICTING

| Item | Frozen Authority | Current State | Resolution |
|---|---|---|---|
| BaseService uses `Log` facade | Platform LoggerServiceInterface | Direct Laravel `Log` facade calls | Auth Services must inject LoggerServiceInterface; existing BaseService is a cosmetic concern, not a compliance blocker |
| Organization model references `App\Domains\Authentication\Models\User` | User model is at `App\Domains\User\Models\User` | Stale reference | Fix relationship in Organization model |

---

## 4. Gap Analysis Summary

| Category | IMPLEMENTED | PARTIALLY | MISSING | CONFLICTING |
|---|---|---|---|---|
| Auth Config | 0 | 0 | 2 (auth.php, sanctum.php) | 0 |
| Migrations | 1 (users) | 0 | 6 (drafts) + 1 (Sanctum) | 0 |
| Models | 1 (User) | 0 | 4 (Session, Device, RefreshToken, LoginHistory) | 0 |
| Repositories | 0 | 0 | 5 (interfaces + implementations) | 0 |
| Services | 0 | 2 (UserService partial) | 5 (Auth, Session, Token, Device, LoginHistory) | 0 |
| Controllers | 0 | 0 | 1 (AuthController, 12 methods) | 0 |
| Requests/DTOs | 0 | 0 | 12 (request + response) | 0 |
| Resources | 0 | 0 | 5 (LoginHistory, Device, Profile, TokenPair, ChangePassword) | 0 |
| Routes | 0 | 0 | 1 (AuthenticationRoutes) | 0 |
| Platform Services | 0 | 0 | 3 (Audit, Notification, FileStorage impl) | 1 (Logger contract) |
| Tests | 0 | 0 | ~30 (all types) | 0 |

---

## 5. Authentication Architecture Boundary

```
User (app/Domains/User/Models/User)
  ↓ owns
Device (app/Domains/Authentication/Models/UserDevice)
  ↓ owns
Session (app/Domains/Authentication/Models/UserSession)  ← revocation boundary
  ↓ owns
Access Token (Sanctum personal_access_tokens) + Refresh Token family
```

**Implementation requirements:**
- Session is the revocation boundary (per DD-AUTH-001, DD-AUTH-007)
- `personal_access_tokens.session_id` FK → `user_sessions.id` (CASCADE)
- `refresh_tokens.session_id` FK → `user_sessions.id` (RESTRICT)
- Device lifecycle independent from Session lifecycle (DD-AUTH-007)
- All FKs use RESTRICT for tenant-scoped ownership, SET NULL for optional references
- Multi-tenant: all tables carry `organization_id` / `branch_id`

---

## 6. Security Boundary

| Concern | Implementation Requirement |
|---|---|
| Password hashing | Argon2id (config-driven), `password` cast as `hashed` in User model |
| Lockout | Redis `auth:failed:{id}:{ip}` counter, 5 max, 15-min TTL, Super Admin exempt |
| Access Token expiry | 60 minutes (config-driven) |
| Refresh Token TTL | 30 days (config-driven) |
| Refresh rotation | Issue new pair, revoke predecessor, `replaced_by_id` chain |
| Refresh reuse detection | Revoke entire token family + owning Session + descendant Access Token |
| Token family revocation | Cascading from Session, audit event emitted |
| Session revocation | Set `revoked_at`, cascade to tokens |
| Secret redaction | Passwords/tokens never logged, never in API responses, writeOnly in OpenAPI |
| Sensitive field exposure | `ip_address`, `user_agent` → Persistence Only, excluded from Device response |

---

## 7. Audit Boundary

| Stream | Category | Lifecycle | Implementation |
|---|---|---|---|
| Canonical Audit Events | Immutable Audit Event | Append Only, Immutable | Audit Platform (AuditServiceInterface) — async via Queue |
| Login History | Operational History Projection | Immutable by default; `logout_at` Controlled One-Time Mutation | `login_histories` table |
| Password Change | DD-AUTH-018 exception | `logout_at` NOT mutated | `PASSWORD_CHANGED` + `SESSION_REVOKED` audit events only |

**Implementation note:** Audit Platform has only contracts (no concrete implementation). Authentication implementation must either build a minimal AuditPlatform implementation first, or stub the interface for Stage 06 and implement separately. The frozen architecture requires audit events to be emitted at all authentication state transitions.

---

## 8. API Implementation Mapping

| # | operationId | Controller Method | Request | Service | Resource | Key Behavior |
|---|---|---|---|---|---|---|
| 1 | `auth.login` | `AuthController@login` | `LoginRequest` | `AuthService.login()` | `LoginResponse` | Device resolve/register, Session create, Token pair issue, Login History append, Audit event |
| 2 | `auth.logout` | `AuthController@logout` | (Bearer token) | `AuthService.logout()` | `MessageResponse` | Revoke current Session + descendant tokens, set `logout_at` |
| 3 | `auth.logoutAll` | `AuthController@logoutAll` | (Bearer token) | `AuthService.logoutAll()` | `MessageResponse` | Revoke all User Sessions + tokens, Devices remain, Audit events |
| 4 | `auth.refresh` | `AuthController@refresh` | `RefreshTokenRequest` | `TokenService.refresh()` | `TokenPairResponse` | Rotation, reuse detection, family+Session revoke on reuse |
| 5 | `auth.forgotPassword` | `AuthController@forgotPassword` | `ForgotPasswordRequest` | `AuthService.forgotPassword()` | `MessageResponse` | Generic response, Password Broker, queued notification |
| 6 | `auth.resetPassword` | `AuthController@resetPassword` | `ResetPasswordRequest` | `AuthService.resetPassword()` | `MessageResponse` | Consume token, Argon2id hash, revoke all Sessions |
| 7 | `auth.changePassword` | `AuthController@changePassword` | `ChangePasswordRequest` | `AuthService.changePassword()` | `ChangePasswordResponse` | Preserve current Session, revoke others, DD-AUTH-018 exception, audit |
| 8 | `auth.profile.show` | `AuthController@profile` | (Bearer token) | `AuthService.getProfile()` | `ProfileResponse` | Resolve User + Org + Branch + Roles + Permissions |
| 9 | `auth.profile.update` | `AuthController@updateProfile` | `UpdateProfileRequest` | `AuthService.updateProfile()` | `ProfileResponse` | Name, Phone, Photo (multipart via FileStorage) |
| 10 | `auth.loginHistory.index` | `AuthController@loginHistory` | `LoginHistoryRequest` | `LoginHistoryService.list()` | `LoginHistoryResource` | Paginated `login_at DESC, id DESC`, tenant-scoped, nullable enrichment |
| 11 | `auth.devices.index` | `AuthController@devices` | `DeviceListRequest` | `DeviceService.list()` | `DeviceResource` | Paginated `last_activity_at DESC, id DESC`, derived `is_active` |
| 12 | `auth.devices.destroy` | `AuthController@revokeDevice` | (path param) | `DeviceService.revoke()` | `MessageResponse` | Current-device 409, revoke Device → Sessions → Tokens, audit |

---

## 9. Test Readiness

| Test Type | Count | Coverage |
|---|---|---|
| **FEATURE** | 14 | Login success/failure/lockout, Logout, Logout All, Refresh rotation/reuse, Forgot Password, Reset Password, Change Password (current-session preserve + other-session revoke), Profile get/update, Login History (pagination, filter, scope), Device list (pagination, sort, filter), Device revoke (current 409, ownership 403) |
| **UNIT** | 12 | AuthService (login, logout, logoutAll, changePassword), TokenService (rotate, reuse detect), SessionService (create, revoke, revokeAll), DeviceService (register, revoke), LoginHistoryService (list, logoutAt mutation) |
| **INTEGRATION** | 6 | Repository implementations (UserSession, UserDevice, RefreshToken, LoginHistory), LockoutService (Redis counter/lock), Password Broker adapter |
| **SECURITY** | 5 | Token reuse detection, Cross-tenant isolation, Ownership enforcement, Secret redaction, Sensitive field exclusion |
| **AUTHORIZATION** | 3 | Self-service boundary (DD-AUTH-003), Permission checks, Tenant scope |
| **AUDIT** | 5 | Login/logout audit events, Credential-change audit, Refresh audit, Session revocation audit, DD-AUTH-018 projection exception |
| **MULTI-TENANT** | 4 | Org-scoped device/session/history, Cross-org 403, Branch-isolation 403, Inactive tenant 403 |

---

## 10. Migration Boundary

Migration Quality Gate: **PASS**. Six reconciled drafts at `docs/Authentication/drafts/migrations/`:

| # | File | Table |
|---|---|---|
| 004 | `2026_08_02_000004_alter_users_table_for_authentication.php.txt` | users (comments only) |
| 005 | `2026_08_02_000005_create_login_histories_table.php.txt` | login_histories |
| 006 | `2026_08_02_000006_create_user_devices_table.php.txt` | user_devices |
| 007 | `2026_08_02_000007_create_user_sessions_table.php.txt` | user_sessions |
| 008 | `2026_08_02_000008_create_refresh_tokens_table.php.txt` | refresh_tokens |
| 009 | `2026_08_02_000009_add_session_id_to_personal_access_tokens_table.php.txt` | personal_access_tokens (alter) |

**Before Stage 07 (Model):**
1. Move drafts from `docs/Authentication/drafts/migrations/` to `app/Domains/Authentication/Migrations/`
2. Convert `.php.txt` extensions to `.php`
3. Publish Sanctum migration for `personal_access_tokens` base table
4. Ensure migration execution order: Organization → Branch → Users → Sanctum → 005→006→007→008→009

**Do NOT redesign or modify migration beyond implementation execution issues.**

---

## 11. Recommended Implementation Order

Based on dependency chains and existing codebase patterns:

### Phase A: Foundation (Stages 06–07)
1. **Config**: `auth.php` (sanctum guard), `sanctum.php` (token expiration, prefix)
2. **Migrations**: Publish Sanctum migration, convert all 6 draft migrations to executable `.php` files in `app/Domains/Authentication/Migrations/`
3. **Enums**: `LoginStatus` (success, failed), `DeviceType` (web, mobile, tablet, api)
4. **Models**: `UserDevice`, `UserSession`, `RefreshToken`, `LoginHistory` (Eloquent, relationships, casts)

### Phase B: Data Access (Stages 08–09)
5. **Repository Interfaces**: `UserDeviceRepositoryInterface`, `UserSessionRepositoryInterface`, `RefreshTokenRepositoryInterface`, `LoginHistoryRepositoryInterface`
6. **Repository Implementations**: Concrete repos extending `BaseRepository`

### Phase C: Business Logic (Stages 10–11)
7. **Service Interfaces**: `AuthServiceInterface`, `TokenServiceInterface`, `SessionServiceInterface`, `DeviceServiceInterface`, `LoginHistoryServiceInterface`
8. **Platform Prerequisites**: Minimal `AuditService` implementation (can start with Queue dispatch), `NotificationService` (Password Reset email), `FileStorageService` (Profile Photo)
9. **LockoutService**: Redis-based counter/lock with Super Admin exemption
10. **Service Implementations**: `AuthService`, `TokenService`, `SessionService`, `DeviceService`, `LoginHistoryService`

### Phase D: HTTP Layer (Stages 12–16)
11. **Requests/FormRequests**: All 12 request validation classes
12. **DTOs**: Request/response DTOs as needed
13. **Resources**: `LoginHistoryResource`, `DeviceResource`, `ProfileResource`, `TokenPairResource`, `ChangePasswordResource`
14. **Policy**: `AuthPolicy` (self-service boundary per DD-AUTH-003)
15. **Controller**: `AuthController` (12 methods, thin, inject Service Interfaces)
16. **Routes**: `app/Domains/Authentication/Routes/api.php` — register in bootstrap/app.php

### Phase E: Events & Background (Stages 17–18)
17. **Events/Listeners**: Auth events dispatched to Queue for async audit
18. **Notifications**: Password Reset email via Notification Platform
19. **Jobs**: Async cleanup/archive (stub for now, per lifecycle boundary)

### Phase F: Validation (Stages 17–18)
20. **Tests**: Feature (14), Unit (12), Integration (6), Security (5), Auth (3), Audit (5), Multi-tenant (4)

---

## Governance Record

| Check | Result |
|---|---|
| Implementation Scope Established | ✅ |
| Gap Analysis Complete | ✅ |
| Architecture Boundary Verified | ✅ |
| Security Boundary Verified | ✅ |
| Audit Boundary Verified | ✅ |
| API Implementation Mapping Complete | ✅ |
| Test Readiness Classified | ✅ |
| Migration Boundary Preserved | ✅ |
| Implementation Order Recommended | ✅ |
| Frozen Design Not Modified | ✅ |

**Implementation Preflight Status:** `STEP_06_03_IMPLEMENTATION_PREFLIGHT_PASS`

Next Stage: **Stage 06 — Convert draft migrations to executable**, then **Stage 07 — Model**
