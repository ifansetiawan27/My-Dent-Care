# Phase 07 — Platform Services Business Rules

**Date:** 2026-08-09
**Phase:** 07 — Platform Services
**SDLC Stage:** 02 — Business Rules
**Status:** `STEP_07_04_PLATFORM_SERVICES_BUSINESS_RULES_DRAFT`

---

## 1. Audit Platform Business Rules

### PLATFORM-BR-AUD-001 — Mandatory Audit Recording

**Rule:** Every write operation (create, update, delete, restore, login, logout) executed by any domain Service MUST record an audit entry through `AuditServiceInterface`.

**Rationale:** Canonical audit evidence per ADR-005. No domain operation that changes state may be invisible to compliance.

**Traceability:**
- `PLATFORM-REQ-AUD-001` — Immutable Audit Recording
- `AuditPlatform.md` Business Rule #1: "Setiap operasi write WAJIB menghasilkan audit record"
- `ArchitectureStandards/AuditPolicy.md` — Immutable Audit Events

**Invariants:**
- Every `create`, `update`, `delete`, `restore` call in a domain Service must be followed by an audit record.
- Login and logout are separate audit events with `auditable_type` and `auditable_id` set to null.
- Audit recording must not block or roll back the domain operation.

---

### PLATFORM-BR-AUD-002 — Audit Record Immutability

**Rule:** Once persisted, an audit record MUST NOT be updated or deleted through normal operations. Archive/purge is governed by a separate retention policy outside the immediate record creation scope.

**Rationale:** Immutable Audit Events are the canonical compliance and forensic evidence. Mutation destroys evidentiary value.

**Traceability:**
- `PLATFORM-REQ-AUD-001` — Immutable Audit Recording, AC #6
- `AuditPlatform.md` Business Rule #2: "Audit record tidak boleh dihapus"
- `AuditPlatform.md` Design Principle #3: "Immutable — record audit tidak boleh diubah atau dihapus"

**Invariants:**
- `audit_logs` table has no `updated_at` column.
- No `UPDATE` or `DELETE` queries execute against `audit_logs` from domain or Platform code.

---

### PLATFORM-BR-AUD-003 — Create Event Diff

**Rule:** For `AuditAction::Create` events, `old_value` MUST be null.

**Rationale:** No prior state exists for newly created records.

**Traceability:**
- `PLATFORM-REQ-AUD-001` AC #3
- `AuditPlatform.md` Business Rule #3: "old_value bernilai null pada event create"
- `AuditAction::isMutation()` — classifies Create as mutation

**Invariants:**
- `AuditEntryDTO.oldValue` is empty array or null when `action === AuditAction::Create`.

---

### PLATFORM-BR-AUD-004 — Delete Event Diff

**Rule:** For `AuditAction::Delete` events, `new_value` MUST be null.

**Rationale:** No post-deletion state exists for deleted records.

**Traceability:**
- `PLATFORM-REQ-AUD-001` AC #3
- `AuditPlatform.md` Business Rule #4: "new_value bernilai null pada event delete"

**Invariants:**
- `AuditEntryDTO.newValue` is empty array or null when `action === AuditAction::Delete`.

---

### PLATFORM-BR-AUD-005 — Session Events Without Model Context

**Rule:** Login and logout audit events MUST NOT carry `auditable_type` or `auditable_id`. These events are user-level, not model-level.

**Traceability:**
- `PLATFORM-REQ-AUD-001` AC #4
- `AuditPlatform.md` Business Rule #5: "Login dan logout dicatat tanpa auditable_type/auditable_id"

**Invariants:**
- When `action` is `AuditAction::Login` or `AuditAction::Logout`, `auditableType` and `auditableId` must be null in the `AuditEntryDTO`.

---

### PLATFORM-BR-AUD-006 — Secret Exclusion from Audit Diffs

**Rule:** Secret-classified fields (password, password hash, access token, refresh token, credit card number) MUST NOT appear in `old_value` or `new_value`.

**Rationale:** Storing secrets in audit records creates an irreversible exposure surface. Audit is immutable; secrets leaked into audit require purge that violates immutability.

