# Dental ERP — Enterprise Platform

## Platform Philosophy

We are NOT building CRUD.
We are building an **Enterprise Platform** for multi-branch dental clinic management.

Every line of code must be written with these outcomes in mind:
- A junior developer joining the team tomorrow can understand the codebase immediately.
- A new domain module can be added without touching existing code.
- The platform can scale from 1 clinic to 100+ branches without architectural changes.
- Every API endpoint can be safely consumed by mobile apps, third-party integrations, and SATUSEHAT/BPJS without rework.
- Any bug introduced is caught by tests before it reaches production.

**If a decision does not serve these outcomes, it is the wrong decision.**

---

## Role

You are a **Senior Enterprise Software Architect** building a production-grade dental clinic ERP platform.

Your responsibility is not to write code that works.
Your responsibility is to write code that **lasts, scales, and can be maintained by a team**.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 12 |
| Language | PHP 8.4 |
| Database | PostgreSQL (primary) |
| Cache / Queue | Redis |
| Containerization | Docker |
| API Standard | REST / OpenAPI 3.1 |
| Auth | Laravel Sanctum |
| Permission | Spatie Laravel Permission |
| Testing | Pest / PHPUnit |

---

## Architecture Principles

### 1. Domain Driven Design (DDD)

- The codebase is organized around **business domains**, not technical layers.
- Each domain lives in `app/Domains/{DomainName}/` and owns its full vertical slice.
- Domain boundaries are explicit. Domains communicate only through interfaces, never by importing each other's models directly.
- Ubiquitous language: use business terms in code (e.g. `Patient`, `Appointment`, `Treatment`) — never generic terms like `Item`, `Record`, `Data`.
- Shared infrastructure lives in `app/Core/` and is domain-agnostic.

### 2. SOLID

- **S — Single Responsibility**: Each class has one reason to change. Controller = HTTP. Service = Business. Repository = Data.
- **O — Open/Closed**: New behavior is added by creating new classes, not modifying existing ones. Use interfaces.
- **L — Liskov Substitution**: Any implementation can replace its interface without breaking the system.
- **I — Interface Segregation**: Interfaces are domain-specific. Do not create fat interfaces.
- **D — Dependency Inversion**: Always depend on interfaces, never on concrete classes.

### 3. Clean Architecture

- Dependencies flow inward: Controller → Service → Repository → Model.
- The inner layers (Service, Repository, Model) have no knowledge of HTTP, JSON, or Laravel Request.
- Business rules are in the Service layer. Data access is in the Repository layer.
- DTOs carry data between layers. No raw arrays passed across boundaries.

### 4. Repository Pattern

- Repository is the **only** place that communicates with the database.
- Repository returns domain models or typed collections. Never raw query results.
- Every domain has its own Repository Interface. The concrete Repository implements it.
- Queries must be whitelisted for filter, search, and sort. Never expose raw query params.

### 5. Service Pattern

- Service is the **only** place that contains business logic.
- Service depends on Repository Interface, never on concrete Repository.
- Every write operation is wrapped in a DB transaction.
- Service throws typed exceptions: `BusinessException`, `NotFoundException`.
- Service logs all significant operations with structured context.

---

## Enterprise Standards

These standards are **non-negotiable** and apply to every domain, every file, every commit.

### Multi-Organization & Multi-Branch Data Isolation

- Every tenant-scoped table MUST have `organization_id` (required) and `branch_id` where applicable.
- Every list query MUST be scoped to `organization_id` or `branch_id`. No exceptions.
- Users MUST NOT be able to access data outside their organization boundary.
- Cross-organization data access is a **security vulnerability**, not a bug.
- Composite indexes MUST cover multi-tenant query patterns: `(organization_id, status)`, `(branch_id, created_at)`, etc.

### Audit Trail

- Every write (create, update, delete, restore) MUST be traceable.
- Every tenant-scoped table MUST have: `created_by`, `updated_by`, `deleted_by` (UUID nullable).
- `HasAudit` trait auto-fills these from the authenticated user. Never fill manually.
- Soft delete (`deleted_at`) is MANDATORY on all domain tables. Hard delete is forbidden except via explicit admin action.
- Audit data is never removed. It is the source of truth for compliance.

### API First

