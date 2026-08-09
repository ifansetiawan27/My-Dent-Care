# Phase 07 — Platform Services Database Design

**Date:** 2026-08-09
**Phase:** 07 — Platform Services
**SDLC Stage:** 04 — Database Design
**Status:** `STEP_07_09_PLATFORM_SERVICES_DATABASE_DESIGN_DRAFT`

**Traceability:**
- Requirement: `docs/Platform/Requirement.md` (STEP_07_03_PASS)
- Business Rules: `docs/Platform/BusinessRule.md` (STEP_07_06_PASS)
- Flow: `docs/Platform/PlatformFlow.md` (STEP_07_08_PASS)
- Design Docs: `AuditPlatform.md`, `FileStorage.md`, `LoggingPlatform.md`, `NotificationPlatform.md`
- Contracts: `app/Platform/*/Contracts/`, `app/Platform/*/DTO/`, `app/Platform/*/Enums/`
- Conventions: `AGENTS.md`, `docs/Architecture/Standards/`, `app/Core/Base/BaseModel.php`

---

## 1. Database Design Overview

### 1.1 Service Ownership

| Service | Table | Data Category (ADR-005) | Model Base Class |
|---|---|---|---|
| Audit Platform | `audit_logs` | Immutable Audit Event | `Model` (not `BaseModel`) |
| FileStorage Platform | `files` | Business Record | `BaseModel` |
| Logging Platform | `system_logs` | Technical Log | `Model` (not `BaseModel`) |
| Notification Platform | `notifications` | Business Record | `BaseModel` |

### 1.2 Repository Conventions Applied

| Convention | Source |
|---|---|
| UUID PK via `Str::orderedUuid()` | `HasUuid` trait, `AGENTS.md` |
| Datetime as `timestamptz` (`timestampsTz()`, `softDeletesTz()`) | `AGENTS.md`, `BaseModel` |
| Soft delete via `deleted_at` (timestamptz, nullable) | `SoftDeletes` trait |
| Audit columns: `created_by`, `updated_by`, `deleted_by` (uuid nullable) | `HasAudit` trait |
| Table names: snake_case plural | Repository standard |
| CHECK constraints via `ALTER TABLE ADD CONSTRAINT` | All existing migrations |
| Composite indexes: tenant columns as leftmost prefix | All existing migrations |
| FK naming: `{table}_{column}_foreign` | All existing migrations |
| Index naming: `{table}_{columns}[_{purpose}]_{index|unique}` | All existing migrations |

### 1.3 Persistence Decisions

All four services require PostgreSQL persistence — none is file-only or memory-only. The rationale for each:

| Service | Persist? | Justification |
|---|---|---|
| Audit Platform | **Yes — `audit_logs`** | PLATFORM-REQ-AUD-001: immutable persistent audit records |
| FileStorage Platform | **Yes — `files`** | PLATFORM-REQ-FS-001: file metadata persistence |
| Logging Platform | **Yes — `system_logs`** | PLATFORM-REQ-LOG-001: database persistence for warning+ |
| Notification Platform | **Yes — `notifications`** | PLATFORM-REQ-NOT-001: notification lifecycle tracking |

---

## 2. `audit_logs` — Audit Platform

### 2.1 Ownership

| Attribute | Value |
|---|---|
| **Service** | Audit Platform |
| **Data Category (ADR-005)** | Immutable Audit Event |
| **Lifecycle** | Immutable — create only; no update, no delete |
| **Model Base** | `Illuminate\Database\Eloquent\Model` (NOT `BaseModel`) + `HasUuid` |
| **Source Requirements** | `PLATFORM-REQ-AUD-001`, `PLATFORM-REQ-AUD-002` |
| **Source Business Rules** | `PLATFORM-BR-AUD-001` through `PLATFORM-BR-AUD-010` |
| **Source Flow** | `PlatformFlow.md` §2.1, §2.3, §2.4 |
| **Source Design Doc** | `AuditPlatform.md` (lines 43-60) |

### 2.2 Column Inventory

