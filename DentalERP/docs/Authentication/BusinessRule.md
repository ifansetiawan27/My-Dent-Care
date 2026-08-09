# Authentication Business Rules

## Rule Catalog

| Rule ID | Rule | Scope |
|---|---|---|
| `AUTH-BR-001` | Only an active User in the selected active Organization and Branch may authenticate; credentials and tenant membership must be valid. | Login |
| `AUTH-BR-002` | Access and Refresh Tokens belong to an explicit User Session; Refresh Tokens are hashed, single-use, valid for 30 days, rotated on use, and protected against reuse. | Session / Refresh Token |
| `AUTH-BR-003` | Five failed attempts lock a non-Super-Admin account for 15 minutes; successful login resets the counter. | Login / Lockout |
| `AUTH-BR-004` | A User may access only their own profile and update only Name, Phone, and Photo. | Profile |
| `AUTH-BR-005` | Login History is an Operational History Projection. Its fields are `Immutable` after creation except `logout_at`, which permits one `Controlled One-Time Mutation` under DD-AUTH-017 with lifecycle authority delegated to DD-AUTH-007. Canonical Audit Events remain `Append Only` and `Immutable`. The projection is paginated, tenant-scoped, and visible only to its User. | Login History |
| `AUTH-BR-006` | A User may list only devices they own within their authentication context. | Device List |
| `AUTH-BR-007` | A User may revoke only an owned non-current Device. Device Revocation marks the Device revoked and revokes every User Session belonging to that Device. Every descendant Access Token and Refresh Token is revoked as part of Session revocation. | Device Revocation |
| `AUTH-BR-008` | Logout revokes only the current User Session. Revoking the Session also revokes its descendant Access Token and active Refresh Token family; other Sessions and Devices remain active. | Session Revocation |
| `AUTH-BR-009` | Logout All revokes every active User Session owned by the User and all descendant Access Tokens and Refresh Token families. Devices remain registered. Login History Operational History Projections are retained and not deleted; canonical Audit Events are retained, `Append Only`, and `Immutable`. | Logout All |
| `AUTH-BR-010` | Forgot Password returns a generic response and issues a hashed, single-use token valid for 15 minutes when the account exists. | Forgot Password |
| `AUTH-BR-011` | Reset Password validates a single-use token, stores an Argon2id hash, consumes the token, and revokes all sessions. | Reset Password |
| `AUTH-BR-012` | Change Password requires the correct current password, a different policy-compliant password, and an audit event without password values. | Change Password |
| `AUTH-BR-013` | Every successful authentication creates exactly one active User Session. One User Device may own multiple User Sessions. Each active User Session owns exactly one active Sanctum Access Token and exactly one active Refresh Token family. | Session Lifecycle |
| `AUTH-BR-014` | Login does not support `remember_me`. Access Token TTL remains 60 minutes, Refresh Token TTL remains 30 days, and Device trust is managed only through a separate verified capability. | Login / Device Trust |
| `AUTH-BR-015` | Device listing is paginated with default 20 and maximum 100 records per page, ordered by `last_activity_at DESC, id DESC`. Only Devices owned by the authenticated User are returned. | Device List Pagination |
| `AUTH-BR-016` | After a successful password change, the current User Session and its active Access/Refresh Token pair remain active. Every other active Session and descendant token is revoked. Registered Devices remain registered. Change Password does not mutate Login History. The `PASSWORD_CHANGED` Audit Event is `Append Only` and `Immutable`. | Change Password Session Security |

## Account Eligibility

- User must be `active`.
- User must belong to an active Organization.
- User must belong to an active Branch.
- The selected Branch must belong to the selected Organization.
- The User must belong to the selected Organization and Branch.
- Password must be stored as an Argon2id hash.

## Failed Login and Account Lockout

- Maximum 5 failed login attempts are allowed within the configured attempt window.
- Account is locked for 15 minutes after the limit is exceeded.
- Successful login resets the failed-login counter.
- Super Admin cannot be locked automatically.
- Failed login responses must not disclose whether a username or email exists.
- Every successful and failed login is audited.

## Session Lifecycle

- Every successful authentication creates exactly one active `user_session` (`AUTH-BR-013`).
- A User Session belongs to exactly one User, Organization, Branch, and User Device.
- One `user_device` may own multiple `user_sessions`.
- Device lifecycle is independent from Session lifecycle.
- Every Authentication-issued Access Token belongs to exactly one User Session.
- Every Refresh Token family belongs to exactly one User Session.
- Refresh Token rotation remains within the same Refresh Token family and User Session.
- Revoked and replaced Refresh Tokens remain historical members of their token family for reuse detection.
- Revoking a User Session revokes its active Access Token and active Refresh Token family.

## Session Invariants

- Exactly one active Access Token is allowed per active User Session.
- Exactly one active Refresh Token family is allowed per active User Session.
- A Refresh Token family may contain multiple historical revoked or replaced tokens, but only one Refresh Token may be active at a time.
- An inactive or revoked User Session cannot own an active Access Token.
- An inactive or revoked User Session cannot own an active Refresh Token.
- A token cannot belong to more than one User Session.

## Access Token

- Access Token is issued by Laravel Sanctum.
- Access Token is valid for 60 minutes.
- Access Token is scoped to User, Organization, Branch, and Device.
- An inactive User, Organization, Branch, or revoked Device invalidates the authentication context.
- Access Token values must never be stored in logs or audit payloads.
- Every Authentication-issued Access Token belongs to exactly one User Session.

## Refresh Token

