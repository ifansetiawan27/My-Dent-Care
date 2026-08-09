# Authentication Architecture

## Purpose

Define a production-ready Authentication Platform for Dental ERP Enterprise using Laravel Sanctum, Spatie Permission, Repository Pattern, Service Pattern, SOLID, and DDD.

This document defines architecture only. It does not introduce Controllers, Models, Migrations, Routes, or executable implementations.

## Architectural Boundary

Authentication is a reusable Platform capability located at:

```text
app/Platform/Authentication/
```

It orchestrates identity verification, token lifecycle, account lockout, password lifecycle, device sessions, authorization context, and authentication audit events.

It consumes existing domain contracts for User, Organization, and Branch. It must not own or duplicate those domain entities.

## Folder Structure

```text
app/Platform/Authentication/
├── Contracts/
│   ├── AuthenticationServiceInterface.php
│   ├── AuthenticationRepositoryInterface.php
│   ├── TokenServiceInterface.php
│   ├── LockoutServiceInterface.php
│   ├── DeviceServiceInterface.php
│   └── PasswordServiceInterface.php
├── DTO/
│   ├── LoginDTO.php
│   ├── TokenPairDTO.php
│   ├── RefreshTokenDTO.php
│   ├── DeviceContextDTO.php
│   ├── ForgotPasswordDTO.php
│   ├── ResetPasswordDTO.php
│   └── ChangePasswordDTO.php
├── Enums/
│   ├── AuthenticationEvent.php
│   ├── DeviceType.php
│   └── TokenStatus.php
├── Exceptions/
│   ├── InvalidCredentialsException.php
│   ├── AccountLockedException.php
│   ├── InactiveTenantException.php
│   ├── InvalidRefreshTokenException.php
│   └── TokenReuseDetectedException.php
├── Repositories/
│   └── AuthenticationRepository.php
├── Services/
│   ├── AuthenticationService.php
│   ├── TokenService.php
│   ├── LockoutService.php
│   ├── DeviceService.php
│   └── PasswordService.php
├── Token/
│   ├── AccessTokenManager.php
│   ├── RefreshTokenManager.php
│   └── TokenHasher.php
├── Lockout/
│   ├── LoginAttemptKeyFactory.php
│   └── RedisLockoutStore.php
├── Devices/
│   └── DeviceFingerprint.php
├── Passwords/
│   ├── PasswordPolicy.php
│   └── LaravelPasswordBrokerAdapter.php
├── Events/
│   ├── LoginSucceeded.php
│   ├── LoginFailed.php
│   ├── UserLoggedOut.php
│   ├── PasswordChanged.php
│   └── DeviceRevoked.php
├── Listeners/
│   ├── RecordAuthenticationAudit.php
│   └── UpdateLastLogin.php
├── Jobs/
│   └── SendPasswordResetNotification.php
├── Support/
│   ├── AuthenticationContext.php
│   └── PermissionTeamContext.php
├── Config/
│   └── authentication.php
└── README.md
```

These names are architectural targets for later SDLC stages. Empty directories define boundaries only; classes are not generated at Stage 05.

## Responsibilities

### LoginDTO Contract

`LoginDTO` is an immutable application input produced after FormRequest validation.
The approved multi-tenant contract contains:

| Property | Type | Source | Required | Description |
|---|---|---|---:|---|
| `identifier` | string | request body | YES | Normalized username or email |
| `password` | string | request body | YES | Plain password over HTTPS only |
| `organizationId` | string | request body | YES | Active Organization UUID |
| `branchId` | string | request body | YES | Active Branch UUID belonging to Organization |
| `deviceUuid` | string | request body | YES | Stable client device identifier |
| `deviceName` | string|null | request body | NO | Human-readable device name |
| `deviceType` | DeviceType | request body | YES | web, mobile, tablet, or api |
| `platform` | string|null | request body | NO | Client operating system/platform |
| `rememberMe` | bool | request body | NO | Optional session-lifetime preference; default false |
| `ipAddress` | string|null | trusted server request context | YES at runtime | Never trusted from client payload |
| `userAgent` | string|null | trusted server request context | NO | Never trusted from client payload |

`fromArray()` may consume validated request data plus trusted server context. `toArray()` must not be used for logging because it contains a plaintext password. The password must never be serialized into audit or application logs.