| # | Column | Type | Nullable | Default | Description |
|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | Ordered UUID PK |
| 2 | `user_id` | `uuid` | NULL | — | Actor UUID (null = system) |
| 3 | `organization_id` | `uuid` | NOT NULL | — | Tenant org (BR-AUD-008) |
| 4 | `branch_id` | `uuid` | NULL | — | Branch context (null when N/A) |
| 5 | `module` | `varchar(100)` | NOT NULL | — | Source domain (e.g. `patient`) |
| 6 | `action` | `varchar(20)` | NOT NULL | — | `AuditAction` enum value |
| 7 | `auditable_type` | `varchar(255)` | NULL | — | Model class (null for login/logout — BR-AUD-005) |
| 8 | `auditable_id` | `uuid` | NULL | — | Record UUID (null for login/logout) |
| 9 | `old_value` | `jsonb` | NOT NULL | `'{}'` | Pre-change state (empty on create — BR-AUD-003) |
| 10 | `new_value` | `jsonb` | NOT NULL | `'{}'` | Post-change state (empty on delete — BR-AUD-004) |
| 11 | `ip_address` | `varchar(45)` | NULL | — | Client IP (Sensitive per ExposureClassification) |
| 12 | `user_agent` | `text` | NULL | — | Client user agent |
| 13 | `device` | `varchar(20)` | NULL | — | `desktop`, `mobile`, `tablet`, `api` |
| 14 | `created_at` | `timestamptz` | NOT NULL | — | Action timestamp |

**14 columns.** Matches `AuditEntryDTO` 12 fields + `id` + `created_at`.

### 2.3 Immutability Design

| Constraint | Rationale |
|---|---|
| **No `updated_at` column** | BR-AUD-002: audit records immutable |
| **No `deleted_at` column** | BR-AUD-002: no soft delete |
| **No audit columns** (`created_by` etc.) | BR-X-004: Audit does not self-audit |
| **Only INSERT allowed** | PlatformFlow.md §2.1; service layer enforces |
| **No UPDATE** from application code | BR-AUD-002 |
| **No DELETE** from application code | BR-AUD-002 |

### 2.4 CHECK Constraints

```sql
ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_action_check
CHECK (action IN (
    'login','logout','create','update','delete','restore',
    'export','import','print','sync','integration'
));

ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_device_check
CHECK (device IS NULL OR device IN ('desktop','mobile','tablet','api'));
```

### 2.5 Foreign Keys

| Column | References | On Delete | Rationale |
|---|---|---|---|
| `user_id` | `users(id)` | SET NULL | Retain audit if user deleted |
| `organization_id` | `organizations(id)` | RESTRICT | Immutable evidence must retain org link |
| `branch_id` | `branches(id)` | SET NULL | Retain audit if branch removed |

### 2.6 Indexes

| Name | Columns | Type | Rationale |
|---|---|---|---|
| `audit_logs_org_created_idx` | `(organization_id, created_at DESC)` | Composite | Primary access: tenant + time |
| `audit_logs_auditable_idx` | `(auditable_type, auditable_id)` | Composite | Entity-scoped audit lookup |
| `audit_logs_user_created_idx` | `(user_id, created_at DESC)` | Composite | User activity timeline |
| `audit_logs_module_action_idx` | `(module, action, created_at DESC)` | Composite | Module + action filter |
| `audit_logs_branch_created_idx` | `(branch_id, created_at DESC)` | Composite | Branch-scoped audit |

### 2.7 Model Guidance

```php
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasUuid;
    public $timestamps = false; // only created_at
    protected function casts(): array {
        return ['old_value' => 'array', 'new_value' => 'array', 'created_at' => 'datetime'];
    }
}
```

---

## 3. `files` — FileStorage Platform

### 3.1 Ownership

| Attribute | Value |
|---|---|
| **Service** | FileStorage Platform |
| **Data Category (ADR-005)** | Business Record |
| **Lifecycle** | Mutable operational state; soft delete; physical deletion deferred |
| **Model Base** | `BaseModel` → `HasUuid` + `HasAudit` + `SoftDeletes` |
| **Source Requirements** | `PLATFORM-REQ-FS-001` through `PLATFORM-REQ-FS-005` |
| **Source Business Rules** | `PLATFORM-BR-FS-001` through `PLATFORM-BR-FS-011` |
| **Source Flow** | `PlatformFlow.md` §3.1, §3.2, §3.3 |
| **Source Design Doc** | `FileStorage.md` (lines 65-90) |