- Refresh Token is valid for 30 days.
- Refresh Token is opaque and stored only as a cryptographic hash.
- Refresh Token can be used only once.
- Every successful refresh produces a new Access Token and Refresh Token.
- The submitted Refresh Token is automatically revoked after rotation.
- Reuse of a rotated Refresh Token revokes its token family.
- Refresh validates the active User, Organization, Branch, and Device context again.
- Refresh Token values must never be stored in logs or audit payloads.
- Every Refresh Token belongs to exactly one User Session and rotation remains within that Session.

## Logout and Session Revocation

- Logout revokes only the current User Session (`AUTH-BR-008`).
- Revoking the current User Session also revokes its descendant Access Token.
- Revoking the current User Session also revokes its active Refresh Token family.
- Logout does not revoke other User Sessions.
- Logout does not revoke the registered User Device.
- Logout All revokes every active User Session and descendant token owned by the User (`AUTH-BR-009`).
- Logout All does not revoke registered Devices.
- Logout All does not delete Login History Operational History Projections or canonical Audit Events. Any approved `logout_at` projection mutation follows DD-AUTH-017 field governance and DD-AUTH-007 lifecycle authority; Audit Events remain `Append Only` and `Immutable`.
- Device Revocation marks the Device revoked and revokes every User Session belonging to that Device (`AUTH-BR-007`).
- Every descendant Access Token and Refresh Token is revoked as part of Session revocation.
- Every Session, Access Token, Refresh Token, Logout, Logout All, and Device revocation is audited.

## Password Reset

- Forgot Password Token is valid for 15 minutes.
- Reset Token is single-use.
- Reset Token is stored only as a cryptographic hash; plaintext token is never persisted.
- Forgot-password response must be identical whether the email exists or not.
- Successful password reset stores the new password as an Argon2id hash.
- After reset succeeds, all Access Tokens and Refresh Tokens for the User are revoked.
- Reset Token and password values must never be logged or audited.

## Change Password

- User must provide and validate the current password.
- New password must satisfy the approved password policy.
- New password must differ from the current password.
- New password is stored as an Argon2id hash.
- Password change is audited without recording either password.
- After a successful password change, the current User Session remains active (`AUTH-BR-016`).
- The current Session's active Sanctum Access Token remains active until normal expiry.
- The current Session's active Refresh Token family remains active.
- Every other active User Session is revoked immediately.
- Every descendant Access Token and Refresh Token family of revoked Sessions is revoked through Session ownership.
- Registered Devices remain registered and are not revoked by Change Password.
- Change Password does not mutate Login History.
- An `Append Only` and `Immutable` `PASSWORD_CHANGED` Audit Event is created with `user_id`, `initiated_by`, timestamp, current `session_id`, and `revoked_session_count`.
- The audit event must not contain the current password, new password, previous password hash, or new password hash.

## Device Ownership

- Every successful authentication creates a new User Session under an existing or newly registered Device.
- Every Device has a stable UUID.
- User Session belongs to exactly one User, Organization, Branch, and Device context.
- User can view all of their own active and revoked devices.
- Device listing is paginated with 20 records by default and a maximum of 100 (`AUTH-BR-015`).
- Device listing is ordered by `last_activity_at DESC, id DESC` for stable newest-activity-first results.
- User can revoke a specific device that they own.
- User cannot revoke another user's device.
- Revoking a Device revokes every User Session belonging to the Device.
- Descendant Access Tokens and Refresh Tokens are revoked through their owning Session, not directly through the Device.
- Revoking the current device must use the logout flow to avoid ambiguous session state.
- Super Admin follows the same self-service Device ownership boundary as other Users. Cross-user Session or Device administration is outside Authentication and belongs to the future Security Administration module under Accepted DD-AUTH-003.

## Profile Restrictions

User can update only:

- Name.
- Phone.
- Photo.

User cannot update through the Authentication profile endpoint:

- Username.
- Employee Code.
- Email.
- Status.
- Organization.
- Branch.
- Roles.
- Permissions.

Profile updates are limited to the authenticated User's own profile.

## Login History

The following data is retained when available:

- Login Time.
- Logout Time.
- IP Address.
- Browser.
- Operating System.
- Device Name.
- Country.
- Login Status.
- Failure Reason code without sensitive detail.

Additional rules:

- Object Category: `Operational History Projection`.
- Default lifecycle: `Immutable` after creation.
- Exception: `logout_at` permits one `Controlled One-Time Mutation` from `NULL` to its approved timestamp under Accepted DD-AUTH-017, with lifecycle authority delegated to Accepted DD-AUTH-007.
- Login History is not a canonical Audit Event.
- Canonical Audit Events remain separate, `Append Only`, and `Immutable`.
- User can view only their own login history.
- Login history queries are paginated and tenant-scoped.
- Login history follows the Audit Platform retention policy.

## Token Expiry

| Token | Expiry |
|---|---|
| Access Token | 60 minutes |
| Refresh Token | 30 days |
| Forgot Password Token | 15 minutes |

Token expiry values are configuration-driven and must not be hardcoded outside environment-backed configuration.

## Remember Me and Device Trust

- Login Request does not accept `remember_me` (`AUTH-BR-014`).
- Access Token TTL remains 60 minutes for every login.
- Refresh Token TTL remains 30 days for every login.
- Login does not automatically mark a Device as trusted.
- Device trust is a separate capability that requires its own verification, authorization, audit, and API contract.
- `remember_me` is not persisted on User, Device, Session, Access Token, or Refresh Token records.

## Authorization Context

- Authentication establishes the User, Organization, Branch, Device, roles, and permissions context.
- Spatie team scope is set to the authenticated Organization before permission checks.
- Authorization decisions remain outside the Authentication module and are enforced by Policies and Spatie Permission.