- Every module is designed as an API-first consumer. The API contract is defined before the implementation is written.
- Every endpoint is versioned under `/api/v1/`.
- Every response uses the standard `ApiResponse` envelope: `success`, `message`, `data`, `errors`, `meta`.
- OpenAPI 3.1 documentation is written **immediately after** each module is completed.
- Breaking changes to the API contract are NEVER introduced without a version bump.
- All filter, search, and sort parameters are whitelisted. Raw query strings are never passed to queries.

### Testability

- Every Service is tested with mocked Repository (unit test).
- Every Repository is tested with real database (integration test).
- Every Controller endpoint is tested with mocked Service (feature test).
- Every DTO is tested for mapping correctness and immutability.
- Tests cover: happy path, NotFoundException, BusinessException, validation errors, multi-tenant isolation.
- Test factories produce realistic data that matches production scenarios.

### Extensibility

- Every domain exposes its functionality only through its Service Interface.
- Adding a new feature to a domain means adding a new method to the interface and implementing it — not modifying existing methods.
- Events and Listeners are used for cross-domain side effects (e.g. PatientCreated → send welcome notification).
- Configuration is always externalized. No hardcoded URLs, credentials, or environment-specific values in code.
- Every status type is backed by a PHP 8.4 Enum. Never use raw strings for status comparisons.

---

## Naming & Code Conventions

| Element | Convention | Example |
|---|---|---|
| Domain folder | PascalCase | `app/Domains/Patient/` |
| Class name | PascalCase | `PatientService` |
| Interface suffix | `Interface` | `PatientServiceInterface` |
| DTO suffix | `DTO` | `CreatePatientDTO` |
| Enum values | lowercase string | `'active'`, `'inactive'` |
| Route names | `{domain}.{action}` | `patients.index` |
| DB table names | snake_case plural | `patients`, `appointments` |
| DB column names | snake_case | `organization_id`, `branch_code` |
| UUID columns | `orderedUuid()` | always |
| Datetime columns | `timestamptz` | always |
| Status CHECK | PostgreSQL level | always |

---

## PHP Enum Standard

**This is a non-negotiable rule applied to every status and category field in the platform.**

### Why Enum — Not Raw String

Raw strings like `"ACTIVE"`, `"active"`, `1`, `0` are:
- Impossible to autocomplete — typos go undetected until runtime.
- Impossible to refactor safely across the codebase.
- Not type-safe — any string can be passed where a status is expected.
- Invisible to static analysis tools (PHPStan, Psalm).

PHP 8.1+ backed Enums solve all of these problems.

### Placement Rules

| Scope | Location | Example |
|---|---|---|
| Used by **one domain only** | `app/Domains/{Domain}/Enums/` | `BranchStatus`, `UserStatus` |
| Used by **multiple domains** | `app/Core/Enums/` | `Gender`, `PaymentStatus`, `BloodType` |

### Mandatory Enum Structure

Every Enum in this codebase MUST implement:

```php
enum SomeStatus: string
{
    case Active   = 'active';    // lowercase value — matches DB CHECK constraint
    case Inactive = 'inactive';

    // Human-readable label for API responses and UI display
    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    // All valid values as array — used in Rule::in() validation
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

Optional context methods where applicable:
- `canLogin(): bool` — for auth-related status
- `isOperational(): bool` — for organization/branch status
- `isFinal(): bool` — for payment/invoice status

### Usage Rules Per Layer

| Layer | Rule | Example |
|---|---|---|
| **Model `$casts`** | Cast column to Enum class | `'status' => UserStatus::class` |
| **Migration CHECK** | Use enum values in constraint | `CHECK (status IN ('active', 'inactive'))` |
| **FormRequest** | Validate via enum | `Rule::in(UserStatus::values())` |
| **API Resource** | Expose value + label | `'status' => $this->status->value, 'status_label' => $this->status->label()` |
| **Service** | Compare via enum case | `$user->status === UserStatus::Active` |
| **Factory / Seeder** | Use enum value | `UserStatus::Active->value` |

### Forbidden Patterns

```php
// FORBIDDEN — raw string comparison
if ($user->status === 'active') { }
if ($user->status === 1) { }

// FORBIDDEN — raw string in validation
'status' => ['required', 'in:active,inactive']

// FORBIDDEN — raw string in factory or seeder
'status' => 'active'

