# Authentication Traceability Matrix

## Status Legend

| Status | Meaning |
|---|---|
| `ACCEPTED` | Design decision passed its Quality Gate. |
| `ACCEPTED` | ADR or Decision is governance-authoritative within its scope. |
| `SUPERSEDED` | Historical Decision replaced by an Accepted superseding Decision. |
| `PROPOSED` | Decision is documented but not binding. |
| `TBD` | Decision remains unresolved. |
| `PLANNED` | Implementation or test does not exist yet. |

Governance baseline: `DD-AUTH-007`, `DD-AUTH-010`, `DD-AUTH-017`, `DD-AUTH-018`, `ADR-005`, and `ADR-006` are Accepted. `DD-AUTH-005` is Superseded by `DD-AUTH-017`; ADR-004 is Superseded by ADR-006. Both superseded records remain historical evidence only.

Authority boundary: Accepted `DD-AUTH-003` restricts Authentication Session and Device operations to self-service for every actor, including Super Admin. Cross-user administration is excluded and belongs to a separate future Security Administration contract.

Synchronization baseline: Database Design, ERD, API.md, OpenAPI, Business Rules, Flow, Flowchart, and Sequence Diagram reference Accepted `DD-AUTH-017` as the active field-policy authority. DD-AUTH-005 remains historical supersession evidence only.

## Requirement Traceability