### 3.2 Column Inventory

| # | Column | Type | Nullable | Default | Description |
|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | Ordered UUID PK (also = `stored_name`) |
| 2 | `organization_id` | `uuid` | NOT NULL | — | Tenant org (BR-FS-003) |
| 3 | `branch_id` | `uuid` | NULL | — | Tenant branch (nullable) |
| 4 | `fileable_type` | `varchar(255)` | NOT NULL | — | Polymorphic owner model class |
| 5 | `fileable_id` | `uuid` | NOT NULL | — | Polymorphic owner record UUID |
| 6 | `folder` | `varchar(50)` | NOT NULL | — | `StorageFolder` enum value |
| 7 | `disk` | `varchar(20)` | NOT NULL | — | `StorageDriver` enum value |
| 8 | `path` | `varchar(500)` | NOT NULL | — | Full storage path per convention |
| 9 | `original_name` | `varchar(255)` | NOT NULL | — | User's filename — metadata only (BR-FS-002) |
| 10 | `stored_name` | `varchar(255)` | NOT NULL | — | UUID physical filename (BR-FS-001) |
| 11 | `mime_type` | `varchar(100)` | NOT NULL | — | MIME type — validated per folder |
| 12 | `extension` | `varchar(10)` | NOT NULL | — | File extension — validated per folder |
| 13 | `size` | `bigint` | NOT NULL | — | Size in bytes — validated per folder |
| 14 | `hash` | `varchar(64)` | NOT NULL | — | SHA-256 integrity hash (BR-FS-008) |
| 15 | `created_by` | `uuid` | NULL | — | Uploading user (auto: `HasAudit`) |
| 16 | `updated_by` | `uuid` | NULL | — | Last updating user (auto: `HasAudit`) |
| 17 | `deleted_by` | `uuid` | NULL | — | Deleting user (auto: `HasAudit`) |
| 18 | `created_at` | `timestamptz` | NOT NULL | — | Upload timestamp |
| 19 | `updated_at` | `timestamptz` | NOT NULL | — | Last update timestamp |
| 20 | `deleted_at` | `timestamptz` | NULL | — | Soft delete timestamp (BR-FS-006) |

**20 columns.** `StoredFileDTO` 12 fields + polymorphic owner + audit columns + timestamps.

### 3.3 Column Evolution from Source Doc

| Source | Change | Rationale |
|---|---|---|
| `created_by` present in FileStorage.md | Preserved | — |
| No `updated_by` in source doc | **Added** | Required by `HasAudit` / `BaseModel` for Business Records |
| No `deleted_by` in source doc | **Added** | Required by `HasAudit` / `BaseModel` for Business Records |

### 3.4 CHECK Constraints

```sql
ALTER TABLE files ADD CONSTRAINT files_folder_check
CHECK (folder IN ('patient','doctor','organization','branch','lab','radiology','asset'));

ALTER TABLE files ADD CONSTRAINT files_disk_check
CHECK (disk IN ('local','s3'));
```

### 3.5 Foreign Keys

| Column | References | On Delete | Rationale |
|---|---|---|---|
| `organization_id` | `organizations(id)` | RESTRICT | File must belong to existing org |
| `branch_id` | `branches(id)` | SET NULL | Retain metadata if branch removed |
| `created_by` | `users(id)` | SET NULL | Retain metadata if user deleted |

### 3.6 Indexes

| Name | Columns | Type | Rationale |
|---|---|---|---|
| `files_org_folder_idx` | `(organization_id, folder)` | Composite | Primary: tenant + category |
| `files_org_branch_idx` | `(organization_id, branch_id)` | Composite | Tenant + branch queries |
| `files_fileable_idx` | `(fileable_type, fileable_id)` | Composite | Polymorphic owner lookup |
| `files_hash_idx` | `(hash)` | Single | Integrity verification (BR-FS-008) |
| `files_folder_idx` | `(folder)` | Single | Category-level queries |
| `files_org_folder_created_idx` | `(organization_id, folder, created_at DESC)` | Composite | Time-ordered listing |
| `files_created_by_idx` | `(created_by)` | Single | User upload history |

