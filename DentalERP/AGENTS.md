# Dental ERP Enterprise

## Role

You are Senior Laravel Enterprise Developer building a multi-branch dental clinic ERP.

This system is designed to scale from 1 to 100+ clinic branches.
Every decision — database, architecture, API, index — must be made with that scale in mind.

---

## Technology

- Laravel 12
- PHP 8.4
- PostgreSQL
- Redis
- Docker

---

## Architecture

- Use Domain Driven Design.
- Use Repository Pattern.
- Use Service Pattern.
- Use SOLID Principles.
- Use Clean Code.

---

## Multi-Branch Enterprise Standards

These standards apply to every file in every domain.
They exist to ensure the system remains fast, consistent, and maintainable at 10–100 branches.

### Database Design

- Every table that belongs to a branch or organization MUST have `organization_id` and/or `branch_id` as indexed foreign keys.
- Never store data that belongs to a branch without scoping it to that branch.
- Composite indexes must be considered for multi-tenant queries — e.g. `(organization_id, status)`, `(branch_id, created_at)`.
- UUID primary keys use `Str::orderedUuid()` for time-ordered B-tree performance.
- All datetime columns use `timestamptz` (timezone-aware) for multi-timezone branch support.
- Status columns always use a PostgreSQL CHECK constraint in addition to application-level validation.
- Default values must be set for `country`, `timezone`, `currency`, and `status` columns.
- Foreign keys must use `RESTRICT` on delete unless explicitly justified otherwise.
- `down()` migration must be rollback-safe — drop child FK constraints before dropping parent tables.

### Query & Performance

- Never query without scoping to `organization_id` or `branch_id` in multi-tenant contexts.
- Paginate all list endpoints — never return unbounded collections.
- Use `select()` to limit columns — never `SELECT *` in production queries.
- Eager load relationships explicitly — never rely on lazy loading in API responses.
- Index every column used in `WHERE`, `ORDER BY`, or `JOIN` clauses.
- Avoid N+1 queries — use `with()` in Repository, not in Service or Controller.

### API Design

- All responses follow the standard ApiResponse envelope (success, message, data, errors, meta).
- Pagination meta must always include: total, per_page, current_page, last_page, from, to.
- Filter, search, sort parameters must be whitelisted — never passed raw to queries.
- API versioning must be considered from the first route (`/api/v1/`).

### Security

- Every authenticated request must be scoped to the user's organization and branch.
- Users must not access data outside their organization boundary.
- FormRequest must validate all input — no raw `$request->all()` passed to Service or Repository.
- Sensitive fields (`password`, `token`, `secret`) must be in `$hidden` on every model.

### Code Consistency

- Enum values are always lowercase strings (e.g. `active`, `inactive`).
- Enum classes live in `app/Domains/{Domain}/Enums/` or `app/Core/Enums/` if shared.
- Every domain follows the same folder structure — no exceptions.
- PHPDoc is required on every class, method, and non-trivial property.
- `declare(strict_types=1)` is required on every PHP file.

---

## Rules

- Controller contains no business logic.
- Business logic only inside Service.
- Repository only communicates with database.
- Use DTO.
- Use FormRequest.
- Use API Resource.
- Use UUID.
- Use Soft Delete.
- Use Audit Trail.
- Use Transactions.
- Every code must be production ready.
- Never create duplicate code.
- Always follow existing architecture.

---

## Module Development Order

Every module MUST be built in this exact order.
Do NOT proceed to the next phase before the current phase passes its checklist.

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

Rules:
- Every endpoint MUST have a complete OpenAPI 3.1 specification.
- Spec files are located at `docs/openapi/paths/{domain}.yaml`.
- Shared schemas live at `docs/openapi/components/schemas/`.
- The main entry point is `docs/openapi/openapi.yaml`.
- Every path must document: summary, description, parameters, request body, responses (200, 201, 400, 401, 403, 404, 422, 500).
- Response schemas must match the actual `ApiResponse` envelope.
- Security scheme: `BearerAuth` (Sanctum token).
- Tags must match the domain name (e.g. `Branch`, `Organization`, `Patient`).
- Never leave `TODO` or empty descriptions in production spec files.

---

## Phase Checklists

### Phase 1 — Migration

- [ ] Table name matches domain name (snake_case plural)
- [ ] UUID primary key using `$table->uuid('id')->primary()`
- [ ] All required columns defined with correct types and lengths
- [ ] Nullable columns explicitly marked
- [ ] Default values set for country, timezone, currency, status
- [ ] Unique constraints defined
- [ ] `organization_id` and/or `branch_id` present and indexed on every tenant-scoped table
- [ ] Composite indexes considered for multi-tenant query patterns
- [ ] Foreign key constraints defined with explicit names and `restrictOnDelete()`
- [ ] Audit columns: created_by, updated_by, deleted_by (uuid nullable)
- [ ] timestamps() present
- [ ] softDeletes() present
- [ ] Status column uses CHECK constraint at DB level (PostgreSQL)
- [ ] down() drops child FK constraints before dropping parent table
- [ ] No typo in column names
- [ ] Every column has a `->comment()`

### Phase 2 — Model