**Traceability:**
- `PLATFORM-REQ-AUD-001` AC #5, Security Boundary
- `AuditPlatform.md` Business Rule #6: "Sensitive fields TIDAK boleh disimpan dalam old_value/new_value"
- `ArchitectureStandards/ExposureClassification.md` — Secret classification

**Invariants:**
- Domain Service must filter Secret-classified fields from `oldValue`/`newValue` arrays before passing to `AuditServiceInterface`.
- The Audit Platform does not perform redaction — exclusion is the caller's responsibility.

---

### PLATFORM-BR-AUD-007 — Non-Blocking Audit Dispatch

**Rule:** Audit record persistence MUST be dispatched asynchronously through Laravel Queue. The caller's request or database transaction MUST NOT block on audit persistence.

**Traceability:**
- `PLATFORM-REQ-AUD-001` — Functional Behavior: "No synchronous database write"
- `AuditPlatform.md` Business Rule #8: "Pencatatan audit dijalankan melalui Queue agar non-blocking"
- `AuditPlatform.md` Design Principle #2: "Non-blocking"

**Invariants:**
- `AuditServiceInterface::record()` dispatches to Queue and returns immediately.
- Audit queue failure does not throw an exception to the domain caller.
- Queue failure itself must be logged (not audited).

---

### PLATFORM-BR-AUD-008 — Tenant-Scoped Audit Records

**Rule:** Every audit record MUST carry `organization_id` and `branch_id`. Access to audit records is scoped to the authenticated user's organization.

**Traceability:**
- `PLATFORM-REQ-AUD-001` — Tenant/Context Boundary
- `AuditPlatform.md` Business Rule #7: "Audit record di-scope ke organization"
- `AuditPlatform.md` Design Principle #4: "Multi-tenant — setiap record wajib menyimpan organization_id dan branch_id"

**Invariants:**
- `AuditEntryDTO.organizationId` populated from the authenticated user's context.
- Cross-organization audit access denied by authorization policy.

---

### PLATFORM-BR-AUD-009 — Interface-Only Audit Consumption

**Rule:** No domain Service, Model, or Repository may write directly to the `audit_logs` table. All audit recording MUST pass through `AuditServiceInterface`.

**Traceability:**
- `PLATFORM-REQ-AUD-003` — Interface-Driven Consumption
- `AuditPlatform.md` Integration Pattern: "Domain tidak pernah menulis ke audit_logs secara langsung"

**Invariants:**
- `audit_logs` table is never referenced in any domain namespace.
- Only the Audit Platform's concrete implementation and its Queue job access the table directly.

---

### PLATFORM-BR-AUD-010 — Audit Failure Isolation

**Rule:** A failure in audit recording (Queue dispatch failure, persistence failure) MUST NOT propagate to the calling domain's transaction boundary. The domain operation succeeds or fails independently of audit recording.

**Traceability:**
- `PLATFORM-REQ-AUD-001` — Failure Behavior: "No caller exception — audit failures must not propagate"

**Invariants:**
- `AuditServiceInterface::record()` never throws.
- If the Queue dispatch itself throws, the exception is caught and logged — not rethrown.

---

## 2. FileStorage Platform Business Rules

### PLATFORM-BR-FS-001 — UUID File Naming

**Rule:** Every stored file MUST receive a UUID as its physical filename. The user's original filename MUST NEVER be used as the stored file name.

**Rationale:** Prevents path traversal, file enumeration, and name collisions. Separates display metadata from physical storage security.

**Traceability:**
- `PLATFORM-REQ-FS-001` — UUID-Based File Storage
- `FileStorage.md` Business Rule #1: "Setiap file WAJIB memiliki nama UUID"
- `FileStorage.md` Design Principle #2: "UUID naming"

**Invariants:**
- `StoredFileDTO.storedName` is always a UUID string.
- Physical file on disk is named by UUID only.

---

### PLATFORM-BR-FS-002 — Original Name as Metadata Only

**Rule:** The user's original filename MUST be stored only in the `original_name` metadata column. It is never used for storage path construction or access control.

**Traceability:**
- `PLATFORM-REQ-FS-001` AC #2
- `FileStorage.md` Business Rule #2: "Nama asli file hanya disimpan sebagai metadata (original_name) untuk display"

---