### 3.7 Model Guidance

```php
use App\Core\Base\BaseModel;

class File extends BaseModel
{
    // Inherits: HasUuid, HasAudit, SoftDeletes
    protected function casts(): array {
        return [...parent::casts(), 'size' => 'integer'];
    }
}
```

---

## 4. `system_logs` — Logging Platform

### 4.1 Ownership

| Attribute | Value |
|---|---|
| **Service** | Logging Platform |
| **Data Category (ADR-005)** | Technical Log |
| **Lifecycle** | Append-only; retention-based purging (90 days default) |
| **Model Base** | `Illuminate\Database\Eloquent\Model` (NOT `BaseModel`) + `HasUuid` |
| **Source Requirements** | `PLATFORM-REQ-LOG-001`, `PLATFORM-REQ-LOG-002` |
| **Source Business Rules** | `PLATFORM-BR-LOG-001` through `PLATFORM-BR-LOG-011` |
| **Source Flow** | `PlatformFlow.md` §4.1, §4.2 |
| **Source Design Doc** | `LoggingPlatform.md` (lines 63-81) |

### 4.2 Column Inventory

| # | Column | Type | Nullable | Default | Description |
|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | Ordered UUID PK |
| 2 | `level` | `varchar(20)` | NOT NULL | — | `LogLevel` enum value |
| 3 | `message` | `text` | NOT NULL | — | `[Module::action] descriptive` format |
| 4 | `context` | `jsonb` | NOT NULL | `'{}'` | Structured context (request_id, metadata) |
| 5 | `channel` | `varchar(100)` | NOT NULL | — | Source module name (BR-LOG-005) |
| 6 | `user_id` | `uuid` | NULL | — | Related user (null = non-authenticated) |
| 7 | `organization_id` | `uuid` | NULL | — | Tenant (nullable per BR-LOG-006) |
| 8 | `branch_id` | `uuid` | NULL | — | Branch (nullable) |
| 9 | `exception_class` | `varchar(255)` | NULL | — | Exception FQCN |
| 10 | `file` | `varchar(500)` | NULL | — | Source file path |
| 11 | `line` | `integer` | NULL | — | Source line number |
| 12 | `trace` | `text` | NULL | — | Stack trace (error+) |
| 13 | `ip_address` | `varchar(45)` | NULL | — | Client IP |
| 14 | `created_at` | `timestamptz` | NOT NULL | — | Log timestamp |

**14 columns.** Matches LoggingPlatform.md schema exactly.

### 4.3 Logging vs Audit Separation

| Aspect | `system_logs` | `audit_logs` |
|---|---|---|
| Purpose | Diagnostic, operational | Compliance, forensics |
| Immutable | No (retention purging allowed) | Yes |
| Business evidence | No (BR-LOG-011) | Yes |
| `updated_at` | None | None |
| `deleted_at` | None (bulk purge, not soft delete) | None |
| Audit columns | None | None |

### 4.4 CHECK Constraints

```sql
ALTER TABLE system_logs ADD CONSTRAINT system_logs_level_check
CHECK (level IN ('emergency','alert','critical','error','warning','notice','info','debug'));
```

### 4.5 Foreign Keys

| Column | References | On Delete | Rationale |
|---|---|---|---|
| `user_id` | `users(id)` | SET NULL | Retain log if user deleted |
| `organization_id` | `organizations(id)` | SET NULL | Diagnostic, not compliance-critical |

### 4.6 Indexes

| Name | Columns | Type | Rationale |
|---|---|---|---|
| `system_logs_level_created_idx` | `(level, created_at DESC)` | Composite | Severity + time queries |
| `system_logs_org_created_idx` | `(organization_id, created_at DESC)` | Composite | Tenant-scoped inspection |
| `system_logs_channel_created_idx` | `(channel, created_at DESC)` | Composite | Module-scoped inspection |
| `system_logs_created_at_idx` | `(created_at DESC)` | Single | Retention purge queries |
| `system_logs_level_org_idx` | `(level, organization_id)` | Composite | Error+ per tenant monitoring |

