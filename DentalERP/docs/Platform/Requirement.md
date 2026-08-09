# Phase 07 — Platform Services Requirements

**Date:** 2026-08-09
**Phase:** 07 — Platform Services
**SDLC Stage:** 01 — Requirement
**Status:** `STEP_07_02_PLATFORM_SERVICES_REQUIREMENTS_DRAFT`

---

## 1. Objective and Scope

### 1.1 Objective

Provide centralized, reusable Platform services that every domain module in the Dental ERP Enterprise consumes through typed interfaces. Platform Services encapsulate cross-cutting infrastructure (audit, file storage, logging, notification) so that no domain implements its own audit logic, file handling, logger, or notification dispatch.

### 1.2 Actors / Callers

| Actor | Role |
|---|---|
| **Domain Service** | Any domain Service (Authentication, Patient, Appointment, Finance, etc.) — the primary consumer of Platform interfaces |
| **Platform Service** | A Platform Service may call another Platform Service (e.g., FileStorage → Audit, Notification → Audit, Notification → Logging) |
| **System (Queue)** | Laravel Queue workers process async jobs dispatched by Platform Services |
| **Administrator** | Views audit logs, system logs, notification status; resends failed notifications |

### 1.3 Four Service Areas

| ID Prefix | Service | Primary Data Category (ADR-005) |
|---|---|---|
| `PLATFORM-REQ-AUD` | Audit Platform | Immutable Audit Event |
| `PLATFORM-REQ-FS` | FileStorage Platform | Business Record |
| `PLATFORM-REQ-LOG` | Logging Platform | Technical Log |
| `PLATFORM-REQ-NOT` | Notification Platform | Business Record |

### 1.4 Explicit Exclusions

The following are out of scope for Phase 07 Platform Services:
- IntegrationHub, PaymentGateway, Queue, Webhook implementations (contracts exist; implementation deferred)
- Authorization, Cache, Events, Mail, Reporting, Search, Settings, Sms (empty stubs; no contracts)
- HTTP-facing endpoints — Platform Services are internal only, consumed via PHP interfaces
- Authentication domain modification — Authentication Design Freeze is ACTIVE
- Cross-organization data access — each service enforces tenant isolation

---

## 2. Audit Platform Requirements

### PLATFORM-REQ-AUD-001 — Immutable Audit Recording

**Statement:** Every write operation (create, update, delete, restore) across any domain MUST produce an immutable audit record.

**Actor / Caller Scope:** Any Domain Service via `AuditServiceInterface::record()` or `AuditServiceInterface::log()`.

**Functional Behavior:**
- Accepts a fully-formed `AuditEntryDTO` (via `record()`) or action-scoped parameters (via `log()`)
- Dispatches the record asynchronously to a Queue job (`AuditLogJob`)
- The Queue job persists to the `audit_logs` table
- No synchronous database write — the caller's request must not block on audit persistence

**Input Boundary:** `AuditEntryDTO` with 12 fields: `action` (AuditAction enum), `module` (string), `userId`, `organizationId`, `branchId`, `auditableType`, `auditableId`, `oldValue` (array), `newValue` (array), `ipAddress`, `userAgent`, `device`.

**Output Boundary:** `void` — no return value. Audit is fire-and-forget from the caller's perspective.

**Security Boundary:**
- `oldValue` and `newValue` MUST NOT contain passwords, tokens, or secret-classified fields
- Sensitive fields (email, phone) may be included but MUST be classified and redacted at the access layer
- Audit records are never exposed through public API — consumption is via authorized admin interface only

**Persistence / Storage:**
- Table: `audit_logs` (PostgreSQL)
- 14 columns: `id` (uuid PK), `user_id`, `organization_id`, `branch_id`, `module`, `action`, `auditable_type`, `auditable_id`, `old_value` (jsonb), `new_value` (jsonb), `ip_address`, `user_agent`, `device`, `created_at` (timestamptz)
- NO `updated_at` — records are immutable
- Candidate for monthly partitioning on `created_at` in production

**Failure Behavior:**
- If Queue dispatch fails, the audit is lost — but the Queue failure itself must be logged
- No caller exception — audit failures must not propagate to domain transaction boundaries