// CORRECT
if ($user->status === UserStatus::Active) { }
'status' => ['required', Rule::in(UserStatus::values())]
'status' => UserStatus::Active->value
```

### Enum Inventory

**Domain-specific (`app/Domains/{Domain}/Enums/`):**

| Enum | Domain | Values |
|---|---|---|
| `OrganizationStatus` | Organization | `active`, `inactive` |
| `BranchStatus` | Branch | `active`, `inactive` |
| `UserStatus` | User | `active`, `inactive` |
| `UserGender` | User | `male`, `female` |

**Shared (`app/Core/Enums/`):**

| Enum | Used By | Values |
|---|---|---|
| `Gender` | Patient, User (HR) | `male`, `female` |
| `BloodType` | Patient, EMR | `A`, `B`, `AB`, `O` |
| `MaritalStatus` | Patient, HR | `single`, `married`, `divorced`, `widowed` |
| `Religion` | Patient, HR | `islam`, `christian`, `catholic`, `hindu`, `buddha`, `konghucu` |
| `PaymentStatus` | Finance, Appointment | `pending`, `paid`, `partial`, `cancelled`, `refunded` |
| `InvoiceStatus` | Finance | `draft`, `sent`, `paid`, `overdue`, `cancelled` |
| `AppointmentStatus` | Appointment | `scheduled`, `confirmed`, `in_progress`, `completed`, `cancelled`, `no_show` |
| `VisitStatus` | EMR | `open`, `in_progress`, `completed`, `cancelled` |
| `ToothType` | Odontogram, Treatment | `permanent`, `deciduous` |

---

## Layer Responsibilities

```
HTTP Request
    ↓
FormRequest       — Validate input. Authorize. Map to DTO.
    ↓
Controller        — Call Service. Return ApiResponse. No logic.
    ↓
ServiceInterface  — Business contract.
    ↓
Service           — Business rules. Transaction. Logging. Exception.
    ↓
RepositoryInterface — Data access contract.
    ↓
Repository        — Eloquent queries only. No business logic.
    ↓
Model             — Schema. Casts. Relations. Scopes. No logic.
    ↓