| Requirement | Business Rules | ADRs | Decisions | Database Design | ERD | API.md | OpenAPI | Test Scenario |
|---|---|---|---|---|---|---|---|---|
| `AUTH-REQ-001` Login and Session creation | `AUTH-BR-001`, `003`, `013`, `014` | ADR-001, ADR-002, ADR-005, ADR-006 | DD-AUTH-001 `ACCEPTED`, DD-AUTH-007 `ACCEPTED`, DD-AUTH-008 `ACCEPTED`, DD-AUTH-017 `ACCEPTED` | Users, Devices, Sessions, Tokens, Redis lockout | User → Device → Session → Tokens | POST `/auth/login` | `auth.login` | `PLANNED`: success, invalid credentials, tenant mismatch, lockout, throttling, reject `remember_me` |
| `AUTH-REQ-002` Logout current Session | `AUTH-BR-008` | ADR-002, ADR-005, ADR-006 | DD-AUTH-001 `ACCEPTED`, DD-AUTH-007 `ACCEPTED` | Session revocation matrix | Current Session → descendant tokens | POST `/auth/logout` | `auth.logout` | `PLANNED`: current Session revoked; other Sessions and Device retained |
| `AUTH-REQ-003` Logout all User Sessions | `AUTH-BR-009` | ADR-002, ADR-005, ADR-006 | DD-AUTH-001 `ACCEPTED`, DD-AUTH-003 `ACCEPTED`, DD-AUTH-007 `ACCEPTED` | Session revocation matrix | User → all Sessions → tokens | POST `/auth/logout-all` | `auth.logoutAll` | `PLANNED`: all Sessions revoked; Devices and audit retained |
| `AUTH-REQ-004` Refresh rotation and reuse detection | `AUTH-BR-002`, `013` | ADR-002, ADR-005 | DD-AUTH-001 `ACCEPTED`, DD-AUTH-007 `ACCEPTED`, DD-AUTH-017 `ACCEPTED` | `user_sessions`, `refresh_tokens` | Session → Refresh Token family | POST `/auth/refresh` | `auth.refresh` | `PLANNED`: rotation, one active pair, expiry, reuse-family/Session/Access Token revocation |
| `AUTH-REQ-005` Forgot Password | `AUTH-BR-010` | ADR-003, ADR-005, ADR-006 | DD-AUTH-007 `ACCEPTED`, DD-AUTH-017 `ACCEPTED` | Framework-managed password reset | Framework-managed node only | POST `/auth/forgot-password` | `auth.forgotPassword` | `PLANNED`: generic response, throttling, queued notification |
| `AUTH-REQ-006` Reset Password and Sessions | `AUTH-BR-011` | ADR-003, ADR-005, ADR-006 | DD-AUTH-007 `ACCEPTED`, DD-AUTH-017 `ACCEPTED` | Password broker, Sessions, Tokens | Password broker + all User Sessions | POST `/auth/reset-password` | `auth.resetPassword` | `PLANNED`: token TTL/use, password hash, all Sessions revoked |
| `AUTH-REQ-007` Change Password | `AUTH-BR-012`, `016` | ADR-005, ADR-006 | DD-AUTH-004 `ACCEPTED`, DD-AUTH-007 `ACCEPTED`, DD-AUTH-017 `ACCEPTED`, DD-AUTH-018 `ACCEPTED` | User, `user_sessions`, `personal_access_tokens`, `refresh_tokens` | User → Sessions → Tokens; Login History unchanged | POST `/auth/change-password` | `auth.changePassword` | `PLANNED`: current-password validation, current Session continuity, other Sessions revoked, Devices retained, unchanged Login History, audit |
| `AUTH-REQ-008` Get Profile | `AUTH-BR-004` | ADR-002 | Not Applicable — existing User-domain authority | Users, Organizations, Branches, Spatie | Existing domain entities | GET `/auth/profile` | `auth.profile.show` | `PLANNED`: ownership, inactive context, redaction, nullability |
| `AUTH-REQ-009` Update Profile | `AUTH-BR-004` | ADR-006 | Not Applicable — existing User/FileStorage authority | User and FileStorage Platform | Existing User entity | PUT `/auth/profile` | `auth.profile.update` | `PLANNED`: multipart Name/Phone/Photo and forbidden fields |
| `AUTH-REQ-010` Login History | `AUTH-BR-005` | ADR-005, ADR-006 | DD-AUTH-007 `ACCEPTED`, DD-AUTH-010 `ACCEPTED`, DD-AUTH-017 `ACCEPTED` | `login_histories` deterministic indexes | User/Device → Login History | GET `/auth/login-history` | `auth.loginHistory.index` | `PLANNED`: scope, filters, pagination, nullable enrichment, ordering |
| `AUTH-REQ-011` Device List | `AUTH-BR-006`, `015` | ADR-002, ADR-005, ADR-006 | DD-AUTH-003 `ACCEPTED`, DD-AUTH-006 `ACCEPTED`, DD-AUTH-007 `ACCEPTED`, DD-AUTH-017 `ACCEPTED` | `user_devices` activity index | User → Devices → Sessions | GET `/auth/devices` | `auth.devices.index` | `PLANNED`: default 20, max 100, sort/filter allowlists, ownership, nullable enrichment |
| `AUTH-REQ-012` Device Revocation | `AUTH-BR-007` | ADR-002, ADR-005, ADR-006 | DD-AUTH-001 `ACCEPTED`, DD-AUTH-003 `ACCEPTED`, DD-AUTH-007 `ACCEPTED` | Device/Session revocation matrix | Device → Sessions → Tokens | DELETE `/auth/devices/{deviceId}` | `auth.devices.destroy` | `PLANNED`: ownership, current-device conflict, cascade, audit retention |
| `AUTH-REQ-013` Distributed Lockout | `AUTH-BR-003` | ADR-001, ADR-006 | — | Redis lockout keys | Redis state + Login History | Login error contract | `auth.login` errors | `PLANNED`: atomic count, fifth failure, TTL, Super Admin exception |
| `AUTH-REQ-014` Immutable Audit | `AUTH-BR-001`, `002`, `003`, `007`, `008`, `009`, `011`, `012`, `016` | ADR-005, ADR-006 | DD-AUTH-007 `ACCEPTED`, DD-AUTH-018 `ACCEPTED` | Login History projection + Audit Platform | Operational History Projection + Immutable Audit Events | Login, Refresh, Reset Password, Logout, Logout All, Change Password, Device Revocation | `auth.login`, `auth.refresh`, `auth.resetPassword`, `auth.logout`, `auth.logoutAll`, `auth.changePassword`, `auth.devices.destroy` | `PLANNED`: actor, context, redaction, retention |
| `AUTH-REQ-015` Multi-tenant context | `AUTH-BR-001`, `002`, `004`, `005`, `006`, `007`, `008`, `009`, `012`, `015`, `016` | ADR-002, ADR-006 | DD-AUTH-001 `ACCEPTED`, DD-AUTH-003 `ACCEPTED`, DD-AUTH-007 `ACCEPTED` | Tenant FKs on Device/Session/Login History | Organization/Branch → Device/Session/Login History | All protected Authentication operations | `auth.logout`, `auth.logoutAll`, `auth.changePassword`, `auth.profile.show`, `auth.profile.update`, `auth.loginHistory.index`, `auth.devices.index`, `auth.devices.destroy` | `PLANNED`: cross-tenant isolation and inactive context |
| `AUTH-REQ-016` Performance and bounded lists | `AUTH-BR-003`, `005`, `015` | ADR-001 | DD-AUTH-002 `ACCEPTED`, DD-AUTH-006 `ACCEPTED`, DD-AUTH-010 `ACCEPTED` | Redis and query indexes | Indexed Authentication entities | Pagination contracts | Pagination schemas | `PLANNED`: login p95, token validation, query plans |
| `AUTH-REQ-017` Secret redaction | `AUTH-BR-002`, `010`, `011`, `012` | ADR-002, ADR-003, ADR-005, ADR-006 | DD-AUTH-002 `ACCEPTED` | Hashed tokens/password | Token/password entities | Write-only request fields | `writeOnly` schemas | `PLANNED`: no plaintext persistence/logging |
| `AUTH-REQ-018` Horizontal scale/availability | `AUTH-BR-003`, `015` | ADR-001, ADR-002 | DD-AUTH-006 `ACCEPTED` | Shared PostgreSQL/Redis | Platform dependencies | Bounded versioned API | API version and pagination | `PLANNED`: distributed state, load, failover |
## Endpoint Traceability