### AuthenticationService

Orchestrates login, logout, refresh, and retrieval of the authenticated context. It owns authentication business workflow but delegates persistence and specialized concerns.

### AuthenticationRepository

Performs authentication-specific persistence and lookup only:

- Find User by normalized username or email.
- Read active Organization and Branch context through approved contracts.
- Persist and retrieve refresh-token, device, and login-history records.
- Revoke tokens by access token, refresh token, device, or user.

It contains no password validation, lockout decisions, or authorization rules.

### TokenService

Manages access and refresh token lifecycle:

- Issue Sanctum access tokens.
- Generate cryptographically secure opaque refresh tokens.
- Persist refresh-token hashes only.
- Rotate refresh tokens.
- Detect refresh-token reuse.
- Revoke current token, device tokens, or all user tokens.

### LockoutService

Uses Redis for distributed failed-attempt counters and temporary account locks. It applies the 5-attempt and 15-minute rules, with Super Admin exemption.

### DeviceService

Registers, updates, lists, and revokes recognized devices. Device revocation also revokes associated access and refresh tokens.

### PasswordService

Coordinates forgot-password, reset-password, and change-password workflows. Forgot/reset token creation, verification, and consumption are delegated to a Laravel Password Broker adapter in accordance with ADR-003. The service delegates notification delivery to the Notification Platform and hashes passwords using the approved Argon2id configuration.

### AuthenticationContext

Immutable request-scoped context containing:

- User UUID
- Organization UUID
- Branch UUID
- Device UUID
- Roles
- Permissions

It prevents tenant and permission context from being reconstructed inconsistently in each domain.

### PermissionTeamContext

Sets the Spatie team scope to the authenticated Organization before role or permission checks. Branch access remains enforced by Policy and tenant-scoped repositories.

## Class Diagram (Text)

```text
AuthenticationServiceInterface
        ^
        |
AuthenticationService
        |-- depends on --> AuthenticationRepositoryInterface
        |-- depends on --> TokenServiceInterface
        |-- depends on --> LockoutServiceInterface
        |-- depends on --> DeviceServiceInterface
        |-- depends on --> PasswordServiceInterface
        |-- depends on --> AuditServiceInterface
        |-- depends on --> LoggerServiceInterface
        `-- produces ----> AuthenticationContext

AuthenticationRepositoryInterface
        ^
        |
AuthenticationRepository
        `-- uses approved persistence models/contracts only

TokenServiceInterface
        ^
        |
TokenService
        |-- uses --> Laravel Sanctum
        |-- uses --> AccessTokenManager
        |-- uses --> RefreshTokenManager
        `-- uses --> TokenHasher

LockoutServiceInterface
        ^
        |
LockoutService
        `-- uses --> RedisLockoutStore

DeviceServiceInterface
        ^
        |
DeviceService
        |-- uses --> AuthenticationRepositoryInterface
        `-- uses --> DeviceFingerprint

PasswordServiceInterface
        ^
        |
PasswordService
        |-- uses --> Laravel Password Broker adapter
        |-- uses --> NotificationServiceInterface
        `-- emits --> PasswordChanged
```

## Request Flow

### Public Authentication Requests

```text
HTTP Request
  -> FormRequest validation
  -> Authentication Controller (thin adapter, later stage)
  -> DTO
  -> AuthenticationServiceInterface
  -> Repository and specialized Platform services
  -> Events and Audit Platform
  -> Resource / ApiResponse
```

### Protected Requests

```text
Bearer Access Token
  -> Sanctum middleware
  -> Resolve User
  -> Validate User status
  -> Resolve Organization and Branch context
  -> Validate Organization and Branch status
  -> Set Spatie team ID to Organization UUID
  -> Build AuthenticationContext
  -> Policy / permission middleware
  -> Domain endpoint
```

## Sequence Flow

### Login