### 4.7 Model Guidance

```php
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected function casts(): array {
        return ['context' => 'array', 'line' => 'integer', 'created_at' => 'datetime'];
    }
}
```

---

## 5. `notifications` — Notification Platform

### 5.1 Ownership

| Attribute | Value |
|---|---|
| **Service** | Notification Platform |
| **Data Category (ADR-005)** | Business Record |
| **Lifecycle** | Mutable status lifecycle; soft delete |
| **Model Base** | `BaseModel` → `HasUuid` + `HasAudit` + `SoftDeletes` |
| **Source Requirements** | `PLATFORM-REQ-NOT-001` through `PLATFORM-REQ-NOT-004` |
| **Source Business Rules** | `PLATFORM-BR-NOT-001` through `PLATFORM-BR-NOT-010` |
| **Source Flow** | `PlatformFlow.md` §5.1, §5.2, §5.3, §5.4 |
| **Source Design Doc** | `NotificationPlatform.md` (lines 52-72) |

### 5.2 Column Inventory

| # | Column | Type | Nullable | Default | Description |
|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | Ordered UUID PK |
| 2 | `organization_id` | `uuid` | NOT NULL | — | Tenant org (BR-NOT-003) |
| 3 | `branch_id` | `uuid` | NULL | — | Tenant branch |
| 4 | `notifiable_type` | `varchar(255)` | NOT NULL | — | Recipient model class |
| 5 | `notifiable_id` | `uuid` | NOT NULL | — | Recipient UUID |
| 6 | `channel` | `varchar(20)` | NOT NULL | — | `NotificationChannel` enum — one channel per row (BR-NOT-004) |
| 7 | `type` | `varchar(100)` | NOT NULL | — | Notification type (e.g. `appointment_reminder`) |
| 8 | `title` | `varchar(255)` | NOT NULL | — | Notification title |
| 9 | `body` | `text` | NOT NULL | — | Body (no secrets per BR-NOT-006) |
| 10 | `data` | `jsonb` | NOT NULL | `'{}'` | Extra payload (no secrets per BR-NOT-006) |
| 11 | `locale` | `varchar(10)` | NULL | `'id'` | Preferred language — from `NotificationMessageDTO` |
| 12 | `status` | `varchar(20)` | NOT NULL | `'pending'` | `NotificationStatus` enum value |
| 13 | `sent_at` | `timestamptz` | NULL | — | Delivery timestamp |
| 14 | `read_at` | `timestamptz` | NULL | — | Read timestamp (in-app only — BR-NOT-005) |
| 15 | `failed_reason` | `text` | NULL | — | Failure reason |
| 16 | `created_by` | `uuid` | NULL | — | Dispatching user (auto: `HasAudit`) |
| 17 | `updated_by` | `uuid` | NULL | — | Last updating user (auto: `HasAudit`) |
| 18 | `deleted_by` | `uuid` | NULL | — | Deleting user (auto: `HasAudit`) |
| 19 | `created_at` | `timestamptz` | NOT NULL | — | Created timestamp |
| 20 | `updated_at` | `timestamptz` | NOT NULL | — | Last update timestamp |
| 21 | `deleted_at` | `timestamptz` | NULL | — | Soft delete timestamp |

**21 columns.** NotificationPlatform.md 16 base fields + `locale` (DTO) + 3 audit columns + `deleted_at`.

### 5.3 Column Evolution from Source Doc

| Source | Change | Rationale |
|---|---|---|
| No `locale` in NotificationPlatform.md | **Added** | Present in `NotificationMessageDTO` (`?string $locale = 'id'`) |
| No audit columns | **Added** (`created_by`, `updated_by`, `deleted_by`) | Required by `HasAudit`/`BaseModel` — Business Record per ADR-005 |
| No soft delete | **Added** (`deleted_at`) | Required by `SoftDeletes`/`BaseModel` — Business Record per ADR-005 |

### 5.4 DTO `channels` (array) vs DB `channel` (single)

