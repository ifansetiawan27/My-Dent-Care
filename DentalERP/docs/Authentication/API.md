# Authentication API Contract

## Common Contract

Base path: `/api/v1/auth`

Protected endpoints require `Authorization: Bearer {access_token}`.

Standard response:

```json
{"success":true,"message":"Success.","data":{},"errors":null,"meta":null}
```

Tokens in examples are placeholders and must never be logged.

Lifecycle governance follows Accepted `DD-AUTH-007` and Accepted `ADR-005`; field classification, exposure, nullability, and field governance follow Accepted `DD-AUTH-017`. DD-AUTH-005 remains superseded historical evidence. This API does not expose retention, archive, Legal Hold, cleanup, or Secret persistence state.

All Session and Device operations in this contract are self-service. Super Admin uses the same ownership boundary; cross-user administration is excluded under Accepted `DD-AUTH-003` and requires a separate Security Administration contract.

Token TTL:

| Token | TTL |
|---|---|
| Access Token | 60 minutes |
| Refresh Token | 30 days |
| Password Reset Token | 15 minutes |

## Business Rule Traceability

| Method | Endpoint | Operation ID | Business Rules | Decision Authorities |
|---|---|---|---|---|
| POST | `/api/v1/auth/login` | `auth.login` | `AUTH-BR-001`, `AUTH-BR-003`, `AUTH-BR-013`, `AUTH-BR-014` | ADR-001, ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-007, DD-AUTH-008, DD-AUTH-017, ADR-005 |
| POST | `/api/v1/auth/logout` | `auth.logout` | `AUTH-BR-008` | ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-007, DD-AUTH-017, ADR-005 |
| POST | `/api/v1/auth/logout-all` | `auth.logoutAll` | `AUTH-BR-009` | ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-003, DD-AUTH-007, DD-AUTH-017, ADR-005 |
| POST | `/api/v1/auth/refresh` | `auth.refresh` | `AUTH-BR-002`, `AUTH-BR-013` | ADR-002, DD-AUTH-001, DD-AUTH-007, DD-AUTH-017, ADR-005 |
| POST | `/api/v1/auth/forgot-password` | `auth.forgotPassword` | `AUTH-BR-010` | ADR-003, ADR-006, DD-AUTH-007, DD-AUTH-017, ADR-005 |
| POST | `/api/v1/auth/reset-password` | `auth.resetPassword` | `AUTH-BR-011` | ADR-003, ADR-006, DD-AUTH-007, DD-AUTH-017, ADR-005 |
| POST | `/api/v1/auth/change-password` | `auth.changePassword` | `AUTH-BR-012`, `AUTH-BR-016` | ADR-006, DD-AUTH-004, DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, ADR-005 |
| GET | `/api/v1/auth/profile` | `auth.profile.show` | `AUTH-BR-004` | ADR-002, ADR-006 |
| PUT | `/api/v1/auth/profile` | `auth.profile.update` | `AUTH-BR-004` | ADR-006 |
| GET | `/api/v1/auth/login-history` | `auth.loginHistory.index` | `AUTH-BR-005` | ADR-006, DD-AUTH-007, DD-AUTH-010, DD-AUTH-017, ADR-005 |
| GET | `/api/v1/auth/devices` | `auth.devices.index` | `AUTH-BR-006`, `AUTH-BR-015` | ADR-002, ADR-006, DD-AUTH-003, DD-AUTH-006, DD-AUTH-007, DD-AUTH-017, ADR-005 |
| DELETE | `/api/v1/auth/devices/{deviceId}` | `auth.devices.destroy` | `AUTH-BR-007` | ADR-002, ADR-006, DD-AUTH-001, DD-AUTH-003, DD-AUTH-007, DD-AUTH-017, ADR-005 |

## POST `/api/v1/auth/login`

Authenticates a user in an active Organization and Branch, creates a User Session under the recognized Device, then issues an Access and Refresh Token pair linked to that Session.