**Availability / Reliability:**
- Fire-and-forget with Queue — best-effort delivery
- Queue retry with dead-letter handling for permanent failures

**Tenant / Context Boundary:**
- Every record carries `organization_id` and `branch_id`
- Access control scoped to organization

**Auditability:**
- Audit Platform is the canonical audit system — it does NOT audit itself (prevents infinite recursion)
- All other domain operations are auditable through this platform

**Secret / Data Protection:**
- `old_value` and `new_value` fields exclude Secret-classified values
- `ip_address` is Sensitive — accessible only by authorized admin

**Acceptance Criteria:**
1. Domain Service can call `AuditServiceInterface::record()` without blocking
2. Audit record appears in `audit_logs` table after async Queue processing
3. `old_value` is null for `create` events; `new_value` is null for `delete` events
4. Login/logout events omit `auditable_type`/`auditable_id`
5. No password, token, or credit card data appears in `old_value`/`new_value`
6. Immutable — records cannot be updated or deleted through normal operations

**Exclusions:**
- Archive/purge lifecycle (retention policy — deferred)
- Admin UI for audit viewing (deferred to Phase 25 Reporting)
- Audit Platform does not audit its own operations

---

### PLATFORM-REQ-AUD-002 — Audit Action Catalog

**Statement:** The Audit Platform MUST classify every recorded activity using a canonical `AuditAction` enum.

**Actor / Caller Scope:** Audit Platform internal — enforced by `AuditAction` enum type.

**Functional Behavior:**
- 11 action types: `login`, `logout`, `create`, `update`, `delete`, `restore`, `export`, `import`, `print`, `sync`, `integration`
- `isMutation()` returns true for create/update/delete/restore (actions that carry old/new value diffs)
- `label()` returns human-readable name

**Acceptance Criteria:**
1. All 11 action types are defined as `AuditAction` enum cases
2. `AuditAction::values()` returns all 11 string values
3. `AuditAction::isMutation()` correctly classifies mutation vs non-mutation actions

---

### PLATFORM-REQ-AUD-003 — Interface-Driven Consumption

**Statement:** Domains MUST depend only on `AuditServiceInterface` — never on a concrete implementation, database table, or Queue job directly.

**Actor / Caller Scope:** Domain Services.

**Functional Behavior:** Domains receive `AuditServiceInterface` via constructor injection (Dependency Inversion). The concrete implementation is resolved by the service container.

**Acceptance Criteria:**
1. No domain Service imports `AuditService` concrete class
2. No domain Model or Repository writes to `audit_logs` directly
3. Binding registered in Platform ServiceProvider

---

## 3. FileStorage Platform Requirements

### PLATFORM-REQ-FS-001 — UUID-Based File Storage

**Statement:** Every stored file MUST be assigned a UUID as its physical filename. The user's original filename MUST NOT be used as the stored filename.

**Actor / Caller Scope:** Any Domain Service via `FileStorageServiceInterface::store()`.

**Functional Behavior:**
- Accepts an `UploadedFile`, `StorageFolder`, `organizationId`, `branchId`
- Steps: (1) validate MIME + extension against folder whitelist, (2) validate size against folder limit, (3) generate UUID filename, (4) build multi-tenant path `{folder}/{organization_id}/{branch_id}/{yyyy}/{mm}/{uuid}.{ext}`, (5) compute SHA-256 hash, (6) store to active disk, (7) save metadata to `files` table
- Returns `StoredFileDTO` with complete metadata

**Input Boundary:** `FileStorageServiceInterface::store(UploadedFile $file, StorageFolder $folder, ?string $organizationId, ?string $branchId): StoredFileDTO`

**Output Boundary:** `StoredFileDTO` with 12 fields: `id` (UUID), `folder`, `disk`, `path`, `originalName`, `storedName`, `mimeType`, `extension`, `size`, `hash`, `organizationId`, `branchId`.

**Security Boundary:**
- MIME type validated against whitelist per folder — reject executable, script
- Size validated per folder — reject oversized files
- File naming uses UUID — prevents path traversal and enumeration
- Files must not be directly accessible via public URL
- Signed URLs have configurable expiry (default 15 minutes)