The `NotificationMessageDTO` carries a `channels` array (`array<int, NotificationChannel>`), but the DB table stores one `channel` per row. The multi-channel fan-out is an application-level pattern: one `send()` produces N rows (one per channel), each tracked independently through the status lifecycle. This is consistent with `PlatformFlow.md` §5.1: the Queue job loops "For each channel in message.channels" and updates per-channel status.

### 5.5 Notification Status Lifecycle

```
send() → pending → sent (terminal, except in-app → read)
              ↘ failed (terminal)
```

Status transitions enforced at the Service layer. `sent → read` valid only for `channel = 'in_app'` (BR-NOT-005).

### 5.6 CHECK Constraints

```sql
ALTER TABLE notifications ADD CONSTRAINT notifications_channel_check
CHECK (channel IN ('email','whatsapp','sms','push','in_app'));

ALTER TABLE notifications ADD CONSTRAINT notifications_status_check
CHECK (status IN ('pending','sent','failed','read'));
```

### 5.7 Foreign Keys

| Column | References | On Delete | Rationale |
|---|---|---|---|
| `organization_id` | `organizations(id)` | RESTRICT | Notification must belong to existing org |
| `branch_id` | `branches(id)` | SET NULL | Retain notification if branch removed |
| `created_by` | `users(id)` | SET NULL | Retain notification if user deleted |

### 5.8 Indexes

| Name | Columns | Type | Rationale |
|---|---|---|---|
| `notifications_org_status_idx` | `(organization_id, status)` | Composite | Primary: tenant + delivery status |
| `notifications_org_channel_idx` | `(organization_id, channel)` | Composite | Tenant + channel queries |
| `notifications_notifiable_idx` | `(notifiable_type, notifiable_id)` | Composite | Recipient-scoped queries |
| `notifications_status_channel_idx` | `(status, channel)` | Composite | Monitoring: pending/failed per channel |
| `notifications_type_idx` | `(type)` | Single | Notification type queries |
| `notifications_org_created_idx` | `(organization_id, created_at DESC)` | Composite | Time-ordered listing |
| `notifications_org_status_channel_idx` | `(organization_id, status, channel)` | Composite | Tenant failed/sent monitoring |

### 5.9 Model Guidance

```php
use App\Core\Base\BaseModel;

class Notification extends BaseModel
{
    // Inherits: HasUuid, HasAudit, SoftDeletes
    protected function casts(): array {
        return [...parent::casts(), 'data' => 'array', 'sent_at' => 'datetime', 'read_at' => 'datetime'];
    }
}
```

---

## 6. Relationships

### 6.1 Entity Relationship Overview

```text
organizations (1) ──< (M) audit_logs       [organization_id — RESTRICT]
organizations (1) ──< (M) files             [organization_id — RESTRICT]
organizations (1) ──< (M) system_logs       [organization_id — SET NULL, nullable]
organizations (1) ──< (M) notifications      [organization_id — RESTRICT]

branches (1) ──< (M) audit_logs             [branch_id — SET NULL]
branches (1) ──< (M) files                  [branch_id — SET NULL]
branches (1) ──< (M) system_logs            [branch_id — SET NULL, nullable]
branches (1) ──< (M) notifications           [branch_id — SET NULL]

users (1) ──< (M) audit_logs                [user_id — SET NULL]
users (1) ──< (M) files                     [created_by — SET NULL]
users (1) ──< (M) system_logs               [user_id — SET NULL, nullable]
users (1) ──< (M) notifications              [created_by — SET NULL]
```

### 6.2 FK Summary

| Table | Column | References | On Delete | Justification |
|---|---|---|---|---|
| `audit_logs` | `user_id` | `users(id)` | SET NULL | Retain audit evidence |
| `audit_logs` | `organization_id` | `organizations(id)` | RESTRICT | Immutable evidence retains org link |
| `audit_logs` | `branch_id` | `branches(id)` | SET NULL | Retain audit evidence |
| `files` | `organization_id` | `organizations(id)` | RESTRICT | File must belong to existing org |
| `files` | `branch_id` | `branches(id)` | SET NULL | Retain metadata |
| `files` | `created_by` | `users(id)` | SET NULL | Retain metadata |
| `system_logs` | `user_id` | `users(id)` | SET NULL | Retain diagnostic log |
| `system_logs` | `organization_id` | `organizations(id)` | SET NULL | Non-compliance-critical |
| `notifications` | `organization_id` | `organizations(id)` | RESTRICT | Notification must belong to existing org |
| `notifications` | `branch_id` | `branches(id)` | SET NULL | Retain record |
| `notifications` | `created_by` | `users(id)` | SET NULL | Retain record |