### PLATFORM-BR-FS-003 — Multi-Tenant File Path

**Rule:** Every stored file path MUST be scoped to `organization_id` and `branch_id` using the convention `{folder}/{organization_id}/{branch_id}/{yyyy}/{mm}/{uuid}.{ext}`.

**Traceability:**
- `PLATFORM-REQ-FS-001` AC #6, Tenant/Context Boundary
- `FileStorage.md` Business Rule #3: "Setiap file WAJIB di-scope ke organization_id dalam path dan metadata"
- `FileStorage.md` Path Convention section

**Invariants:**
- Root segment before storage path always contains `organization_id`.
- Cross-organization file access is prevented by path isolation.

---

### PLATFORM-BR-FS-004 — Pre-Storage Validation

**Rule:** MIME type and file extension MUST be validated against the per-folder whitelist before the file is written to disk. The whitelist is defined by `StorageFolder::allowedExtensions()`.

**Traceability:**
- `PLATFORM-REQ-FS-002` — MIME and Size Validation per Folder
- `FileStorage.md` Business Rule #4: "Ekstensi dan MIME type WAJIB divalidasi sebelum penyimpanan (whitelist)"
- `FileStorage.md` Security: "MIME validation — Whitelist per folder — tolak executable & script"

**Invariants:**
- File with MIME/extension outside the folder whitelist is rejected with `BusinessException` before any disk write.
- Executable and script MIME types are never accepted.

---

### PLATFORM-BR-FS-005 — Per-Folder Size Limits

**Rule:** File size MUST be validated against the per-folder maximum defined by `StorageFolder::maxSizeBytes()`. Oversized files are rejected before any disk write.

**Traceability:**
- `PLATFORM-REQ-FS-002` — MIME and Size Validation per Folder
- `FileStorage.md` Business Rule #5: "Ukuran file maksimum diatur per folder/kategori"

**Invariants:**
- Size check happens before disk allocation.
- Current limits: Patient/Doctor/Asset 10 MB, Organization/Branch 5 MB, Lab 20 MB, Radiology 100 MB.

---

### PLATFORM-BR-FS-006 — Soft Delete File Lifecycle

**Rule:** File records use soft delete (`deleted_at`). The physical file is retained until the applicable retention period expires; the physical deletion date is determined by the file category's retention policy, not the soft-delete timestamp.

**Traceability:**
- `PLATFORM-REQ-FS-001` — Persistence: Soft delete via `deleted_at`
- `FileStorage.md` Business Rule #6: "File dihapus menggunakan soft delete — file fisik dihapus setelah masa retensi"
- `FileStorage.md` Business Rule #7: "File medis mengikuti masa retensi rekam medis (7 tahun)"

**Invariants:**
- Domain calls soft-delete only.
- Physical file deletion is deferred to a background retention process (out of Phase 07 scope).

---

### PLATFORM-BR-FS-007 — Signed URL Access Only

**Rule:** File access MUST be granted through time-limited signed URLs or permission-based streaming. Permanent public URLs to stored files are forbidden. Medical files (patient, radiology, lab) must never be publicly accessible.

**Traceability:**
- `PLATFORM-REQ-FS-004` — Secure File Access
- `FileStorage.md` Business Rule #8: "Akses file WAJIB melalui signed URL / permission check — tidak boleh public langsung"
- `FileStorage.md` Security: "Direct access — Public direct access DILARANG untuk file medis"

**Invariants:**
- Default signed URL expiry: 15 minutes (900 seconds).
- Expired signed URLs return HTTP 403 or 404.

---

### PLATFORM-BR-FS-008 — Integrity Hash

**Rule:** Every stored file MUST have a SHA-256 hash computed at upload time and stored in the `hash` column. The hash is used for integrity verification and optional deduplication.

**Traceability:**
- `PLATFORM-REQ-FS-001` AC #5
- `FileStorage.md` Business Rule #9: "File hash (SHA-256) digunakan untuk mencegah duplikasi dan verifikasi integritas"
- `FileStorage.md` Design Principle #6: "Integrity check"

**Invariants:**
- Hash computed from the uploaded file content before disk write.
- Hash computation failure aborts the entire store operation.

---

### PLATFORM-BR-FS-009 — Interface-Only File Operations