**Persistence / Storage:**
- Table: `files` (PostgreSQL)
- 18 columns: `id` (uuid PK, same as filename), `organization_id`, `branch_id` (nullable), `fileable_type`, `fileable_id`, `folder`, `disk`, `path`, `original_name`, `stored_name`, `mime_type`, `extension`, `size`, `hash`, `created_by`, `created_at`, `updated_at`, `deleted_at`
- Polymorphic ownership via `fileable_type` / `fileable_id`
- Soft delete via `deleted_at`
- Physical disk: Local (dev) or S3-compatible (production)

**Failure Behavior:**
- MIME/size validation failure → `BusinessException` with specific reason
- Disk write failure → `BusinessException`, record not created
- Hash computation failure → must fail the entire store operation

**Tenant / Context Boundary:**
- Path always scoped to `organization_id` and `branch_id`
- `files` table scoped by `organization_id`

**Auditability:**
- Upload and delete operations MUST be recorded in the Audit Platform
- `created_by` field captures the uploading user

**Secret / Data Protection:**
- Sensitive documents should use S3 SSE or disk encryption at rest
- File access only via signed URL with expiry or permission-based streaming

**Acceptance Criteria:**
1. Uploaded file receives a UUID filename, not the original name
2. Original filename preserved only as metadata (`original_name`)
3. MIME type outside whitelist is rejected with `BusinessException`
4. Size exceeding folder limit is rejected with `BusinessException`
5. SHA-256 hash is computed and stored
6. Path follows `{folder}/{org_id}/{branch_id}/{yyyy}/{mm}/{uuid}.{ext}` convention
7. `StoredFileDTO` returned with all 12 fields populated
8. Upload audit event recorded via Audit Platform

**Exclusions:**
- Thumbnail generation (deferred — Queue-based)
- Deduplication (optional — deferred)
- DICOM PACS integration (deferred to IntegrationHub)

---

### PLATFORM-REQ-FS-002 — MIME and Size Validation per Folder

**Statement:** Every upload MUST be validated against per-folder MIME type whitelist and size limits defined in the `StorageFolder` enum.

| Folder | Allowed Types | Max Size |
|---|---|---|
| `patient` | jpg, jpeg, png, pdf | 10 MB |
| `doctor` | jpg, jpeg, png, pdf | 10 MB |
| `organization` | jpg, jpeg, png, svg, pdf | 5 MB |
| `branch` | jpg, jpeg, png | 5 MB |
| `lab` | pdf, jpg, jpeg, png | 20 MB |
| `radiology` | dcm, jpg, jpeg, png, pdf | 100 MB |
| `asset` | jpg, jpeg, png, pdf | 10 MB |

**Acceptance Criteria:**
1. MIME validation enforced before disk write
2. Size validation enforced before disk write
3. Validation is performed server-side — client validation is not trusted
4. `StorageFolder::allowedExtensions()` and `StorageFolder::maxSizeBytes()` are the authoritative source

---

### PLATFORM-REQ-FS-003 — Driver-Agnostic Storage

**Statement:** Domain consumers MUST be unaware of whether the active storage driver is Local or S3-compatible.

**Functional Behavior:**
- Active driver resolved from Laravel filesystem configuration
- `StorageDriver` enum: `local`, `s3`
- Domains call the same `FileStorageServiceInterface::store()` regardless of driver

**Acceptance Criteria:**
1. Switching `FILESYSTEM_DISK` between `local` and `s3` requires no domain code change
2. No domain resolves or references `StorageDriver`

---

### PLATFORM-REQ-FS-004 — Secure File Access

**Statement:** All file access MUST be through signed URLs or permission-based streaming. Public direct access to medical files is forbidden.

**Functional Behavior:**
- `FileStorageServiceInterface::temporaryUrl(string $path, int $expiresIn = 900): string` — generates a signed URL
- Default expiry: 15 minutes (900 seconds)
- For S3: uses S3 presigned URLs
- For Local: uses Laravel signed route URLs

**Acceptance Criteria:**
1. `temporaryUrl()` returns a time-limited signed URL
2. Expired URLs return 403/404
3. No permanent public URL to any file is exposed

---