**No CASCADE deletes. Every delete is either RESTRICT (for tenant-critical links) or SET NULL (for diagnostic/operational records).**

---

## 7. Lifecycle Classification

| Table | Class | Created | Updated | Deleted | Model Base |
|---|---|---|---|---|---|
| `audit_logs` | **Immutable** | INSERT via Queue job | Never (no `updated_at`) | Never | `Model` + `HasUuid` |
| `files` | **Soft-deletable Business Record** | INSERT via `store()` | Metadata updates | Soft delete → deferred physical purge | `BaseModel` |
| `system_logs` | **Append-only / Retention-purge** | INSERT via Queue (warning+) + sync (file) | Never (no `updated_at`) | Bulk purge after 90 days | `Model` + `HasUuid` |
| `notifications` | **Soft-deletable Business Record** | INSERT via `send()` (status=pending) | Status updates | Soft delete for archival | `BaseModel` |

---

## 8. Tenant / Security Boundaries

| Table | `organization_id` | Nullable? | `branch_id` | Cross-tenant Prevention |
|---|---|---|---|---|
| `audit_logs` | Required | No | Nullable | Query scoped by org_id (BR-AUD-008); primary index leads with org_id |
| `files` | Required | No | Nullable | Query scoped by org_id; path includes org_id (BR-FS-003) |
| `system_logs` | Optional | **Yes** | Nullable | Query scoped when available; null for system contexts (BR-LOG-006) |
| `notifications` | Required | No | Nullable | Query scoped by org_id; channel availability checked per org (BR-NOT-003) |

---

## 9. Transaction Considerations

| Operation | Transaction Boundary | Notes |
|---|---|---|
| Audit recording | **Outside domain transaction** | Post-commit Queue dispatch (BR-AUD-007, AUD-010) |
| File upload | **Within Platform transaction** | Validation + disk write + DB insert atomic |
| Log file write | **Synchronous, no transaction** | Local filesystem |
| Log DB write | **Inside Queue job transaction** | Async via Queue |
| Notification create | **Within Platform transaction** | INSERT + Queue dispatch |
| Notification deliver | **Inside Queue job, per-channel** | Independent per channel |

---

## 10. Requirement → Table → Field Traceability

| Requirement | Table | Key Fields |
|---|---|---|
| PLATFORM-REQ-AUD-001 | `audit_logs` | `action`, `module`, `user_id`, `organization_id`, `auditable_type`, `auditable_id`, `old_value`, `new_value`, `created_at` |
| PLATFORM-REQ-AUD-002 | `audit_logs` | `action` (CHECK enforces 11 AuditAction values) |
| PLATFORM-REQ-FS-001 | `files` | `id`, `stored_name`, `path`, `original_name`, `hash`, `organization_id`, `branch_id` |
| PLATFORM-REQ-FS-002 | `files` | `folder`, `mime_type`, `extension`, `size` |
| PLATFORM-REQ-LOG-001 | `system_logs` | `level`, `message`, `context`, `channel`, `created_at` |
| PLATFORM-REQ-NOT-001 | `notifications` | `status`, `sent_at`, `failed_reason`, `channel`, `type` |
| PLATFORM-REQ-NOT-003 | `notifications` | `read_at` |
| PLATFORM-REQ-X-003 | All | `organization_id`, `branch_id` |

---

## 11. Contract → Database Alignment