Every successful authentication creates exactly one active User Session. One Device may own multiple Sessions. Each active Session owns exactly one active Sanctum Access Token and exactly one active Refresh Token family.

Access Token expires in 60 minutes. Refresh Token expires in 30 days.

Request:

```json
{
  "identifier":"doctor@example.com",
  "password":"StrongPassword123!",
  "organization_id":"01927f3e-0000-7000-8000-000000000001",
  "branch_id":"01927f3e-0000-7000-8000-000000000002",
  "device_uuid":"mobile-6f12b92d",
  "device_name":"Doctor iPhone",
  "device_type":"mobile",
  "platform":"ios"
}
```

`identifier`, `password`, `organization_id`, `branch_id`, `device_uuid`, and `device_type` are required. Device type is `web`, `mobile`, `tablet`, or `api`.

Login does not support `remember_me`. Access Token TTL remains 60 minutes, Refresh Token TTL remains 30 days, and Device trust is managed through a separate verified capability (`AUTH-BR-014`).

Response `200 OK`:

```json
{
  "success":true,
  "message":"Login successful.",
  "data":{
    "token_type":"Bearer",
    "access_token":"1|access-token-placeholder",
    "access_token_expires_at":"2026-08-02T14:46:00Z",
    "refresh_token":"refresh-token-placeholder",
    "refresh_token_expires_at":"2026-09-01T13:46:00Z",
    "device_id":"01927f3e-0000-7000-8000-000000000020",
    "user":{"id":"01927f3e-0000-7000-8000-000000000010","name":"Dr. Budi Santoso","username":"budi.santoso","email":"doctor@example.com","organization_id":"01927f3e-0000-7000-8000-000000000001","branch_id":"01927f3e-0000-7000-8000-000000000002","status":"active"},
    "roles":["doctor"],
    "permissions":["patient.view","medical_record.create"]
  },
  "errors":null,
  "meta":null
}
```

Statuses: `200` success, `401` invalid credentials, `403` inactive user/tenant or tenant mismatch, `422` validation, `423` locked, `429` throttled, `500` server error.

## POST `/api/v1/auth/logout`

Revokes the current User Session and every Access Token and Refresh Token linked to it.

Other User Sessions remain active. The registered Device remains available.

Request: no body; bearer token required.

Response `200 OK`:

```json
{"success":true,"message":"Logout successful.","data":null,"errors":null,"meta":null}
```

Statuses: `200`, `401`, `500`.

## POST `/api/v1/auth/logout-all`

Revokes every active User Session owned by the authenticated User and all descendant Access Tokens and Refresh Tokens. Devices remain registered. The revocation is audited.

Login History Operational History Projections and immutable Audit Events are retained; revocation does not delete either.

Business Rule: `AUTH-BR-009`.

Request: no body; bearer token is required.

Response `200 OK`:

```json
{"success":true,"message":"All sessions revoked successfully.","data":null,"errors":null,"meta":null}
```

Statuses: `200`, `401`, `500`.

## POST `/api/v1/auth/refresh`

Rotates a valid Refresh Token within its current User Session. The submitted token becomes invalid immediately and cannot be reused. Reuse of a rotated token revokes the entire Refresh Token family, its owning Session, and the descendant Access Token under Accepted DD-AUTH-007.

Rotation remains within the same Refresh Token family and User Session. The previous active Access Token and Refresh Token become invalid before the replacement token pair becomes active, preserving the one-active-token-pair invariant.

Request:

```json
{"refresh_token":"refresh-token-placeholder"}
```

Response `200 OK`:

```json
{
  "success":true,
  "message":"Token refreshed successfully.",
  "data":{
    "token_type":"Bearer",
    "access_token":"2|new-access-token-placeholder",
    "access_token_expires_at":"2026-08-02T15:46:00Z",
    "refresh_token":"new-refresh-token-placeholder",
    "refresh_token_expires_at":"2026-09-01T14:46:00Z"
  },
  "errors":null,
  "meta":null
}
```

Statuses: `200`, `401` invalid/expired/revoked, `403` inactive user/tenant/device, `409` token reuse, `422`, `500`.