### PLATFORM-REQ-FS-005 — Interface-Driven Consumption

**Statement:** Domains MUST depend only on `FileStorageServiceInterface` — never on `Storage::put()`, S3 SDK, or disk operations directly.

**Acceptance Criteria:**
1. No domain Service calls `Storage::put()` or `Storage::disk()`
2. No domain resolves `StorageDriver`
3. Binding registered in Platform ServiceProvider

---

## 4. Logging Platform Requirements

### PLATFORM-REQ-LOG-001 — Structured Logging

**Statement:** Every domain and Platform service MUST write structured logs through `LoggerServiceInterface`, not through Laravel's `Log` facade directly.

**Actor / Caller Scope:** Any Domain Service, any Platform Service.

**Functional Behavior:**
- 8 PSR-3 / RFC 5424 log levels: `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`
- Each log entry carries: `level`, `message` (with `[Module::action]` convention), `context` (array)
- Message format: `[ServiceName::action] descriptive message`
- Request correlation: every log during a request cycle carries `request_id`

**Input Boundary:** `LoggerServiceInterface::log(LogLevel $level, string $message, array $context = []): void` plus 8 convenience methods: `emergency()`, `alert()`, `critical()`, `error()`, `warning()`, `notice()`, `info()`, `debug()`.

**Security Boundary:**
- Passwords, tokens, credit card numbers MUST NOT appear in log messages or context
- Sensitive fields may appear after redaction/minimization
- `debug` level ONLY active in non-production environments

**Persistence / Storage:**
- Table: `system_logs` (PostgreSQL) — for levels ≥ warning
- 14 columns: `id` (uuid PK), `level`, `message` (text), `context` (jsonb), `channel`, `user_id`, `organization_id`, `branch_id`, `exception_class`, `file`, `line`, `trace` (text), `ip_address`, `created_at` (timestamptz)
- Daily log file: `storage/logs/laravel-YYYY-MM-DD.log` — all levels

**Routing per Level:**

| Level Range | Daily File | Database | External |
|---|---|---|---|
| `debug`, `info`, `notice` | ✅ | — | — |
| `warning` | ✅ | ✅ | — |
| `error` and above | ✅ | ✅ | ✅ |

**Failure Behavior:**
- Database log write failure must not propagate to the caller
- File log write failure is exceptional — must fail visibly

**Availability / Reliability:**
- Database and external writes dispatched via Queue (non-blocking)
- File writes are synchronous (acceptable — local filesystem)

**Tenant / Context Boundary:**
- `organization_id`, `branch_id`, `user_id` populated when available from request context
- Nullable when no authenticated context exists

**Secret / Data Protection:**
- Sensitive data excluded by caller before passing to LoggerServiceInterface
- Logging Platform does not scan or redact — exclusion is caller's responsibility

**Acceptance Criteria:**
1. `LoggerServiceInterface::error('[AuthService::login] Invalid credentials.', [...])` produces a log entry at ERROR level
2. ERROR-level logs appear in daily file, database `system_logs`, and external monitoring (if configured)
3. DEBUG-level logs appear ONLY in daily file, never in database
4. DEBUG-level logs are suppressed in production environment
5. `request_id` is included in every log entry during a request
6. Database writes do not block the caller (Queue-dispatched)

**Exclusions:**
- External monitoring provider integration (Sentry/Datadog/ELK) — deferred, configured via environment
- Log archival/purge (default 90 days database, 14 days file)

---

### PLATFORM-REQ-LOG-002 — Level-Based Routing

**Statement:** The destination of a log entry MUST be determined automatically by its severity level per the routing table.

**Functional Behavior:**
- `LogLevel::shouldPersist() → bool`: returns true for warning and above
- `LogLevel::shouldForwardExternal() → bool`: returns true for error and above
- Routing logic encapsulated in concrete `LoggerService` implementation

**Acceptance Criteria:**
1. Callers never specify a destination — routing is automatic
2. Adding a new log destination does not change the interface

---

### PLATFORM-REQ-LOG-003 — Interface-Driven Consumption

**Statement:** Domains MUST depend only on `LoggerServiceInterface` — never on Laravel's `Log` facade directly.