| DTO / Enum | Fields | DB Table | Status |
|---|---|---|---|
| `AuditEntryDTO` (12 fields) | All 12 mapped | `audit_logs` | **PASS** |
| `StoredFileDTO` (12 fields) | All 12 mapped | `files` | **PASS** |
| `NotificationMessageDTO` (10 fields) | All 10 mapped including `locale` | `notifications` | **PASS** |
| `AuditAction` (11 values) | CHECK constraint | `audit_logs.action` | **PASS** |
| `LogLevel` (8 values) | CHECK constraint | `system_logs.level` | **PASS** |
| `StorageFolder` (7 values) | CHECK constraint | `files.folder` | **PASS** |
| `StorageDriver` (2 values) | CHECK constraint | `files.disk` | **PASS** |
| `NotificationStatus` (4 values) | CHECK constraint | `notifications.status` | **PASS** |
| `NotificationChannel` (5 values) | CHECK constraint | `notifications.channel` | **PASS** |

---

## 12. Contract Method → Persistence Mapping

| Contract Method | DB Impact | Status |
|---|---|---|
| `AuditServiceInterface::record()` | INSERT `audit_logs` | **PASS** |
| `AuditServiceInterface::log()` | INSERT `audit_logs` | **PASS** |
| `FileStorageServiceInterface::store()` | INSERT `files` | **PASS** |
| `FileStorageServiceInterface::temporaryUrl()` | Read `files` (path lookup) | **PASS** |
| `FileStorageServiceInterface::delete()` | UPDATE `deleted_at` on `files` | **PASS** |
| `LoggerServiceInterface::log()` | INSERT `system_logs` (warning+) | **PASS** |
| `NotificationServiceInterface::send()` | INSERT `notifications` | **PASS** |
| `NotificationServiceInterface::markAsRead()` | UPDATE `read_at`, `status` | **PASS** |
| `NotificationChannelInterface::isAvailableFor()` | Read (org config — external to Platform tables) | **PASS** |
| `NotificationChannelInterface::deliver()` | UPDATE `status`, `sent_at` / `failed_reason` | **PASS** |

---

## 13. Drift Findings

| # | Comparison | Finding | Classification |
|---|---|---|---|
| 1 | `files`: source doc has `created_by` only; BaseModel requires all 3 | Added `updated_by`, `deleted_by` | **RESOLVED** |
| 2 | `notifications`: source doc has no audit columns or soft delete; ADR-005 = Business Record | Added `created_by`, `updated_by`, `deleted_by`, `deleted_at` | **RESOLVED** |
| 3 | `notifications`: `locale` field in `NotificationMessageDTO` but absent from source doc schema | Added `locale` column | **RESOLVED** |
| 4 | `audit_logs`: `organization_id` source doc unclear on nullability; BR-AUD-008 mandates "every record" | Declared NOT NULL | **RESOLVED** |
| 5 | `system_logs`: `organization_id` nullable — consistent with BR-LOG-006 | No conflict | **PASS** |
| 6 | Timestamptz: older migrations use `timestamps()` without tz; Platform uses `timestamptz` | Pre-existing drift in `organizations`/`branches`; not Phase 07 scope | **NOTED** |
| 7 | DTO `channels` (array) vs DB `channel` (single): fan-out at application layer | Consistent with Flow §5.1; one DB row per channel | **PASS** |

**0 unresolved conflicts. 0 blocking issues.**

---

## Governance Record

| Check | Result |
|---|---|
| All persistence requirements covered | ✅ 4 tables for 4 services |
| No unsupported table/field | ✅ All columns trace to source artifacts |
| Nullability explicit for every column | ✅ §2.2, §3.2, §4.2, §5.2 + nullability rationale tables |
| FK behavior explicit for every FK | ✅ §6.2 — all 11 FKs documented |
| Indexes justified for every index | ✅ 23 indexes with documented query rationales |
| Tenant boundaries explicit | ✅ §8 — all 4 tables classified |
| Immutable records remain immutable | ✅ `audit_logs`: no `updated_at`, no `deleted_at` |
| Contracts aligned | ✅ §11-12 — all 9 enums and 10 methods verified |
| Traceability complete | ✅ §10 — requirement→table→field matrix |
| Repository conventions applied | ✅ timestamptz, ordered UUID, CHECK, index prefixes, FK naming |
| No ERD created | ✅ Design doc only |
| No migration created | ✅ Design doc only |
| No frozen Authentication artifact modified | ✅ Confirmed by git diff |
| No ADR/Decision modified | ✅ |
| No AGENTS.md modified | ✅ |