**Rule:** No domain Service, Controller, or Model may call `Storage::put()`, `Storage::disk()`, or any filesystem SDK directly. All file operations MUST pass through `FileStorageServiceInterface`.

**Traceability:**
- `PLATFORM-REQ-FS-005` — Interface-Driven Consumption
- `FileStorage.md` Integration Pattern: "Domain tidak pernah memanggil Storage::put() atau S3 SDK secara langsung"

---

### PLATFORM-BR-FS-010 — Server-Side Validation

**Rule:** File validation (MIME, extension, size) MUST be performed server-side on every upload. Client-side validation is never trusted as the sole validation layer.

**Traceability:**
- `PLATFORM-REQ-FS-002` AC #3
- `FileStorage.md` Notes: "Semua upload divalidasi ulang di server — tidak percaya validasi client"

---

### PLATFORM-BR-FS-011 — File Operations Audit Trail

**Rule:** File upload and delete operations MUST be recorded in the Audit Platform via `AuditServiceInterface`.

**Traceability:**
- `PLATFORM-REQ-FS-001` AC #8
- `PLATFORM-REQ-X-004` — Audit Trail of Platform Operations
- `FileStorage.md` Notes: "File Storage Platform mencatat aktivitas upload/delete ke Audit Platform"

---

## 3. Logging Platform Business Rules

### PLATFORM-BR-LOG-001 — Minimum Error-Level for Exceptions

**Rule:** Every unhandled exception MUST be logged at minimum `LogLevel::Error`.

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Structured Logging
- `LoggingPlatform.md` Business Rule #1: "Setiap exception yang tidak tertangani WAJIB tercatat minimal level error"

**Invariants:**
- Global exception handler captures all unhandled exceptions and logs at error or higher.

---

### PLATFORM-BR-LOG-002 — Database Persistence for Warning and Above

**Rule:** Log entries at `LogLevel::Warning` severity and above MUST be persisted to the `system_logs` database table. Lower severity levels (`debug`, `info`, `notice`) are written to daily log files only.

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Routing per Level table
- `LoggingPlatform.md` Business Rule #2: "Log level error ke atas WAJIB masuk ke database"
- `LoggingPlatform.md` Routing per Level: error and above → file + database + external

**Invariants:**
- `LogLevel::shouldPersist()` returns `true` for Warning, Error, Critical, Alert, Emergency.
- Debug, Info, Notice are never written to `system_logs` table.

---

### PLATFORM-BR-LOG-003 — Sensitive Data Exclusion

**Rule:** Passwords, tokens, credit card numbers, and other Secret-classified data MUST NOT appear in log messages or context arrays.

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Security Boundary
- `LoggingPlatform.md` Business Rule #3: "Sensitive data TIDAK boleh masuk ke log"

**Invariants:**
- Exclusion is the caller's responsibility; the Logging Platform does not scan or redact.

---

### PLATFORM-BR-LOG-004 — Debug Only in Non-Production

**Rule:** `LogLevel::Debug` log entries MUST only be active in non-production environments. In production, debug-level calls must be silently suppressed.

**Traceability:**
- `PLATFORM-REQ-LOG-001` AC #4
- `LoggingPlatform.md` Business Rule #4: "Log debug hanya aktif di environment non-production"

**Invariants:**
- Production environment (`APP_ENV=production`): `LoggerServiceInterface::debug()` must not write to file or any destination.

---

### PLATFORM-BR-LOG-005 — Source Channel on Every Entry

**Rule:** Every log entry MUST include a `channel` value identifying the source module (e.g., `AuthService`, `PatientService`).

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Functional Behavior: message format
- `LoggingPlatform.md` Business Rule #5: "Setiap log WAJIB menyertakan channel (nama modul asal)"

---

### PLATFORM-BR-LOG-006 — Tenant Context on User Logs

**Rule:** Log entries that are created within an authenticated user context MUST carry `organization_id` and `branch_id`. Non-authenticated contexts may leave these null.

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Tenant/Context Boundary
- `LoggingPlatform.md` Business Rule #6: "Log yang menyertakan user WAJIB menyimpan organization_id dan branch_id"

---