**Acceptance Criteria:**
1. No domain Service calls `Log::info()`, `Log::error()`, or any `Log` facade method
2. Binding registered in Platform ServiceProvider
3. Existing domain code using `Log` facade (noted in Authentication `AuthService`) is a pre-existing cosmetic concern, not a Phase 07 blocker

---

## 5. Notification Platform Requirements

### PLATFORM-REQ-NOT-001 — Queue-Based Notification Dispatch

**Statement:** ALL notifications MUST be dispatched through Laravel Queue. Synchronous dispatch is forbidden.

**Actor / Caller Scope:** Any Domain Service via `NotificationServiceInterface::send()` or `sendMany()`.

**Functional Behavior:**
- Domain calls `send(NotificationMessageDTO)` or `sendMany(array of NotificationMessageDTO)`
- Implementation creates `notifications` table record(s) with status `pending`
- Dispatches `SendNotificationJob` to Queue
- Queue worker resolves the correct `NotificationChannelInterface` driver per channel
- Each driver delivers to its provider (SMTP, WhatsApp API, SMS gateway, FCM, database)
- Status updated: `pending` → `sent` or `failed`

**Input Boundary:** `NotificationMessageDTO` with 10 fields: `type`, `notifiableType`, `notifiableId`, `channels` (array of `NotificationChannel`), `title`, `body`, `organizationId`, `branchId`, `data` (array), `locale`.

**Output Boundary:** `void` — fire-and-forget from the caller's perspective.

**Security Boundary:**
- Sensitive data must not appear in plaintext in `body` or `data` columns
- Channel provider credentials are per-organization and never exposed to domains
- WhatsApp channel routes through IntegrationHub for provider connection

**Persistence / Storage:**
- Table: `notifications` (PostgreSQL)
- 16 columns: `id` (uuid PK), `organization_id`, `branch_id` (nullable), `notifiable_type`, `notifiable_id`, `channel`, `type`, `title`, `body`, `data` (jsonb), `status`, `sent_at`, `read_at`, `failed_reason`, `created_at`, `updated_at`

**Status Lifecycle:**

| From | To | Trigger |
|---|---|---|
| — | `pending` | Created by `send()` |
| `pending` | `sent` | Queue worker successful delivery |
| `pending` | `failed` | Delivery failed, retries exhausted |
| `sent` | `read` | User reads in-app notification |

**Failure Behavior:**
- Failed notification must be retried: default 3 attempts with exponential backoff
- After retries exhausted, status set to `failed`, reason recorded in `failed_reason`
- WhatsApp/SMS to invalid numbers: mark `failed`, do not retry indefinitely
- Channel not configured for organization: skip gracefully, do not fail

**Availability / Reliability:**
- Non-blocking via Queue
- Retry mechanism ensures transient failures are recovered
- Dead-letter tracking for permanent failures

**Tenant / Context Boundary:**
- `organization_id` required for every notification
- Channel availability checked per organization (`NotificationChannelInterface::isAvailableFor()`)
- Channel provider credentials configured per organization

**Auditability:**
- Notification dispatch (send, success, failure) recorded in Audit Platform
- Notification failures logged in Logging Platform

**Secret / Data Protection:**
- Provider credentials stored per organization, not in notification records
- `body` and `data` must not contain plaintext sensitive data

**Acceptance Criteria:**
1. `NotificationServiceInterface::send()` creates a `pending` notification record and dispatches to Queue
2. Queue worker delivers to all requested channels via channel drivers
3. Successful delivery updates status to `sent`
4. Failed delivery after 3 retries updates status to `failed` with `failed_reason`
5. Non-configured channel is skipped gracefully
6. Invalid WhatsApp/SMS number produces `failed` after limited retries

**Exclusions:**
- In-App realtime broadcasting (WebSocket/Pusher) — deferred
- Multi-language templates — deferred
- Admin manual resend UI — deferred
- Channel provider credentials management — deferred

---

### PLATFORM-REQ-NOT-002 — Multi-Channel Delivery

**Statement:** A single notification MUST support delivery to multiple channels simultaneously.

