# Authentication Module Requirements

## Objective

Provide secure, auditable, multi-organization, and multi-branch authentication for Dental ERP Enterprise.

## Actors

### Super Admin

- Login.
- Logout.
- Manage only their own active sessions through the same self-service contract as other Users.
- View login history.
- Reset own password.
- Update own profile.

Cross-user Session or Device administration belongs to the future Security Administration module under Accepted DD-AUTH-003 and is outside Authentication.

### Organization Owner

- Login.
- Logout.
- Manage own sessions.
- Update own profile.

### Branch Manager

- Login.
- Logout.
- Manage own sessions.

### Doctor

- Login.
- Logout.
- Update own profile.

### Staff

- Login.
- Logout.
- Change own password.

## Functional Scope

The Authentication module covers:

- Login using username or email.
- Logout of the current session.
- Logout of all sessions.
- Access token issuance and revocation.
- Refresh token issuance, rotation, reuse detection, and revocation.
- Forgot password.
- Reset password.
- Change password.
- User profile retrieval and self-service update.
- Device registration, listing, and revocation.
- Session management.
- Login history.
- Failed-login tracking and temporary account lockout.
- Authentication audit events.

## Requirement Catalog

This catalog assigns stable identifiers to the existing Authentication scope. It does not add new scope.

### AUTH-REQ-001 — Login and Session Creation

- **Requirement Statement:** An eligible User can authenticate with username or email in an active Organization and Branch context, create one User Session under a recognized Device, and receive an Access/Refresh Token pair.
- **Actor / Scope:** All Authentication actors; own Authentication context.
- **Acceptance Criteria:** Valid credentials and tenant membership create one Session and one active token pair; invalid or inactive context creates no token authority.
- **Business Rule Reference:** `AUTH-BR-001`, `AUTH-BR-003`, `AUTH-BR-013`, `AUTH-BR-014`.
- **Explicit Exclusion:** Authorization decisions, `remember_me`, MFA, OAuth, and SSO are outside this requirement.

### AUTH-REQ-002 — Logout Current Session

- **Requirement Statement:** An authenticated User can terminate only the current User Session and its descendant Access/Refresh Token authority.
- **Actor / Scope:** Authenticated User; current Session only.
- **Acceptance Criteria:** Current Session and descendant tokens are revoked; other Sessions and the registered Device remain active; lifecycle evidence is emitted.
- **Business Rule Reference:** `AUTH-BR-008`.
- **Explicit Exclusion:** Other User Sessions and Device revocation are not part of current-session logout.

### AUTH-REQ-003 — Logout All Sessions

- **Requirement Statement:** An authenticated User can revoke all active User Sessions and descendant token authority owned by that User.
- **Actor / Scope:** Authenticated User; all own Sessions.
- **Acceptance Criteria:** Every owned active Session and descendant token family is revoked; registered Devices and retained lifecycle evidence are not deleted.
- **Business Rule Reference:** `AUTH-BR-009`.
- **Explicit Exclusion:** Other Users' Sessions and Devices are outside this self-service operation.

### AUTH-REQ-004 — Refresh Rotation and Reuse Detection

- **Requirement Statement:** A valid Refresh Token can be exchanged once within its Session family, while reuse of a rotated token deterministically revokes the affected family.
- **Actor / Scope:** Holder of a valid Refresh Token; owning Session and token family.
- **Acceptance Criteria:** Rotation invalidates the predecessor before activating the replacement pair; expired/revoked tokens fail; reuse revokes the family.
- **Business Rule Reference:** `AUTH-BR-002`, `AUTH-BR-013`.
- **Explicit Exclusion:** Rotation cannot change User, tenant, Device, Session, or token-family ownership.

### AUTH-REQ-005 — Forgot Password