| Endpoint | Requirements | Business Rules | ERD / Data | operationId | Decisions | Test Status |
|---|---|---|---|---|---|---|
| POST `/api/v1/auth/login` | `AUTH-REQ-001`, `013`, `014`, `015`, `017` | `AUTH-BR-001`, `003`, `013`, `014` | Users, Devices, Sessions, Login History, Tokens, Redis | `auth.login` | ADR-001, ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-007, DD-AUTH-008, DD-AUTH-017, ADR-005 | `PLANNED` |
| POST `/api/v1/auth/logout` | `AUTH-REQ-002`, `014`, `015` | `AUTH-BR-008` | Sessions, Access/Refresh Tokens, Login History projection | `auth.logout` | ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |
| POST `/api/v1/auth/logout-all` | `AUTH-REQ-003`, `014`, `015` | `AUTH-BR-009` | All User Sessions and descendant tokens | `auth.logoutAll` | ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-003, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |
| POST `/api/v1/auth/refresh` | `AUTH-REQ-004`, `014`, `015`, `017` | `AUTH-BR-002`, `013` | Session and Refresh Token family | `auth.refresh` | ADR-002, DD-AUTH-001, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |
| POST `/api/v1/auth/forgot-password` | `AUTH-REQ-005`, `017` | `AUTH-BR-010` | Framework password broker | `auth.forgotPassword` | ADR-003, ADR-006, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |
| POST `/api/v1/auth/reset-password` | `AUTH-REQ-006`, `014`, `017` | `AUTH-BR-011` | Framework password broker, Sessions, Tokens | `auth.resetPassword` | ADR-003, ADR-006, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |
| POST `/api/v1/auth/change-password` | `AUTH-REQ-007`, `014`, `015`, `017` | `AUTH-BR-012`, `016` | User, Sessions, Access/Refresh Tokens; Login History unchanged | `auth.changePassword` | ADR-006, DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, ADR-005 | `PLANNED` |
| GET `/api/v1/auth/profile` | `AUTH-REQ-008`, `015` | `AUTH-BR-004` | User, Organization, Branch, Spatie | `auth.profile.show` | ADR-002, ADR-006 | `PLANNED` |
| PUT `/api/v1/auth/profile` | `AUTH-REQ-009`, `015` | `AUTH-BR-004` | User, FileStorage Platform | `auth.profile.update` | ADR-006 | `PLANNED` |
| GET `/api/v1/auth/login-history` | `AUTH-REQ-010`, `015`, `016` | `AUTH-BR-005` | Login History and deterministic indexes | `auth.loginHistory.index` | ADR-006, DD-AUTH-007, DD-AUTH-010, DD-AUTH-017, ADR-005 | `PLANNED` |
| GET `/api/v1/auth/devices` | `AUTH-REQ-011`, `015`, `016` | `AUTH-BR-006`, `015` | Devices and activity index | `auth.devices.index` | ADR-002, ADR-006, DD-AUTH-003, DD-AUTH-006, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |
| DELETE `/api/v1/auth/devices/{deviceId}` | `AUTH-REQ-012`, `014`, `015` | `AUTH-BR-007` | Device → Sessions → Tokens | `auth.devices.destroy` | ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-003, DD-AUTH-007, DD-AUTH-017, ADR-005 | `PLANNED` |