### PLATFORM-BR-LOG-007 — Non-Blocking Database Log Writes

**Rule:** Log persistence to the `system_logs` database table and to external monitoring MUST be dispatched through Laravel Queue. File writes may be synchronous.

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Availability/Reliability: "Database and external writes dispatched via Queue"
- `LoggingPlatform.md` Business Rule #7: "Penulisan ke database dan external dijalankan melalui Queue (non-blocking)"

---

### PLATFORM-BR-LOG-008 — Consistent Message Format

**Rule:** Log messages MUST follow the format `[Module::action] descriptive message`.

**Traceability:**
- `PLATFORM-REQ-LOG-001` — Functional Behavior: "Message format: [ServiceName::action] descriptive message"
- `LoggingPlatform.md` Business Rule #8: "Format pesan konsisten: [Module::action] message"

**Examples:**
- `[AuthService::login] Login successful.`
- `[PatientService::create] Failed to create patient.`

---

### PLATFORM-BR-LOG-009 — Interface-Only Logging

**Rule:** No domain Service or Platform Service may call Laravel's `Log` facade directly (`Log::info()`, `Log::error()`, etc.). All logging MUST pass through `LoggerServiceInterface`.

**Traceability:**
- `PLATFORM-REQ-LOG-003` — Interface-Driven Consumption
- `LoggingPlatform.md` Integration Pattern: "Domain / Platform Service calls interface"

**Existing Exception:**
- `AuthService` currently uses `Log` facade directly. This is a pre-existing cosmetic concern documented in the Authentication Implementation Preflight. It is not a Phase 07 blocker but should be migrated to `LoggerServiceInterface` after Platform implementation.

---

### PLATFORM-BR-LOG-010 — Request Correlation

**Rule:** Log entries created during the same HTTP request lifecycle MUST carry a common `request_id` for correlation.

**Traceability:**
- `PLATFORM-REQ-LOG-001` AC #5
- `LoggingPlatform.md` Design Principle #6: "Correlation — setiap request memiliki request_id untuk menelusuri log terkait"
- `LoggingPlatform.md` Notes: "request_id di-generate di middleware"

---

### PLATFORM-BR-LOG-011 — Logging vs Audit Separation

**Rule:** The Logging Platform captures technical/diagnostic events. Business data changes MUST be recorded through the Audit Platform, not the Logging Platform. The two platforms serve distinct and non-overlapping purposes.

**Traceability:**
- `LoggingPlatform.md` Notes: "Logging Platform terpisah dari Audit Platform: Logging untuk teknis/error, Audit untuk perubahan data bisnis"
- `ArchitectureStandards/AuditPolicy.md` — Data Categories: Technical Log vs Immutable Audit Event

---

## 4. Notification Platform Business Rules

### PLATFORM-BR-NOT-001 — Queue-Only Notification Dispatch

**Rule:** ALL notification dispatch MUST go through Laravel Queue. Synchronous notification delivery is forbidden. The domain caller's request must not block on notification delivery.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Queue-Based Notification Dispatch
- `NotificationPlatform.md` Business Rule #1: "SEMUA notifikasi WAJIB dikirim melalui Queue Laravel — tidak boleh synchronous"
- `NotificationPlatform.md` Design Principle #2: "Queue-based"

**Invariants:**
- `NotificationServiceInterface::send()` creates a `pending` record, dispatches a Queue job, and returns immediately.

---

### PLATFORM-BR-NOT-002 — Retry with Exponential Backoff

**Rule:** Failed notification delivery MUST be retried. Default retry count: 3 attempts with exponential backoff. After all retries are exhausted, the notification status is set to `failed` and the reason is recorded.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Failure Behavior
- `NotificationPlatform.md` Business Rule #2: "Notifikasi gagal WAJIB di-retry (default: 3x dengan exponential backoff)"
- `NotificationPlatform.md` Design Principle #6: "Retryable"

**Invariants:**
- Retry count is configurable; default is 3.
- Exponential backoff is applied (e.g., 1 min, 5 min, 15 min for 3 attempts).

---

### PLATFORM-BR-NOT-003 — Tenant-Scoped Notifications