Access Token expires in 60 minutes. Refresh Token expires in 30 days. Every successful refresh returns a new token pair.

## POST `/api/v1/auth/forgot-password`

Requests a password reset link. The response does not disclose whether the email exists.

Password Reset Token expires in 15 minutes and is single-use.

Request:

```json
{"email":"doctor@example.com"}
```

Response `202 Accepted`:

```json
{"success":true,"message":"If the email is registered, password reset instructions will be sent.","data":null,"errors":null,"meta":null}
```

Statuses: `202`, `422`, `429`, `500`.

Forgot Password Token is valid for 15 minutes.

## POST `/api/v1/auth/reset-password`

Resets the password using a Password Reset Token and revokes every active User Session and all descendant Access Tokens and Refresh Token families. Registered Devices remain available. The token is valid for 15 minutes and can be used only once.

Request:

```json
{
  "email":"doctor@example.com",
  "token":"reset-token-placeholder",
  "password":"NewStrongPassword456!",
  "password_confirmation":"NewStrongPassword456!"
}
```

Response `200 OK`:

```json
{"success":true,"message":"Password reset successfully. Please log in again.","data":null,"errors":null,"meta":null}
```

Statuses: `200`, `400` invalid/expired/used token, `422`, `429`, `500`.

## POST `/api/v1/auth/change-password`

Changes the authenticated user's password after validating the current password.

After a successful password change:

- Current authenticated Session remains active.
- Current Access Token and Refresh Token family remain active.
- All other active Sessions are revoked.
- Descendant Access Tokens and Refresh Token families of revoked Sessions are revoked.
- Registered Devices remain associated with the User.
- Immutable `PASSWORD_CHANGED` audit event is created; Login History is not modified.
- Accepted DD-AUTH-018 makes credential-change revocation an explicit exception to the generic `logout_at` projection trigger.

Request:

```json
{
  "current_password":"StrongPassword123!",
  "password":"NewStrongPassword456!",
  "password_confirmation":"NewStrongPassword456!"
}
```

Response `200 OK`:

```json
{
  "success":true,
  "message":"Password changed successfully.",
  "data":{
    "current_session_active":true,
    "other_sessions_revoked":true,
    "registered_devices_retained":true
  },
  "errors":null,
  "meta":null
}
```

Statuses: `200`, `401`, `422` incorrect/reused/invalid password, `500`.

## GET `/api/v1/auth/profile`

Returns the authenticated user's profile, tenant context, roles, and permissions.

Request: no body; bearer token required.

Response `200 OK`:

```json
{
  "success":true,
  "message":"Profile retrieved successfully.",
  "data":{
    "id":"01927f3e-0000-7000-8000-000000000010",
    "employee_code":"EMP-0001",
    "name":"Dr. Budi Santoso",
    "username":"budi.santoso",
    "email":"doctor@example.com",
    "phone":"+62-812-0000-0001",
    "photo":null,
    "gender":"male",
    "birth_date":"1985-06-15",
    "organization_id":"01927f3e-0000-7000-8000-000000000001",
    "branch_id":"01927f3e-0000-7000-8000-000000000002",
    "status":"active",
    "organization":{"id":"01927f3e-0000-7000-8000-000000000001","company_code":"ORG-0001","company_name":"My Dent Care"},
    "branch":{"id":"01927f3e-0000-7000-8000-000000000002","branch_code":"BRC-0001","branch_name":"Sudirman Clinic"},
    "roles":["doctor"],
    "permissions":["patient.view","medical_record.create"]
  },
  "errors":null,
  "meta":null
}
```

Statuses: `200`, `401`, `403`, `500`.

## PUT `/api/v1/auth/profile`

Updates only Name, Phone, and Photo. Username, Employee Code, Email, Status, Organization, Branch, roles, permissions, gender, and birth date cannot be changed.

Content type: `multipart/form-data`

| Field | Type | Required | Description |
|---|---|---:|---|
| `name` | string | YES | Full name, maximum 200 characters |
| `phone` | string or null | NO | Phone number, maximum 30 characters |
| `photo` | binary file | NO | New profile photo |