## Business Rule Coverage

| Business Rule | Coverage | Decisions | Test Status |
|---|---|---|---|
| `AUTH-BR-001` | Login eligibility and tenant membership | DD-AUTH-001 | `PLANNED` |
| `AUTH-BR-002` | Session and Refresh Token lifecycle | DD-AUTH-001 | `PLANNED` |
| `AUTH-BR-003` | Failed login and Redis lockout | ADR-001 | `PLANNED` |
| `AUTH-BR-004` | Own profile and update restrictions | Not Applicable — User Profile fields are outside DD-AUTH-017 | `PLANNED` |
| `AUTH-BR-005` | Login History visibility/pagination | DD-AUTH-017, DD-AUTH-010 | `PLANNED` |
| `AUTH-BR-006` | Device ownership | DD-AUTH-003, DD-AUTH-017 | `PLANNED` |
| `AUTH-BR-007` | Device → Session → Token revocation | DD-AUTH-001, DD-AUTH-003, DD-AUTH-007 | `PLANNED` |
| `AUTH-BR-008` | Current Session logout | DD-AUTH-001, DD-AUTH-007 | `PLANNED` |
| `AUTH-BR-009` | Logout All and immutable audit | DD-AUTH-001, DD-AUTH-003, DD-AUTH-007 | `PLANNED` |
| `AUTH-BR-010` | Forgot Password generic response/TTL | ADR-003 | `PLANNED` |
| `AUTH-BR-011` | Reset Password and Session revocation | DD-AUTH-001, DD-AUTH-002 | `PLANNED` |
| `AUTH-BR-012` | Change Password | DD-AUTH-004, DD-AUTH-018 | `PLANNED` |
| `AUTH-BR-013` | Session creation and active-token invariants | DD-AUTH-001 | `PLANNED` |
| `AUTH-BR-014` | No Remember Me; trusted Device separated | DD-AUTH-008 | `PLANNED` |
| `AUTH-BR-015` | Device pagination/sorting/filtering | DD-AUTH-006 | `PLANNED` |
| `AUTH-BR-016` | Change Password Session security, projection exception, and audit | DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, DD-AUTH-018 | `PLANNED` |

## Design Decision Traceability