**Rule:** Every notification record MUST carry `organization_id`. Notifications are scoped to the organization of the recipient.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Tenant/Context Boundary
- `NotificationPlatform.md` Business Rule #3: "Setiap notifikasi WAJIB menyimpan organization_id untuk multi-tenant scoping"

---

### PLATFORM-BR-NOT-004 — Graceful Channel Skip

**Rule:** When a requested delivery channel is not configured or not available for the target organization, the channel MUST be skipped gracefully without failing the entire notification. A warning-level log entry records the skip.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Failure Behavior: "Channel not configured: skip gracefully, do not fail"
- `NotificationPlatform.md` Business Rule #4: "Channel yang tidak dikonfigurasi untuk sebuah organization harus di-skip dengan graceful"
- `NotificationChannelInterface::isAvailableFor()`

**Invariants:**
- If a notification requests channels `['whatsapp', 'email']` and WhatsApp is not configured for the org, only email delivery proceeds. The notification status reflects partial delivery.

---

### PLATFORM-BR-NOT-005 — In-App Read Status

**Rule:** In-App channel notifications MUST support a read/unread lifecycle via a `read_at` timestamp. Calling `markAsRead()` sets `read_at` to the current timestamp.

**Traceability:**
- `PLATFORM-REQ-NOT-003` — In-App Notification Read Status
- `NotificationPlatform.md` Business Rule #5: "In-App notification menyimpan read_at untuk menandai sudah dibaca"
- `NotificationServiceInterface::markAsRead()`
- `NotificationStatus::Read` enum case

**Invariants:**
- `markAsRead()` returns `false` for a non-existent notification ID.
- `read_at` is only applicable to in-app channel notifications.

---

### PLATFORM-BR-NOT-006 — Sensitive Data Exclusion

**Rule:** Secret-classified and Sensitive-classified data MUST NOT appear in plaintext in the `body` or `data` columns of the `notifications` table.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Security Boundary, Secret/Data Protection
- `NotificationPlatform.md` Business Rule #6: "Sensitive data tidak boleh disimpan dalam kolom body/data dalam bentuk plaintext"

---

### PLATFORM-BR-NOT-007 — Opt-Out Honored

**Rule:** If a recipient has opted out of a delivery channel, the channel MUST be skipped for that recipient. Opt-out preferences are checked per-channel, per-recipient.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Implicit in "preference honored"
- `NotificationPlatform.md` Business Rule #7: "Preferensi channel penerima harus dihormati (opt-out honored)"

**Status:** `TBD/OPEN` — The exact storage and resolution mechanism for recipient opt-out preferences is not defined in existing Platform design artifacts or contracts. This rule establishes the invariant but defers the implementation mechanism.

---

### PLATFORM-BR-NOT-008 — Invalid Contact Handling

**Rule:** WhatsApp and SMS notifications sent to invalid or unreachable phone numbers MUST be marked as `failed` after a limited number of retry attempts (not exceeding the standard retry count). No unlimited retry loop is permitted for permanently invalid contacts.

**Traceability:**
- `PLATFORM-REQ-NOT-001` AC #6
- `NotificationPlatform.md` Business Rule #8: "Notifikasi WhatsApp/SMS ke nomor tidak valid ditandai failed, tidak retry tak terbatas"

**Invariants:**
- Invalid contact status is provider-determined (e.g., WhatsApp API returns "invalid number").
- After detection, remaining retries are skipped and the notification is marked `failed`.

---

### PLATFORM-BR-NOT-009 — Interface-Only Provider Access

**Rule:** No domain Service may call any notification provider (SMTP, Twilio, FCM, WhatsApp Business API) directly. All notification delivery MUST pass through `NotificationServiceInterface` and its channel drivers.

**Traceability:**
- `PLATFORM-REQ-NOT-004` — Interface-Driven Consumption
- `NotificationPlatform.md` Integration Pattern: "Domain tidak pernah memanggil provider secara langsung"

---

### PLATFORM-BR-NOT-010 — Notification Audit and Logging

**Rule:** Notification dispatch events (send, delivery success, delivery failure) MUST be recorded in the Audit Platform via `AuditServiceInterface`. Delivery failures MUST additionally be logged in the Logging Platform via `LoggerServiceInterface`.