- **Requirement Statement:** A password-reset request returns a generic response and, when an account exists, initiates the approved single-use reset-token lifecycle.
- **Actor / Scope:** Unauthenticated requester; submitted email.
- **Acceptance Criteria:** Existing and non-existing emails receive the same response; issued reset material is hashed, bounded, and never logged.
- **Business Rule Reference:** `AUTH-BR-010`.
- **Explicit Exclusion:** The response does not disclose account existence or return reset-token persistence details.

### AUTH-REQ-006 — Reset Password and Session Revocation

- **Requirement Statement:** A valid single-use reset token permits password recovery and revokes all existing User Sessions and descendant token authority.
- **Actor / Scope:** Reset-token holder; resolved User account.
- **Acceptance Criteria:** Token validity and password policy are enforced; new password is hashed; token is consumed; all Sessions are revoked; Devices remain registered; evidence excludes secrets.
- **Business Rule Reference:** `AUTH-BR-011`.
- **Explicit Exclusion:** No existing Session remains active after password recovery.

### AUTH-REQ-007 — Change Password

- **Requirement Statement:** An authenticated User can change their own password after current-password validation while preserving the current Session and revoking every other Session.
- **Actor / Scope:** Authenticated User; own credential and Sessions.
- **Acceptance Criteria:** Current Session/token family remains active; all other Sessions and descendants are revoked; Devices remain registered; `PASSWORD_CHANGED` evidence contains no password material.
- **Business Rule Reference:** `AUTH-BR-012`, `AUTH-BR-016`.
- **Explicit Exclusion:** Other Users' credentials and Sessions are not affected.

### AUTH-REQ-008 — Get Profile

- **Requirement Statement:** An authenticated User can retrieve the approved projection of their own profile and active tenant context.
- **Actor / Scope:** Authenticated User; own profile.
- **Acceptance Criteria:** Only approved fields, Organization/Branch context, roles, and permissions are returned; sensitive persistence-only fields are excluded.
- **Business Rule Reference:** `AUTH-BR-004`.
- **Explicit Exclusion:** Other Users' profiles and profile administration are outside this requirement.

### AUTH-REQ-009 — Update Profile

- **Requirement Statement:** An authenticated User can update only approved self-service profile fields.
- **Actor / Scope:** Authenticated User; own Name, Phone, and Photo.
- **Acceptance Criteria:** Only Name, Phone, and Photo changes are accepted; ownership and file constraints are enforced; other fields remain unchanged.
- **Business Rule Reference:** `AUTH-BR-004`.
- **Explicit Exclusion:** Username, employee code, email, status, tenant, roles, permissions, gender, and birth date cannot be changed here.

### AUTH-REQ-010 — Login History

- **Requirement Statement:** An authenticated User can retrieve a tenant-scoped, paginated Login History Operational History Projection.
- **Actor / Scope:** Authenticated User; own history in active tenant context.
- **Acceptance Criteria:** Stable nullable response fields are present; filters and pagination are bounded; `logout_at` follows its approved controlled mutation; canonical Audit Events remain separate.
- **Business Rule Reference:** `AUTH-BR-005`.
- **Explicit Exclusion:** Login History is not the canonical Audit Event source and does not expose persistence-only ownership fields.

### AUTH-REQ-011 — Device List

- **Requirement Statement:** An authenticated User can retrieve a bounded, stable list of their own recognized Devices.
- **Actor / Scope:** Authenticated User; own Devices.
- **Acceptance Criteria:** Pagination defaults to 20 and is capped at 100; sort/filter inputs are allowlisted; nullable enrichment remains present; `is_active` is derived.
- **Business Rule Reference:** `AUTH-BR-006`, `AUTH-BR-015`.
- **Explicit Exclusion:** Other Users' Devices and administrative target-user listing are outside this self-service requirement.

### AUTH-REQ-012 — Device Revocation