**Functional Behavior:**
- 5 channels: `email`, `whatsapp`, `sms`, `push`, `in_app`
- Each channel implemented as a separate `NotificationChannelInterface` driver
- Channel routing: WhatsApp, SMS, Push → IntegrationHub; Email → SMTP; In-App → database

**Channel Drivers Required:**

| Channel | Driver Class | Provider |
|---|---|---|
| Email | `EmailChannel` | SMTP / Mailgun / SES |
| WhatsApp | `WhatsAppChannel` | WhatsApp Business API (via IntegrationHub) |
| SMS | `SmsChannel` | Twilio / Vonage (via IntegrationHub) |
| Push | `PushChannel` | Firebase FCM (via IntegrationHub) |
| In-App | `InAppChannel` | Database + Realtime |

**Acceptance Criteria:**
1. Sending to `['whatsapp', 'sms', 'push']` delivers to all three channels via the queue job
2. Each channel driver implements `NotificationChannelInterface`
3. Adding a new channel requires only a new driver — no existing code is modified

---

### PLATFORM-REQ-NOT-003 — In-App Notification Read Status

**Statement:** In-App notifications MUST support a read/unread lifecycle with `read_at` timestamp.

**Functional Behavior:**
- `NotificationServiceInterface::markAsRead(string $notificationId): bool`
- Sets `read_at` to current timestamp
- Only applicable to `in_app` channel notifications

**Acceptance Criteria:**
1. `markAsRead()` sets `read_at` and returns `true` for existing pending/sent in-app notification
2. `markAsRead()` returns `false` for non-existent notification

---

### PLATFORM-REQ-NOT-004 — Interface-Driven Consumption

**Statement:** Domains MUST depend only on `NotificationServiceInterface` — never on SMTP, Twilio, FCM, or any provider SDK directly.

**Acceptance Criteria:**
1. No domain Service calls any provider SDK
2. No domain resolves `NotificationChannel`
3. Binding registered in Platform ServiceProvider

---

## 6. Cross-Cutting Requirements

### PLATFORM-REQ-X-001 — Service Container Binding

**Statement:** Every Platform Service interface MUST be bound to its concrete implementation in a Platform ServiceProvider registered in the application.

**Applies to:** All four services.

**Acceptance Criteria:**
1. `AuditServiceInterface → AuditService` binding
2. `FileStorageServiceInterface → FileStorageService` binding
3. `LoggerServiceInterface → LoggerService` binding
4. `NotificationServiceInterface → NotificationService` binding
5. Each `NotificationChannelInterface` bound to its channel driver

---

### PLATFORM-REQ-X-002 — Laravel Queue Dependency

**Statement:** All four Platform Services depend on Laravel Queue (Redis-backed) for async, non-blocking processing.

**Applies to:**
- Audit: `AuditLogJob` (record persistence)
- Logging: database + external log writes
- Notification: `SendNotificationJob` (all channel delivery)

**Acceptance Criteria:**
1. Queue connection configured (Redis default from Phase 01)
2. Queue worker process must be running for async job processing
3. Dead-letter / failed-job handling configured

---

### PLATFORM-REQ-X-003 — Multi-Tenant Isolation

**Statement:** Every Platform Service MUST enforce tenant isolation through `organization_id` and `branch_id` on all persisted records.

**Applies to:** Audit, FileStorage, Logging (when context available), Notification.

**Acceptance Criteria:**
1. Every table carries `organization_id` column
2. Every query scoped to organization where applicable
3. Cross-organization data leakage prevented by default query scoping

---

### PLATFORM-REQ-X-004 — Audit Trail of Platform Operations

**Statement:** Platform Service operations that affect domain data MUST be recorded in the Audit Platform.

**Applies to:**
- FileStorage: upload and delete operations
- Notification: send, success, failure events

**Excluded from self-audit:**
- Audit Platform does not audit itself
- Logging Platform logs are technical logs, not business audit evidence

---

## 7. Dependency Verification

### 7.1 Authentication Dependencies on Platform

| Authentication Feature | Platform Service | Requirement Reference |
|---|---|---|
| Login/Logout Audit Events | Audit Platform | `AUTH-REQ-014`, `PLATFORM-REQ-AUD-001` |
| Password Reset Email | Notification Platform | `AUTH-REQ-005`, `PLATFORM-REQ-NOT-001` |
| Profile Photo Upload | FileStorage Platform | `AUTH-REQ-009`, `PLATFORM-REQ-FS-001` |
| Structured Logging | Logging Platform | `AUTH-REQ-017`, `PLATFORM-REQ-LOG-001` |