**Traceability:**
- `PLATFORM-REQ-NOT-001` — Auditability
- `PLATFORM-REQ-X-004` — Audit Trail of Platform Operations
- `NotificationPlatform.md` Notes: "Notification Platform mencatat aktivitas ke Audit Platform dan Logging Platform"

---

## 5. Cross-Cutting Business Rules

### PLATFORM-BR-X-001 — Platform Interface Consumption

**Rule:** Domain Services MUST depend on Platform Service interfaces (`AuditServiceInterface`, `FileStorageServiceInterface`, `LoggerServiceInterface`, `NotificationServiceInterface`) exclusively. No domain may import, instantiate, or reference a Platform concrete implementation, Model, Repository, Migration, or Queue job.

**Traceability:**
- `PLATFORM-REQ-AUD-003`, `PLATFORM-REQ-FS-005`, `PLATFORM-REQ-LOG-003`, `PLATFORM-REQ-NOT-004`
- `PLATFORM-REQ-X-001` — Service Container Binding
- `AGENTS.md` Architecture Principles: SOLID — Dependency Inversion

**Invariants:**
- Domain composer imports only interfaces.
- Service container resolves concrete implementations.

---

### PLATFORM-BR-X-002 — Mandatory Tenant Column

**Rule:** Every persisted Platform record (audit_logs, files, system_logs, notifications) MUST include an `organization_id` column. Where a `branch_id` is declared in the schema, it MUST be populated when available from the operation context.

**Traceability:**
- `PLATFORM-REQ-X-003` — Multi-Tenant Isolation
- `AGENTS.md` Enterprise Standards: Multi-Organization & Multi-Branch Data Isolation

**Invariants:**
- `organization_id` is non-nullable on `audit_logs`, `files`, `notifications`.
- `organization_id` is nullable on `system_logs` (non-authenticated contexts exist).
- `branch_id` is nullable where the schema declares it nullable.

---

### PLATFORM-BR-X-003 — Non-Blocking Platform Operations

**Rule:** Platform service operations that involve external I/O (database writes via Queue, file storage to remote disks, notification provider delivery) MUST NOT block the domain caller's request. Any operation that could introduce latency must be dispatched asynchronously.

**Traceability:**
- `PLATFORM-REQ-AUD-001`, `PLATFORM-REQ-LOG-001`, `PLATFORM-REQ-NOT-001` — Queue-based processing
- `PLATFORM-REQ-X-002` — Laravel Queue Dependency
- `AGENTS.md` Architecture Principles: Non-blocking cross-cutting operations

**Invariants:**
- Audit persistence: Queue-dispatched.
- Log database writes: Queue-dispatched.
- Notification delivery: Queue-dispatched.
- File storage: synchronous (disk I/O is fast/necessary for validation) but must not trigger audit/notification synchronously.

---

### PLATFORM-BR-X-004 — No Self-Audit

**Rule:** Platform Services that produce audit records MUST NOT audit their own internal operations. The Audit Platform does not record audit events about its own record creation. The Logging Platform does not send its own errors through the Audit Platform — they go through the Logging Platform.

**Traceability:**
- `PLATFORM-REQ-AUD-001` — Auditability: "Audit Platform does NOT audit itself"
- `PLATFORM-REQ-X-004` — Audit Trail of Platform Operations

**Invariants:**
- `audit_logs` table inserts do not generate further audit records.
- `system_logs` table inserts are technical logs, not audit events.
- FileStorage and Notification dispatch audit events through Audit Platform — the Audit Platform itself does not.

---

## 6. Business Rule Summary