- **Requirement Statement:** An authenticated User can revoke an owned non-current Device, causing revocation of descendant Sessions and token authority.
- **Actor / Scope:** Authenticated User; owned non-current Device.
- **Acceptance Criteria:** Ownership is verified; Device and descendants are revoked; Login History projections and canonical Audit Events are retained.
- **Business Rule Reference:** `AUTH-BR-007`.
- **Explicit Exclusion:** Other Users' Devices and current-Device revocation outside logout are excluded.

### AUTH-REQ-013 — Distributed Lockout

- **Requirement Statement:** Failed authentication attempts are counted consistently across application instances and temporarily lock eligible non-Super-Admin accounts at the approved threshold.
- **Actor / Scope:** Authentication platform; submitted identifier/IP and resolved User when available.
- **Acceptance Criteria:** Counting is atomic; the fifth failed attempt triggers the configured lock; successful authentication clears failures; Super Admin remains exempt from automatic lockout; all outcomes are evidenced.
- **Business Rule Reference:** `AUTH-BR-003`.
- **Explicit Exclusion:** Lockout does not reveal whether an unresolved identifier exists.

### AUTH-REQ-014 — Immutable Audit Evidence

- **Requirement Statement:** Material Authentication lifecycle actions emit canonical append-only and immutable Audit Events separate from operational projections.
- **Actor / Scope:** Authentication and Audit platforms; all material lifecycle operations.
- **Acceptance Criteria:** Actor, target, tenant context when resolved, timestamp, correlation ID, reason where required, and outcome are recorded without Secret data.
- **Business Rule Reference:** `AUTH-BR-001`, `AUTH-BR-002`, `AUTH-BR-003`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-011`, `AUTH-BR-012`, `AUTH-BR-016`.
- **Explicit Exclusion:** Login History, technical logs, and transient state are not substitutes for canonical Audit Events.

### AUTH-REQ-015 — Multi-Tenant Context

- **Requirement Statement:** Authentication authority is bound to the resolved User, Organization, Branch, Device, and Session context.
- **Actor / Scope:** All authenticated operations and token validation.
- **Acceptance Criteria:** Organization and Branch are active and related; User membership is valid; tokens cannot cross tenant or Device/Session boundaries.
- **Business Rule Reference:** `AUTH-BR-001`, `AUTH-BR-002`, `AUTH-BR-004`, `AUTH-BR-005`, `AUTH-BR-006`, `AUTH-BR-007`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-012`, `AUTH-BR-015`, `AUTH-BR-016`.
- **Explicit Exclusion:** Cross-tenant authorization and tenant administration remain outside Authentication.

### AUTH-REQ-016 — Performance and Bounded Lists

- **Requirement Statement:** Authentication operations and list contracts satisfy the documented latency and bounded-result requirements.
- **Actor / Scope:** Authentication platform operations and list consumers.
- **Acceptance Criteria:** Login and token validation meet stated targets under normal conditions; Login History and Device lists are paginated/bounded with deterministic ordering.
- **Business Rule Reference:** `AUTH-BR-003`, `AUTH-BR-005`, `AUTH-BR-015`.
- **Explicit Exclusion:** Infrastructure-specific tuning and unapproved index assumptions are outside this requirement.

### AUTH-REQ-017 — Secret Redaction

- **Requirement Statement:** Passwords, token values/hashes, authorization headers, cookies, and equivalent Secret data are excluded from logs, audit payloads, archives, and public output.
- **Actor / Scope:** Every Authentication input, persistence, logging, audit, archive, and response boundary.
- **Acceptance Criteria:** Secret inputs are write-only where applicable; persisted verifier material is protected; evidence is detached and redacted; no example contains usable secrets.
- **Business Rule Reference:** `AUTH-BR-002`, `AUTH-BR-010`, `AUTH-BR-011`, `AUTH-BR-012`.
- **Explicit Exclusion:** Legal Hold does not restore or extend retention of destroyed Secret material.

### AUTH-REQ-018 — Horizontal Scale and Availability