### 7.2 Inter-Platform Dependencies

| Consumer | Depends On | Purpose |
|---|---|---|
| FileStorage | Audit Platform | Record upload/delete events |
| Notification | Audit Platform | Record send/success/failure events |
| Notification | Logging Platform | Log delivery failures |
| Notification | IntegrationHub | WhatsApp, SMS, Push external provider connectivity |
| Audit | Queue | Async record persistence |
| Logging | Queue | Async database/external log writes |
| Notification | Queue | Async channel delivery |

### 7.3 Infrastructure Prerequisites

| Dependency | Source | Status |
|---|---|---|
| Redis | Phase 01 Core Framework | ✅ Available |
| Laravel Queue | Phase 01 Core Framework | ✅ Available |
| PostgreSQL | Phase 01 Core Framework | ✅ Available |
| S3-compatible storage | External (MinIO/AWS) | ⚠️ Required for production; Local for dev |
| SMTP | External | ⚠️ Required for email channel |
| WhatsApp Business API | External (via IntegrationHub) | ⚠️ Required for WhatsApp channel |
| Firebase FCM | External (via IntegrationHub) | ⚠️ Required for Push channel |

---

## 8. Requirement Summary

| ID | Service | Requirement |
|---|---|---|
| PLATFORM-REQ-AUD-001 | Audit | Immutable audit recording via Queue |
| PLATFORM-REQ-AUD-002 | Audit | Audit action catalog (11 actions) |
| PLATFORM-REQ-AUD-003 | Audit | Interface-driven consumption |
| PLATFORM-REQ-FS-001 | FileStorage | UUID-based file storage with validation |
| PLATFORM-REQ-FS-002 | FileStorage | Per-folder MIME/size validation |
| PLATFORM-REQ-FS-003 | FileStorage | Driver-agnostic storage (local/S3) |
| PLATFORM-REQ-FS-004 | FileStorage | Secure file access via signed URLs |
| PLATFORM-REQ-FS-005 | FileStorage | Interface-driven consumption |
| PLATFORM-REQ-LOG-001 | Logging | Structured PSR-3 logging with level routing |
| PLATFORM-REQ-LOG-002 | Logging | Level-based destination routing |
| PLATFORM-REQ-LOG-003 | Logging | Interface-driven consumption |
| PLATFORM-REQ-NOT-001 | Notification | Queue-based notification dispatch with retry |
| PLATFORM-REQ-NOT-002 | Notification | Multi-channel delivery (5 channels) |
| PLATFORM-REQ-NOT-003 | Notification | In-App notification read status |
| PLATFORM-REQ-NOT-004 | Notification | Interface-driven consumption |
| PLATFORM-REQ-X-001 | Cross-cutting | Service container binding |
| PLATFORM-REQ-X-002 | Cross-cutting | Laravel Queue dependency |
| PLATFORM-REQ-X-003 | Cross-cutting | Multi-tenant isolation |
| PLATFORM-REQ-X-004 | Cross-cutting | Platform operations audit trail |

Total: 19 requirements across 4 service areas + 4 cross-cutting.

---

## Governance Record

| Check | Result |
|---|---|
| Derived from existing Platform design artifacts | ✅ `AuditPlatform.md`, `FileStorage.md`, `LoggingPlatform.md`, `NotificationPlatform.md` |
| Consistent with existing Platform contracts | ✅ `AuditServiceInterface`, `FileStorageServiceInterface`, `LoggerServiceInterface`, `NotificationServiceInterface`, `NotificationChannelInterface` |
| Consistent with Architecture Standards | ✅ `AuditPolicy.md`, `FieldClassification.md`, `ExposureClassification.md` |
| No invented functionality | ✅ All requirements trace to existing docs/contracts |
| Authentication not modified | ✅ Protected boundary respected |
| ADRs/Decisions not modified | ✅ |
| AGENTS.md not modified | ✅ |