```text
Client
  -> LoginRequest: validated credentials + tenant + device
  -> AuthenticationService: login(LoginDTO)
  -> LockoutService: assertNotLocked(identifier)
  -> AuthenticationRepository: findUser(identifier)
  -> Password Hasher: verify password
  -> AuthenticationRepository: resolve Organization and Branch
  -> AuthenticationService: validate active User/Organization/Branch and membership
  -> DeviceService: registerOrUpdate(device context)
  -> TokenService: issue access token + rotate/create refresh token
  -> LockoutService: clearFailedAttempts(identifier)
  -> Event: LoginSucceeded
  -> AuditService: record login
  -> AuthenticationRepository: update last login and login history
  <- TokenPairDTO + AuthenticationContext
```

### Failed Login

```text
Client
  -> AuthenticationService
  -> credential or tenant validation fails
  -> LockoutService: incrementFailedAttempts
  -> if attempts >= 5 and not Super Admin: lock for 15 minutes
  -> Event: LoginFailed
  -> AuditService: record failed login
  <- generic 401 or 423 response without account enumeration
```

### Refresh Token

```text
Client
  -> RefreshTokenRequest
  -> TokenService: hash submitted token
  -> AuthenticationRepository: find token by hash
  -> TokenService: validate expiry/revocation/device/tenant
  -> AuthenticationService: revalidate active User/Organization/Branch
  -> TokenService: revoke submitted token
  -> TokenService: issue new access + refresh pair
  -> link replacement token for rotation chain
  <- new TokenPairDTO
```

### Logout

```text
Authenticated Client
  -> AuthenticationService: logout(current access token)
  -> TokenService: revoke current access token only
  -> TokenService: revoke associated refresh token
  -> Event: UserLoggedOut
  -> AuditService: record logout
  <- success response
```

### Forgot and Reset Password

```text
Forgot Password:
Client -> PasswordService -> generic accepted response
                       -> if account exists: create hashed reset token
                       -> Queue SendPasswordResetNotification

Reset Password:
Client -> PasswordService -> validate token + password policy
                         -> update hashed password in transaction
                         -> revoke all access and refresh tokens
                         -> Event PasswordChanged
                         -> AuditService record
```

### Revoke Device

```text
Authenticated Client
  -> DeviceService: verify device ownership
  -> AuthenticationRepository: revoke device
  -> TokenService: revoke all device tokens
  -> Event: DeviceRevoked
  -> AuditService: record device revocation
  <- success response
```

## Dependency Flow

Dependencies point inward toward contracts and immutable DTOs:

```text
HTTP / Laravel Middleware
        |
        v
Authentication Contracts + DTOs
        |
        v
Authentication Services
        |
        +--> Repository Contracts --> Persistence adapters
        +--> Laravel Sanctum adapter
        +--> Spatie Permission adapter
        +--> Redis lockout adapter
        +--> Audit Platform contract
        +--> Logging Platform contract
        +--> Notification Platform contract
        `--> Queue Platform contract
```

Forbidden dependency directions:

```text
Authentication Platform -X-> Patient / Doctor / Finance / other business domains
Repository             -X-> Business rules
Controller             -X-> Eloquent / database
Domain                 -X-> Sanctum or Spatie concrete classes
```

Authentication may depend on User, Organization, and Branch only through approved interfaces/contracts. Other domains consume `AuthenticationServiceInterface` or the request-scoped `AuthenticationContext`.

## SOLID and DDD Decisions

- Single Responsibility: Authentication, Token, Lockout, Device, and Password services are separate.
- Open/Closed: token stores, notification providers, and persistence adapters can change behind interfaces.
- Liskov Substitution: every implementation must honor its contract and DTO shape.
- Interface Segregation: consumers depend only on the capability they need.
- Dependency Inversion: orchestration depends on interfaces, not Sanctum/Spatie/Redis concrete APIs.
- DDD Boundary: Authentication owns authentication workflows, not User profile or business-domain behavior.

## Production Constraints

1. Access, refresh, reset tokens, and passwords must never be logged.
2. All token and password operations use constant-time cryptographic comparison where applicable.
3. Refresh token rotation and reuse detection are mandatory.
4. Redis lockout keys must work across multiple application instances.
5. All authentication events are emitted and audited.
6. Generic errors prevent username/email enumeration.
7. Organization and Branch context is revalidated during login and refresh.
8. Spatie team context is set before authorization checks.
9. No synchronous notification delivery occurs in authentication requests.
10. Configuration values come from environment-backed config, never hardcoded credentials.