Request:

```http
Content-Type: multipart/form-data

name=Dr. Budi Santoso, Sp.Ort
phone=+62-812-1111-2222
photo=@profile.jpg
```

Response `200 OK`:

```json
{
  "success":true,
  "message":"Profile updated successfully.",
  "data":{"id":"01927f3e-0000-7000-8000-000000000010","employee_code":"EMP-0001","name":"Dr. Budi Santoso, Sp.Ort","username":"budi.santoso","email":"doctor@example.com","phone":"+62-812-1111-2222","photo":"https://files.example.test/signed/profile-photo","gender":"male","birth_date":"1985-06-15","organization_id":"01927f3e-0000-7000-8000-000000000001","branch_id":"01927f3e-0000-7000-8000-000000000002","status":"active","organization":{"id":"01927f3e-0000-7000-8000-000000000001","company_code":"ORG-0001","company_name":"My Dent Care"},"branch":{"id":"01927f3e-0000-7000-8000-000000000002","branch_code":"BRC-0001","branch_name":"Sudirman Clinic"},"roles":["doctor"],"permissions":["patient.view","medical_record.create"]},
  "errors":null,
  "meta":null
}
```

Statuses: `200`, `401`, `403`, `422`, `500`.

## GET `/api/v1/auth/login-history`

Returns paginated authentication history for the current user only.

Default ordering is `login_at DESC, id DESC` under Accepted DD-AUTH-010. Pagination remains page-based and the tie-breaker does not change the response shape.

Field classification, exposure, and nullability follow Accepted `DD-AUTH-017`.

Login History is an Operational History Projection, not canonical audit evidence. `logout_at` follows the field policy in Accepted DD-AUTH-017 and lifecycle authority in Accepted DD-AUTH-007; canonical Audit Events remain append-only and immutable.

All documented response properties remain present. A nullable property is returned
with a `null` value when enrichment data is unavailable or its lifecycle event has
not occurred.

### Response Fields

| Field | Presence | Value Nullability | Description |
|---|---|---|---|
| `id` | Required | NOT NULL | Login History UUID |
| `login_at` | Required | NOT NULL | Authentication attempt/login timestamp |
| `logout_at` | Required | Nullable | `null` while the Session is active; set when the Session ends |
| `ip_address` | Required | Nullable | Client IP when available |
| `browser` | Required | Nullable | Best-effort browser enrichment |
| `operating_system` | Required | Nullable | Best-effort operating-system enrichment |
| `device_name` | Required | Nullable | Client-provided Device name when available |
| `country` | Required | Nullable | Best-effort geolocation country |
| `city` | Required | Nullable | Best-effort geolocation city |
| `login_status` | Required | NOT NULL | `success` or `failed` |
| `failure_reason` | Required | Nullable | `null` for successful authentication |

Query parameters: `page` (default 1), `per_page` (default 15, maximum 100), `login_status` (`success`, `failed`), `from`, `to`.

Request: no body; bearer token required.

Request example:

```http
GET /api/v1/auth/login-history?login_status=success&per_page=15
```

Response `200 OK`:

```json
{
  "success":true,
  "message":"Login history retrieved successfully.",
  "data":[{"id":"01927f3e-0000-7000-8000-000000000030","login_at":"2026-08-02T13:46:00Z","logout_at":null,"ip_address":"203.0.113.10","browser":null,"operating_system":null,"device_name":"Doctor iPhone","country":null,"city":null,"login_status":"success","failure_reason":null}],
  "errors":null,
  "meta":{"pagination":{"total":24,"per_page":15,"current_page":1,"last_page":2,"from":1,"to":15}}
}
```

Statuses: `200`, `401`, `422`, `500`.

## GET `/api/v1/auth/devices`

Returns recognized Devices owned by the authenticated User.

Each Device represents an independent hardware/browser identity and may own multiple User Sessions. Device lifecycle is independent from Session lifecycle. Session revocation is performed through Logout, Logout All, or Device Revocation.