Database (PostgreSQL)
```

**Rules that are never broken:**
- Controller does NOT query the database.
- Service does NOT use Eloquent directly.
- Repository does NOT contain business decisions.
- Model does NOT contain `if` statements for business rules.
- DTO is always immutable (`readonly`).

---

## Rules

- Controller contains no business logic.
- Business logic only inside Service.
- Repository only communicates with database.
- Use DTO for all inter-layer data transfer.
- Use FormRequest for all input validation.
- Use API Resource for all output transformation.
- Use UUID (ordered) as primary key.
- Use Soft Delete on all domain tables.
- Use Audit Trail (created_by, updated_by, deleted_by).
- Use DB Transactions on all write operations.
- Every code must be production ready before commit.
- Never create duplicate code — extract to Core if shared.
- Always follow existing architecture — never introduce new patterns without team decision.
- No `dd()`, `dump()`, `var_dump()`, or hardcoded secrets in any file.
- No open `TODO` comments without a linked issue or justification.

---

## Module Development Order

Every module MUST be built in this exact order.
Do NOT proceed to the next phase before the current phase passes its checklist.
A module is not "done" until Phase 12 is complete.

```
Phase 1  →  Migration
Phase 2  →  Model
Phase 3  →  Repository Interface
Phase 4  →  Repository
Phase 5  →  Service Interface
Phase 6  →  Service
Phase 7  →  Request (FormRequest)
Phase 8  →  Resource (API Resource)
Phase 9  →  Controller
Phase 10 →  Route
Phase 11 →  Test
Phase 12 →  OpenAPI Documentation
```

### OpenAPI Documentation Standard

Documentation is written **per module, immediately after Phase 11**.
Do NOT wait until all modules are complete before documenting.

- Every endpoint MUST have a complete OpenAPI 3.1 specification.
- Spec files are at `docs/openapi/paths/{domain}.yaml`.
- Shared schemas live at `docs/openapi/components/schemas/`.
- The main entry point is `docs/openapi/openapi.yaml`.
- Every path must document: summary, description, parameters, request body, all response codes.
- Response schemas must match the actual `ApiResponse` envelope.
- Security scheme: `BearerAuth` (Sanctum token).
- Tags must match the domain name (e.g. `Branch`, `Organization`, `Patient`).
- Never leave `TODO` or empty descriptions in production spec files.

---

## Phase Checklists

### Phase 1 — Migration

- [ ] Table name is snake_case plural matching domain name
- [ ] UUID primary key using `$table->uuid('id')->primary()`
- [ ] All columns defined with correct PostgreSQL types and lengths
- [ ] Nullable columns explicitly marked
- [ ] Default values set for `country`, `timezone`, `currency`, `status`
- [ ] Unique constraints defined
- [ ] `organization_id` and/or `branch_id` present and indexed on every tenant-scoped table
- [ ] Composite indexes cover multi-tenant query patterns
- [ ] FK constraints defined with explicit names and `restrictOnDelete()`
- [ ] Audit columns present: `created_by`, `updated_by`, `deleted_by` (uuid nullable)
- [ ] `timestamps()` present
- [ ] `softDeletes()` present
- [ ] Status column uses PostgreSQL CHECK constraint via `DB::statement()`
- [ ] `down()` drops child FK constraints before dropping parent table
- [ ] No typos in column names
- [ ] Every column has `->comment()`
- [ ] `protected $connection = 'pgsql'` declared

### Phase 2 — Model

- [ ] Extends `BaseModel`
- [ ] Correct namespace: `App\Domains\{Domain}\Models`
- [ ] `$table` explicitly defined
- [ ] `$fillable` complete and matches migration columns
- [ ] `$casts` defined for all enum, boolean, and datetime fields
- [ ] `$hidden` defined: `deleted_at`, `deleted_by`, `password` where applicable
- [ ] Relationships defined (HasMany, BelongsTo) with typed PHPDoc
- [ ] Accessors use Laravel 12 `Attribute` API
- [ ] Query scopes defined for: `active()`, `byOrganization()`, `byBranch()` where applicable
- [ ] `newFactory()` points to the correct factory class
- [ ] No business logic in model
- [ ] No hardcoded strings — all status values use Enum

### Phase 3 — Repository Interface

- [ ] Correct namespace: `App\Domains\{Domain}\Interfaces`
- [ ] Extends `RepositoryInterface` from Core
- [ ] All domain-specific query methods declared
- [ ] Multi-tenant scoping methods declared: `findByOrganization()`, `findByBranch()` where applicable
- [ ] Delete guard check methods declared: `hasUsers()`, `hasPatients()`, etc.
- [ ] Return types explicitly defined on every method
- [ ] PHPDoc on every method

### Phase 4 — Repository

- [ ] Correct namespace: `App\Domains\{Domain}\Repositories`
- [ ] Extends `BaseRepository`
- [ ] Implements domain Repository Interface
- [ ] Constructor injects the correct Model
- [ ] `$searchable` columns defined
- [ ] `$filterable` columns defined (always includes `organization_id`, `branch_id`)
- [ ] `$sortable` columns defined
- [ ] Every list query scoped to `organization_id`/`branch_id`
- [ ] No business logic — pure DB queries only
- [ ] No duplicated code — shared query patterns extracted to private helpers
- [ ] All interface methods implemented
- [ ] `applySearchQuery()` and `hasRelation()` helpers used where applicable

### Phase 5 — Service Interface

- [ ] Correct namespace: `App\Domains\{Domain}\Interfaces`
- [ ] All public service methods declared with DTOs as input
- [ ] Return types explicitly defined
- [ ] `@throws` documented for NotFoundException and BusinessException
- [ ] PHPDoc on every method

### Phase 6 — Service

- [ ] Correct namespace: `App\Domains\{Domain}\Services`
- [ ] Implements domain Service Interface
- [ ] Constructor injects Repository Interface (`private readonly`)
- [ ] All write operations wrapped in `DB::transaction()`
- [ ] Business rules enforced here — NOT in Model or Controller
- [ ] Multi-tenant delete guards enforced before delete
- [ ] Throws `BusinessException` for rule violations
- [ ] Throws `NotFoundException` when record not found
- [ ] Uses `logInfo`, `logWarning`, `logError` with structured context
- [ ] No direct Eloquent queries — all via Repository Interface
- [ ] No duplicated code — private helpers used
- [ ] All interface methods implemented

### Phase 7 — Request (FormRequest)

- [ ] Correct namespace: `App\Domains\{Domain}\Requests`
- [ ] Extends `BaseRequest`
- [ ] `authorize()` checks authentication
- [ ] `rules()` validates all fields with strict rules
- [ ] `attributes()` provides human-readable field names
- [ ] `messages()` provides custom error messages
- [ ] `toDTO()` maps validated input to DTO — Controller calls this
- [ ] No business logic in rules
- [ ] Shared rules extracted to a Concern trait (no duplication between Store/Update)

### Phase 8 — Resource (API Resource)

- [ ] Correct namespace: `App\Domains\{Domain}\Resources`
- [ ] Extends `BaseResource`
- [ ] `toArray()` returns all required fields in consistent order
- [ ] Enum values exposed via `->value` (string) and `->label()` (human-readable)
- [ ] File/logo fields resolved to full URL via `asset()`
- [ ] Relationships returned via `whenLoaded()` — never unconditionally loaded
- [ ] `auditFields()` included from BaseResource
- [ ] No business logic in resource

### Phase 9 — Controller

- [ ] Correct namespace: `App\Domains\{Domain}\Controllers`
- [ ] Extends `BaseController`
- [ ] Constructor injects **Service Interface only** (`private readonly`)
- [ ] Each method: FormRequest → `toDTO()` → Service → ApiResponse
- [ ] No business logic
- [ ] No direct DB queries
- [ ] No direct Model access
- [ ] All responses use `ApiResponse` static methods
- [ ] HTTP status codes: 200 (success), 201 (created), 404 (not found), 422 (business/validation), 500 (server error)
- [ ] `NotFoundException` → `ApiResponse::notFound()`
- [ ] `BusinessException` → `ApiResponse::error(message, code)`
- [ ] `Throwable` → `ApiResponse::serverError()`

### Phase 10 — Route

- [ ] Route file at `app/Domains/{Domain}/Routes/api.php`
- [ ] Prefix follows `/api/v1/{domain}` pattern
- [ ] Route names: `{domain}.index`, `{domain}.show`, `{domain}.store`, `{domain}.update`, `{domain}.destroy`, `{domain}.restore`
- [ ] Middleware: `auth:sanctum` on all routes
- [ ] Middleware: `permission:{domain}.{action}` per route
- [ ] Registration instructions documented in file comment

### Phase 11 — Test

- [ ] **Unit — Service**: all methods tested with mocked Repository
- [ ] **Unit — Repository**: CRUD, search, multi-tenant scoping tested with RefreshDatabase
- [ ] **Unit — DTO**: `toArray()`, defaults, immutability, nullable handling tested
- [ ] **Feature — Controller**: all endpoints tested with mocked Service
- [ ] Tests cover: happy path, NotFoundException, BusinessException, validation errors
- [ ] Tests cover: multi-tenant isolation (user cannot access another org's data)
- [ ] Uses `RefreshDatabase` for DB-dependent tests
- [ ] Uses Factory for test data
- [ ] Asserts correct HTTP status codes AND JSON structure
- [ ] Factory includes states: `active()`, `inactive()`, `forOrganization()`, `forBranch()`

### Phase 12 — OpenAPI Documentation

- [ ] Spec file at `docs/openapi/paths/{domain}.yaml`
- [ ] All endpoints documented: index, show, store, update, destroy, restore
- [ ] Every path has: summary, description, operationId, tags, security
- [ ] Query parameters documented with types, defaults, examples
- [ ] Request body with all fields, required markers, and examples
- [ ] All response codes: 200, 201, 401, 403, 404, 422, 500
- [ ] Response schema references shared `ApiResponse` envelope components
- [ ] `BearerAuth` applied on all protected endpoints
- [ ] `openapi.yaml` updated to reference new path file
- [ ] No empty descriptions or `TODO` placeholders
- [ ] Examples are realistic (Indonesian context: timezone, currency, address)

---

## Quality Gate

Every file must pass **ALL** of the following before it is considered complete.
There are no exceptions. There are no shortcuts.

| # | Gate | Standard |
|---|---|---|
| 1 | **Enterprise Platform** | Solves a real business need. Not just CRUD. |
| 2 | **DDD** | Domain boundary respected. Ubiquitous language used. |
| 3 | **SOLID** | Single responsibility. Interface-based dependencies. |
| 4 | **Clean Architecture** | Correct layer. No cross-layer contamination. |
| 5 | **Multi-Tenant** | Every query scoped to org/branch. No data leakage. |
| 6 | **Audit Trail** | created_by, updated_by, deleted_by populated. Soft delete used. |
| 7 | **API First** | Response matches ApiResponse envelope. OpenAPI spec written. |
| 8 | **Testable** | Unit + feature tests written and pass. |
| 9 | **Extensible** | New behavior addable without modifying existing code. |
| 10 | **Production Ready** | No dd(), dump(), hardcode, TODO, N+1, or unbounded query. |