- [ ] Extends BaseModel
- [ ] Correct namespace: App\Domains\{Domain}\Models
- [ ] Table name explicitly defined
- [ ] fillable array is complete and matches migration columns
- [ ] casts defined for enum, boolean, and datetime fields
- [ ] hidden array defined (deleted_at, deleted_by, password)
- [ ] Relationships defined (HasMany, BelongsTo, etc.) with PHPDoc return types
- [ ] Accessors use Laravel 12 Attribute API
- [ ] Query scopes defined for common filters (active, byOrganization, byBranch)
- [ ] newFactory() points to correct factory class
- [ ] No business logic inside model
- [ ] No hardcoded strings — use Enum values

### Phase 3 — Repository Interface

- [ ] Correct namespace: App\Domains\{Domain}\Interfaces
- [ ] Extends or references RepositoryInterface from Core
- [ ] All domain-specific query methods declared
- [ ] Multi-tenant scoping methods declared where applicable
- [ ] Return types explicitly defined
- [ ] PHPDoc on every method

### Phase 4 — Repository

- [ ] Correct namespace: App\Domains\{Domain}\Repositories
- [ ] Extends BaseRepository
- [ ] Implements domain Repository Interface
- [ ] Constructor injects the correct Model
- [ ] searchable columns defined
- [ ] filterable columns defined (including organization_id, branch_id)
- [ ] sortable columns defined
- [ ] All queries scoped to organization/branch where applicable
- [ ] Only communicates with database — no business logic
- [ ] All interface methods implemented

### Phase 5 — Service Interface

- [ ] Correct namespace: App\Domains\{Domain}\Interfaces
- [ ] All public service methods declared
- [ ] Return types explicitly defined
- [ ] PHPDoc on every method

### Phase 6 — Service

- [ ] Correct namespace: App\Domains\{Domain}\Services
- [ ] Extends BaseService
- [ ] Implements domain Service Interface
- [ ] Constructor injects the correct Repository Interface
- [ ] All write operations wrapped in DB transaction
- [ ] Business rules enforced here — not in Model or Controller
- [ ] Multi-branch rules enforced (e.g. cannot delete if has active branches)
- [ ] Throws BusinessException for rule violations
- [ ] Throws NotFoundException when record not found
- [ ] Uses structured logging (logInfo, logWarning, logError)
- [ ] No direct DB queries — all via Repository
- [ ] All interface methods implemented

### Phase 7 — Request (FormRequest)

- [ ] Correct namespace: App\Domains\{Domain}\Requests
- [ ] Extends FormRequest
- [ ] authorize() method defined
- [ ] rules() method defined with strict validation
- [ ] messages() method defined
- [ ] No business logic inside rules

### Phase 8 — Resource (API Resource)

- [ ] Correct namespace: App\Domains\{Domain}\Resources
- [ ] Extends BaseResource
- [ ] toArray() returns all required fields
- [ ] Enum values exposed via ->value and ->label()
- [ ] File fields resolved to full URL via asset()
- [ ] auditFields() included
- [ ] No business logic

### Phase 9 — Controller

- [ ] Correct namespace: App\Domains\{Domain}\Controllers
- [ ] Extends BaseController
- [ ] Constructor injects Service Interface only
- [ ] Each method: validate via FormRequest, call Service, return ApiResponse
- [ ] No business logic
- [ ] No direct DB queries
- [ ] No direct Model access
- [ ] Returns standard ApiResponse for all responses
- [ ] HTTP status codes correct (200, 201, 204, 404, 422, 500)

### Phase 10 — Route

- [ ] Route file located at App\Domains\{Domain}\Routes\api.php
- [ ] Route prefix follows `/api/v1/{domain}` pattern
- [ ] Route names follow: {domain}.index, {domain}.show, {domain}.store, {domain}.update, {domain}.destroy
- [ ] Middleware applied (auth:sanctum, verified organization/branch scope)
- [ ] Route model binding or explicit ID parameter used correctly

### Phase 11 — Test

- [ ] Feature test for each endpoint (index, show, store, update, destroy)
- [ ] Tests cover happy path and error cases
- [ ] Tests cover multi-branch isolation (user cannot access another org's data)
- [ ] Uses RefreshDatabase
- [ ] Uses factory to seed test data
- [ ] Asserts correct HTTP status codes
- [ ] Asserts correct JSON structure
- [ ] Asserts business rule violations return correct error responses

### Phase 12 — OpenAPI Documentation

- [ ] Spec file created at `docs/openapi/paths/{domain}.yaml`
- [ ] All endpoints documented: index, show, store, update, destroy, restore (where applicable)
- [ ] Every path has: summary, description, operationId, tags
- [ ] Parameters documented: path params, query params (search, filter, sort, pagination)
- [ ] Request body documented with required fields and examples
- [ ] All response codes documented: 200, 201, 401, 403, 404, 422, 500
- [ ] Response schema references shared `ApiResponse` envelope
- [ ] Security applied: `BearerAuth` on all protected endpoints
- [ ] `openapi.yaml` updated to include new path file
- [ ] No empty descriptions or TODO placeholders

---

## Quality Gate

Every file must pass ALL of the following before being considered done:

- [ ] Follows AGENTS.md rules
- [ ] Follows Laravel 12 standards
- [ ] Follows project architecture
- [ ] Designed for 10–100 branches — scoping, indexing, and performance considered
- [ ] No business logic in the wrong layer
- [ ] Production ready — no dd(), dump(), hardcoded credentials, or open TODOs
- [ ] OpenAPI documentation written and synced with implementation (Phase 12)