Field classification, exposure, and nullability follow Accepted `DD-AUTH-017`.

All documented response properties remain present. Nullable enrichment and lifecycle
properties are returned as `null` when unavailable or not generated yet.

### Response Fields

| Field | Presence | Value Nullability | Description |
|---|---|---|---|
| `id` | Required | NOT NULL | Device UUID |
| `device_uuid` | Required | NOT NULL | Stable client Device identifier |
| `device_name` | Required | Nullable | Client-provided Device name |
| `platform` | Required | Nullable | Detected client platform |
| `browser` | Required | Nullable | Best-effort browser enrichment |
| `operating_system` | Required | Nullable | Best-effort operating-system enrichment |
| `last_login_at` | Required | Nullable | `null` until first successful Device login |
| `last_activity_at` | Required | Nullable | `null` until first tracked authenticated activity |
| `is_trusted` | Required | NOT NULL | Persisted trusted-Device state |
| `is_active` | Required | NOT NULL | Derived from `revoked_at IS NULL` |

Query parameters:

| Parameter | Type | Required | Default | Constraint |
|---|---|---:|---:|---|
| `page` | integer | NO | 1 | Minimum 1 |
| `per_page` | integer | NO | 20 | Minimum 1, maximum 100 |
| `sort` | string | NO | `last_activity_at` | Allowed: `last_activity_at`, `created_at`, `device_name` |
| `direction` | string | NO | `desc` | Allowed: `asc`, `desc` |
| `platform` | string | NO | — | Exact platform filter |
| `trusted` | boolean | NO | — | Maps to `is_trusted` |
| `active` | boolean | NO | — | Filters active Device state |
| `revoked` | boolean | NO | — | Filters revoked Device state; cannot conflict with `active` |

Default sort: `last_activity_at DESC, id DESC`.

Unknown sort fields, directions, or filters return `422 Unprocessable Entity`.

Request example:

```http
GET /api/v1/auth/devices?page=1&per_page=20&sort=last_activity_at&direction=desc&platform=ios&trusted=true&active=true
```

Request: no body; bearer token required.

Response `200 OK`:

```json
{
  "success":true,
  "message":"Devices retrieved successfully.",
  "data":[{"id":"01927f3e-0000-7000-8000-000000000020","device_uuid":"mobile-6f12b92d","device_name":null,"platform":null,"browser":null,"operating_system":null,"last_login_at":"2026-08-02T13:46:00Z","last_activity_at":null,"is_trusted":false,"is_active":true}],
  "errors":null,
  "meta":{"pagination":{"total":3,"per_page":20,"current_page":1,"last_page":1,"from":1,"to":3}}
}
```

Statuses: `200`, `401`, `422`, `500`.

## DELETE `/api/v1/auth/devices/{deviceId}`

Revokes a recognized Device, every User Session under it, and all descendant Access/Refresh Tokens. A User cannot revoke another User's Device.

Revoking a Device changes the Device lifecycle state; it does not delete Login History Operational History Projections or immutable Audit Events.

Path parameter: `deviceId` is the device UUID.

Request: no body; bearer token required.

Request example:

```http
DELETE /api/v1/auth/devices/01927f3e-0000-7000-8000-000000000020
```

Response `200 OK`:

```json
{"success":true,"message":"Device revoked successfully.","data":null,"errors":null,"meta":null}
```

Statuses: `200`, `401`, `403` device ownership mismatch, `404` device not found, `409` current-device revocation requires logout flow, `500`.

## Common Error Examples

Validation error `422`:

```json
{"success":false,"message":"The given data was invalid.","data":null,"errors":{"email":["Email must be valid."]},"meta":null}
```

Locked account `423`:

```json
{"success":false,"message":"Account is temporarily locked. Try again in 15 minutes.","data":null,"errors":null,"meta":{"retry_after":900}}
```

Server error `500`:

```json
{"success":false,"message":"Internal server error.","data":null,"errors":null,"meta":null}
```