| Decision | Status | ADR / Document Mapping | Affected Contract | Test Status |
|---|---|---|---|---|
| `DD-AUTH-001` | `ACCEPTED` | ADR-002, ERD, Database Design, Business Rules, API.md, OpenAPI | User → Device → Session → Tokens; revocation | `PLANNED` |
| `DD-AUTH-002` | `ACCEPTED` | Requirement, Business Rules, User design, Authentication flows | Argon2id, rehash, legacy bcrypt migration | `PLANNED` |
| `DD-AUTH-003` | `ACCEPTED` | Authentication self-service boundary and future Security Administration | Super Admin Session/Device scope | `PLANNED` |
| `DD-AUTH-004` | `ACCEPTED` | Business Rules, API/OpenAPI, Flow, ADR-002 | Change Password Session behavior | `PLANNED` |
| `DD-AUTH-005` | `SUPERSEDED` | Historical field/nullability policy; superseded by DD-AUTH-017 | Historical Login History and Device nullability decision | `PLANNED` |
| `DD-AUTH-006` | `ACCEPTED` | Requirement, Business Rules, ERD/DB index, API.md, OpenAPI | Device pagination, sort/filter allowlists | `PLANNED` |
| `DD-AUTH-007` | `ACCEPTED` | ADR-005, Global Standards, Database Design, ERD, API.md, OpenAPI, Flow, Flowchart, Sequence Diagram | Authentication lifecycle, audit separation, retention/archive/cleanup boundary | `PLANNED` |
| `DD-AUTH-008` | `ACCEPTED` | Business Rules, API.md, OpenAPI, Login request | Remember Me removed; trusted Device separated | `PLANNED` |
| `DD-AUTH-010` | `ACCEPTED` | ERD, Database Design, API.md, OpenAPI, Flow, future Repository query | Deterministic Login History index direction and UUID tie-breaker | `PLANNED` |
| `DD-AUTH-017` | `ACCEPTED` | DD-AUTH-005 supersession, DD-AUTH-007, ADR-005, Global Standards, Database Design, ERD, API.md, OpenAPI, Business Rules, Flow, Flowchart, Sequence Diagram | Active Authentication field classification, exposure, nullability, and field governance | `PLANNED` |
| `DD-AUTH-018` | `ACCEPTED` | DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, API.md, OpenAPI, Business Rules, Flow, Flowchart, Sequence Diagram | Credential-change revocation does not mutate Login History or `logout_at` | `PLANNED` |
| `ADR-004` | `SUPERSEDED` | Historical Authentication audit strategy; superseded by ADR-006 | Durable-audit and transient-state separation history | `PLANNED` |
| `ADR-005` | `ACCEPTED` | Platform governance and Global Standards | Platform lifecycle, audit, retention, archive, Legal Hold, and cleanup authority | `PLANNED` |
| `ADR-006` | `ACCEPTED` | ADR-004 supersession, DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, API.md, OpenAPI, diagrams, Traceability | Canonical Audit Events versus Login History Operational History Projection authority | `PLANNED` |

### DD-AUTH-001 Document Mapping

| Decision | Document | Traced Impact |
|---|---|---|
| `DD-AUTH-001` | `docs/ADR/ADR-002-Authentication-Token.md` | Token strategy uses explicit Session boundary. |
| `DD-AUTH-001` | `docs/Authentication/ERD.md` | Device → Session → Access/Refresh Tokens. |
| `DD-AUTH-001` | `docs/Authentication/BusinessRule.md` | Session creation and revocation invariants. |
| `DD-AUTH-001` | `docs/Authentication/API.md` | Session-oriented endpoint behavior without public `session_id`. |
| `DD-AUTH-001` | `docs/api/openapi.yaml` | Session lifecycle descriptions and token-pair invariant. |

### DD-AUTH-017 Field Policy Mapping

| Requirement | Business Rule | Decision | Database Design | ERD | API.md | OpenAPI | Authentication Test |
|---|---|---|---|---|---|---|---|
| `AUTH-REQ-010` Login History | `AUTH-BR-005` | `DD-AUTH-017` | Complete field classification; nullable enrichment and lifecycle fields; Sensitive IP boundary | Login History classification/nullability annotations | Stable response shape with nullable values | `LoginHistory` nullable unions and active `x-decisions` | `PLANNED`: nullable enrichment, active Session logout null, success failure_reason null, Sensitive exposure |
| `AUTH-REQ-011` User Device | `AUTH-BR-006`, `AUTH-BR-015` | `DD-AUTH-017` | Complete field classification; nullable enrichment/lifecycle; derived `is_active`; Sensitive fields excluded | Device classification/nullability annotations | Stable response shape with nullable values | `Device` nullable unions and active `x-decisions` | `PLANNED`: nullable enrichment/lifecycle values, required property presence, derived formula, exclusions |