- **Requirement Statement:** Authentication remains consistent across horizontally scaled application instances and meets the documented availability target.
- **Actor / Scope:** Authentication platform and shared operational dependencies.
- **Acceptance Criteria:** Lockout/rate-limit state is consistent across instances; persistent state is shared and monitored; list processing is bounded; dependency health supports the availability objective.
- **Business Rule Reference:** `AUTH-BR-003`, `AUTH-BR-015`.
- **Explicit Exclusion:** Vendor-specific failover topology and infrastructure implementation are outside this design requirement.

## Out of Scope

This module does not cover:

- Authorization decisions.
- Role management.
- Permission management.
- User CRUD.
- Organization management.
- Branch management.
- Multi-factor authentication (future phase).
- OAuth provider integration (future phase).
- Single sign-on (future phase).

Authorization is supplied by Spatie Permission after Authentication establishes the User, Organization, Branch, roles, and permission context.

## Technology Constraints

- Laravel 12.
- Laravel Sanctum for access tokens.
- Spatie Laravel Permission for authorization context.
- PostgreSQL for persistent authentication records.
- Redis for distributed rate limiting and temporary account lockout.
- Queue-backed password reset notifications.

## Non-Functional Requirements

### Performance

- Login completes in less than 500 ms under normal operating conditions, excluding external notification delivery.
- Access-token validation completes in less than 100 ms under normal operating conditions.
- Login history and device-list endpoints are paginated or bounded.

### Security

- Passwords are hashed using Argon2id.
- Authentication traffic is accepted over HTTPS only in non-local environments.
- Browser-based authentication flows use applicable CSRF protection.
- Output encoding and Content Security Policy support XSS protection.
- Parameterized Eloquent/query-builder access prevents SQL injection.
- Access, refresh, reset tokens, and passwords are never written to logs or audit payloads.
- Authentication errors do not disclose whether a username or email exists.
- Refresh tokens are opaque, hashed at rest, rotated, and protected against reuse.

### Availability

- Authentication targets 99.9% service availability.
- Redis and PostgreSQL dependencies must support production redundancy and health monitoring.

### Scalability

- Supports multiple Organizations.
- Supports multiple Branches per Organization.
- Supports horizontal application scaling with shared PostgreSQL and Redis state.
- Lockout and rate-limit behavior is consistent across application instances.

### Auditability

- Every successful and failed login is recorded.
- Every logout is recorded.
- Every access-token, refresh-token, and device revocation is recorded.
- Every password change and password reset is recorded without sensitive values.

## Multi-Tenant Requirements

- Login requires an active Organization and active Branch context.
- The selected Branch must belong to the selected Organization.
- The User must belong to the selected Organization and Branch.
- Spatie team context is set to the authenticated Organization before authorization checks.
- A token cannot be used outside its assigned Organization, Branch, User, and device context.

## Acceptance Criteria

- [ ] User can log in using a valid username or email.
- [ ] Access token is returned after successful login.
- [ ] Refresh token is returned after successful login.
- [ ] Failed login attempts are counted atomically.
- [ ] Account is locked after the configured failed-attempt limit.
- [ ] Super Admin is not automatically locked.
- [ ] Login history is stored for successful and failed attempts.
- [ ] Recognized devices are recorded and associated with sessions.
- [ ] Logout revokes only the current token pair.
- [ ] Logout All revokes all access and refresh tokens for the User.
- [ ] Refresh token rotation succeeds and invalidates the submitted token.
- [ ] Reuse of a rotated refresh token revokes its token family.
- [ ] Forgot-password response does not reveal whether an email exists.
- [ ] Reset password stores a hash and revokes existing sessions.
- [ ] Change password requires validation of the current password.
- [ ] User can view and update only approved self-profile fields.
- [ ] User can list and revoke only their own devices.
- [ ] Inactive User, Organization, or Branch cannot receive new tokens.
- [ ] All authentication lifecycle events are audited.