| Rule ID | Service | Rule |
|---|---|---|
| PLATFORM-BR-AUD-001 | Audit | Mandatory audit recording for every write |
| PLATFORM-BR-AUD-002 | Audit | Audit record immutability |
| PLATFORM-BR-AUD-003 | Audit | old_value null on create |
| PLATFORM-BR-AUD-004 | Audit | new_value null on delete |
| PLATFORM-BR-AUD-005 | Audit | Login/logout without auditable model context |
| PLATFORM-BR-AUD-006 | Audit | Secret exclusion from audit diffs |
| PLATFORM-BR-AUD-007 | Audit | Non-blocking Queue dispatch |
| PLATFORM-BR-AUD-008 | Audit | Tenant-scoped records |
| PLATFORM-BR-AUD-009 | Audit | Interface-only consumption |
| PLATFORM-BR-AUD-010 | Audit | Failure isolation from caller |
| PLATFORM-BR-FS-001 | FileStorage | UUID file naming |
| PLATFORM-BR-FS-002 | FileStorage | Original name as metadata only |
| PLATFORM-BR-FS-003 | FileStorage | Multi-tenant path scoping |
| PLATFORM-BR-FS-004 | FileStorage | Pre-storage MIME/extension validation |
| PLATFORM-BR-FS-005 | FileStorage | Per-folder size limits |
| PLATFORM-BR-FS-006 | FileStorage | Soft delete lifecycle |
| PLATFORM-BR-FS-007 | FileStorage | Signed URL access only |
| PLATFORM-BR-FS-008 | FileStorage | SHA-256 integrity hash |
| PLATFORM-BR-FS-009 | FileStorage | Interface-only file operations |
| PLATFORM-BR-FS-010 | FileStorage | Server-side validation |
| PLATFORM-BR-FS-011 | FileStorage | File operations audit trail |
| PLATFORM-BR-LOG-001 | Logging | Minimum error-level for exceptions |
| PLATFORM-BR-LOG-002 | Logging | Database persistence for warning and above |
| PLATFORM-BR-LOG-003 | Logging | Sensitive data exclusion |
| PLATFORM-BR-LOG-004 | Logging | Debug only in non-production |
| PLATFORM-BR-LOG-005 | Logging | Source channel on every entry |
| PLATFORM-BR-LOG-006 | Logging | Tenant context on user logs |
| PLATFORM-BR-LOG-007 | Logging | Non-blocking database writes |
| PLATFORM-BR-LOG-008 | Logging | Consistent message format |
| PLATFORM-BR-LOG-009 | Logging | Interface-only logging |
| PLATFORM-BR-LOG-010 | Logging | Request correlation ID |
| PLATFORM-BR-LOG-011 | Logging | Logging vs Audit separation |
| PLATFORM-BR-NOT-001 | Notification | Queue-only dispatch |
| PLATFORM-BR-NOT-002 | Notification | Retry with exponential backoff |
| PLATFORM-BR-NOT-003 | Notification | Tenant-scoped notifications |
| PLATFORM-BR-NOT-004 | Notification | Graceful channel skip |
| PLATFORM-BR-NOT-005 | Notification | In-App read status |
| PLATFORM-BR-NOT-006 | Notification | Sensitive data exclusion |
| PLATFORM-BR-NOT-007 | Notification | Opt-out honored (TBD/OPEN) |
| PLATFORM-BR-NOT-008 | Notification | Invalid contact handling |
| PLATFORM-BR-NOT-009 | Notification | Interface-only provider access |
| PLATFORM-BR-NOT-010 | Notification | Notification audit and logging |
| PLATFORM-BR-X-001 | Cross-cutting | Platform interface consumption |
| PLATFORM-BR-X-002 | Cross-cutting | Mandatory tenant column |
| PLATFORM-BR-X-003 | Cross-cutting | Non-blocking platform operations |
| PLATFORM-BR-X-004 | Cross-cutting | No self-audit |

**Total:** 46 business rules (10 Audit + 11 FileStorage + 11 Logging + 10 Notification + 4 Cross-cutting).

---

## 7. Open Items

| Rule | Status | Description |
|---|---|---|
| PLATFORM-BR-NOT-007 | `TBD/OPEN` | Opt-out preference storage and resolution mechanism is not defined in existing artifacts. Rule establishes invariant; implementation mechanism is deferred. |

---

## Governance Record

| Check | Result |
|---|---|
| All rules trace to PLATFORM-REQ-* requirements | ✅ See traceability in each rule |
| No invented functionality | ✅ All derived from existing source docs |
| Authentication not modified | ✅ Not referenced beyond consumer example |
| No ADR/Decision modified | ✅ |
| No AGENTS.md modified | ✅ |
| No implementation artifacts created | ✅ |
| No Design Freeze declared | ✅ |
| Architecture Standards respected | ✅ |
| Status remains DRAFT | ✅ |
