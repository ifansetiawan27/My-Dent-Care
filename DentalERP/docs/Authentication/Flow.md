# Authentication Flow

Lifecycle flow follows Accepted `DD-AUTH-007` and Accepted `ADR-005`. Audit evidence and Login History projection authority follow Accepted `ADR-006`. Field classification, exposure, nullability, and field governance follow Accepted `DD-AUTH-017`; DD-AUTH-005 and ADR-004 remain superseded historical evidence. Login History is an Operational History Projection with the `logout_at` controlled one-time mutation governed by DD-AUTH-017 and delegated to the lifecycle authority of DD-AUTH-007; immutable Audit Events remain separate canonical evidence.

Session and Device flows are self-service for every actor, including Super Admin. Cross-user administration is excluded under Accepted `DD-AUTH-003` and belongs to a separate Security Administration capability.

## Public Request Flow

```text
HTTP Request
  -> FormRequest validation
  -> Thin Controller adapter (implementation stage)
  -> Immutable DTO
  -> AuthenticationServiceInterface
  -> Repository and specialized platform contracts
  -> Authentication events
  -> Audit and Logging platforms
  -> Resource / ApiResponse
```

## Protected Request Flow

```text
Bearer Access Token
  -> Laravel Sanctum middleware
  -> Resolve User
  -> Validate active User
  -> Resolve Organization and Branch context
  -> Validate active Organization and Branch
  -> Validate Branch belongs to Organization
  -> Set Spatie team ID to Organization UUID
  -> Build AuthenticationContext
  -> Policy / permission middleware
  -> Protected endpoint
```

## Login Sequence

```text
Client
  -> validate login payload
  -> LockoutService: assertNotLocked(identifier)
  -> AuthenticationRepository: find user by normalized username/email
  -> verify password using constant-time hash check
  -> resolve Organization and Branch
  -> validate active User, Organization, Branch, and membership
  -> DeviceService: register or update device
  -> SessionService: create active User Session linked to User and Device
  -> TokenService: issue access and refresh token pair
  -> LockoutService: clear failed attempts
  -> persist last login and login history
  -> emit LoginSucceeded
  -> AuditService: record login
  <- TokenPairDTO + AuthenticationContext
```

## Failed Login Sequence

```text
Client
  -> credentials or tenant validation fails
  -> LockoutService: increment failed attempts
  -> if attempts >= 5 and user is not Super Admin:
       lock account for 15 minutes
  -> persist failed login history
  -> emit LoginFailed
  -> AuditService: record failure
  <- generic 401 or 423 response without identifier enumeration
```

## Logout Sequence

```text
Authenticated Client
  -> AuthenticationService: logout current session
  -> revoke current Sanctum access token
  -> revoke refresh token associated with current session/device
  -> apply controlled one-time logout_at mutation to the linked Login History when applicable
  -> emit UserLoggedOut
  -> AuditService: append immutable logout evidence
  <- success response
```

## Logout All Sequence

```text
Authenticated Client
  -> AuthenticationService: logout all sessions for current User
  -> TokenService: revoke every Sanctum access token owned by User
  -> TokenService: revoke every Refresh Token owned by User
  -> preserve Login History projections without deletion
  -> emit UserLoggedOutFromAllDevices
  -> AuditService: append immutable Logout All evidence
  <- success response
```

## Refresh Token Sequence

```text
Client
  -> validate refresh token payload
  -> hash submitted token
  -> find refresh token by hash
  -> validate expiry, revocation, device, User, Organization, and Branch
  -> if rotated-token reuse is detected:
       revoke the entire Refresh Token family
       revoke the owning Session and descendant Access Token
       emit immutable reuse/revocation evidence
       return approved generic failure without replacement
  -> otherwise, for a valid active token:
       atomically revoke the submitted Refresh Token and previous Access Token
       issue one replacement Access and Refresh Token pair
       link replacement token in the same rotation chain
       emit immutable rotation evidence
  <- new TokenPairDTO only on successful rotation
```

## Forgot Password Sequence