## Persistent Entity Coverage

| Persistent Entity | Database Design | ERD | Active Decisions | API / Flow Coverage | Test Status |
|---|---|---|---|---|---|
| Login History | `login_histories` | `LOGIN_HISTORIES` | DD-AUTH-007, DD-AUTH-010, DD-AUTH-017, DD-AUTH-018, ADR-005 | Login, Logout, Login History, credential-change projection exception; Flow, Flowchart, Sequence Diagram | `PLANNED` |
| User Device | `user_devices` | `USER_DEVICES` | DD-AUTH-001, DD-AUTH-003, DD-AUTH-006, DD-AUTH-007, DD-AUTH-008, DD-AUTH-017, ADR-005 | Login, Device List, Device Revocation; Flow, Flowchart, Sequence Diagram | `PLANNED` |
| User Session | `user_sessions` | `USER_SESSIONS` | DD-AUTH-001, DD-AUTH-003, DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, ADR-005 | Login, Refresh, Logout, Logout All, password lifecycle, Device Revocation; Flow, Flowchart, Sequence Diagram | `PLANNED` |
| Access Token | `personal_access_tokens` | `PERSONAL_ACCESS_TOKENS` | ADR-002, DD-AUTH-001, DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, ADR-005 | Login, Refresh, revocation and password lifecycle; Flow, Flowchart, Sequence Diagram | `PLANNED` |
| Refresh Token | `refresh_tokens` | `REFRESH_TOKENS` | ADR-002, DD-AUTH-001, DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, ADR-005 | Login, Refresh rotation/reuse, revocation and password lifecycle; Flow, Flowchart, Sequence Diagram | `PLANNED` |
| Password Reset Token | Framework-managed password reset storage | Framework-managed password reset node | ADR-003, DD-AUTH-007, DD-AUTH-017, ADR-005 | Forgot Password, Reset Password; Flow, Flowchart, Sequence Diagram | `PLANNED` |

## Diagram Traceability

| Diagram Artifact | Covered Requirements | Covered Rules / Decisions | Status |
|---|---|---|---|
| `docs/Authentication/Flow.md` | All 12 API operations, failed login/lockout, dependency boundary, audit separation, and asynchronous cleanup; NFR metrics remain Requirement/Business Rule concerns | Accepted Authentication Decisions and ADRs through DD-AUTH-018 and ADR-006 | Resolved |
| `docs/Authentication/Flowchart.md` | Login/failure/lockout, refresh, logout, forgot/reset/change password, Profile, Device, Login History, audit, retention/archive/cleanup | DD-AUTH-003, DD-AUTH-007, DD-AUTH-010, DD-AUTH-017, DD-AUTH-018, ADR-005, ADR-006 | Resolved |
| `docs/Authentication/SequenceDiagram.md` | Login success/failure, lockout, token issuance/rotation/reuse, Session/Device revocation, forgot/reset/change password, Profile, Login History, Device list, asynchronous cleanup | DD-AUTH-003, DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, ADR-001–ADR-003, ADR-005, ADR-006 | Resolved |

## Coverage Rules

- Every Requirement maps to Business Rules, ADRs, Decisions, Database Design, ERD, API.md, OpenAPI, and a test scenario.
- Every OpenAPI operation appears exactly once in Endpoint Traceability.
- Every Business Rule `AUTH-BR-001` through `AUTH-BR-016` appears exactly once in Business Rule Coverage.
- Every Accepted or unresolved required decision is represented with its real status.
- DD-AUTH-005 appears only as `SUPERSEDED` historical lineage; DD-AUTH-017 is the active field-policy authority.
- Database Design and ERD resolve all six Authentication persistent entities governed by DD-AUTH-017.
- Flow, Flowchart, and Sequence Diagram resolve and trace to Accepted authority.
- Missing implementation and tests are marked `PLANNED`; they are never inferred as complete.
- Any upstream change invalidates affected downstream traceability until Drift Detection passes again.