```text
Client
  -> submit email
  -> delegate token lifecycle to Laravel Password Broker adapter
  -> always return generic accepted response
  -> if account exists:
       framework creates single-use hashed reset token
       queue password-reset notification
  -> Audit/Logging records operational outcome without exposing identity
```

## Reset Password Sequence

```text
Client
  -> delegate token lifecycle to Laravel Password Broker adapter
  -> validate email, reset token, and password policy
  -> framework verifies single-use token
  -> update hashed password in transaction
  -> framework consumes reset token
  -> revoke all access and refresh tokens
  -> emit PasswordChanged
  -> AuditService: record password reset
  <- success response requiring a new login
```

## Change Password Sequence

```text
Authenticated Client
  -> validate current password and new password payload
  -> verify current password
  -> verify new password differs and satisfies policy
  -> update hashed password in transaction
  -> preserve current Session and current Access/Refresh Token authority
  -> revoke every other Session and descendant token authority
  -> under DD-AUTH-018: do not mutate Login History or logout_at
  -> emit PASSWORD_CHANGED
  -> emit applicable SESSION_REVOKED evidence for every revoked other Session
  -> AuditService: append immutable password-change and Session-revocation evidence
  <- success response
```

## Get Profile Sequence

```text
Authenticated Client
  -> AuthenticationContext supplies user/organization/branch IDs
  -> profile query through approved service/repository contract
  -> load Organization, Branch, roles, and permissions efficiently
  -> Resource transforms approved fields
  <- profile ApiResponse
```

## Update Profile Sequence

```text
Authenticated Client
  -> submit Name, Phone, and optional binary Photo
  -> validate safe self-service fields and binary Photo
  -> Policy verifies ownership
  -> immutable DTO
  -> if Photo exists: upload binary Photo through FileStorage Service
  -> Storage Service validates MIME type, size, and UUID storage name
  -> profile service saves the resulting User Photo path in transaction
  -> profile service updates Name and Phone
  -> emit profile-updated event / audit record
  <- updated profile resource
```

## Login History Sequence

```text
Authenticated Client
  -> validate pagination and filters
  -> scope query to current User and tenant context
  -> order by login_at DESC, id DESC under DD-AUTH-010
  -> read Login History Operational History Projection records
  <- paginated ApiResponse
```

## List Devices Sequence

```text
Authenticated Client
  -> scope device query to current User
  -> derive is_active from revoked_at
  -> return persisted trust state
  <- device list with is_trusted and is_active flags
```

## Revoke Device Sequence

```text
Authenticated Client
  -> validate device UUID
  -> Policy verifies device ownership
  -> prevent ambiguous current-device revocation outside logout flow
  -> mark device revoked
  -> revoke every descendant Session for device
  -> revoke all descendant access and refresh tokens through those Sessions
  -> emit DeviceRevoked
  -> AuditService: record device revocation
  <- success response
```

## Dependency Flow

```text
HTTP / Middleware
        -> Authentication contracts and DTOs
        -> Authentication services
              -> Repository contracts
              -> Sanctum adapter
              -> Spatie team-context adapter
              -> Redis lockout adapter
              -> Audit Platform contract
              -> Logging Platform contract
              -> Notification Platform contract
              -> Queue Platform contract
```

Forbidden dependencies:

```text
Authentication -X-> Patient, Doctor, Finance, or other business domains
Repository     -X-> business decisions
Controller     -X-> Eloquent or database
Business domain-X-> Sanctum, Spatie, or Redis concrete classes
```

## Lifecycle Cleanup Flow

```text
Authentication transaction
  -> apply minimum revocation / expiration transition
  -> append immutable Audit Event without Secret material
  -> record cleanup eligibility metadata
  -> commit normal business transaction

Background lifecycle processing
  -> evaluate Accepted retention policy
  -> evaluate Legal Hold and ownership scope
  -> archive non-Secret evidence when eligible
  -> purge or cryptographically destroy only when Hard Deletable
  -> preserve detached immutable evidence and referential integrity
```

Archive, retention enforcement, cleanup, purge, cryptographic destruction, and Legal Hold eligibility evaluation are asynchronous, idempotent, retry-safe, resumable, and bounded. They do not block normal Authentication transactions.
