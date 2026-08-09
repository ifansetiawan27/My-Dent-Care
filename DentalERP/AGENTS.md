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
- Tenant-scoped Business Record tables MUST have `created_by`, `updated_by`, and `deleted_by` (UUID nullable) unless an Accepted Decision/ADR classifies the object under a different primary Data Category with an explicit audit policy.
- `HasAudit` trait auto-fills Business Record audit columns from the authenticated user. Never fill manually.
- Business Records use soft delete (`deleted_at`) by default. Immutable Audit Events, Operational History Projections, Mutable Operational State, Revocable Security Data, and Expiring Security Data follow their Accepted lifecycle policy under ADR-005; hard deletion requires `Hard Deletable`, retention eligibility, Legal Hold evaluation, authorization, and immutable evidence.
- Immutable Audit Events are never removed under current governance and remain canonical compliance evidence. Operational projections, technical logs, and transient state are not substitutes for canonical Audit Events.

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
- Use soft delete on Business Record tables by default; other primary Data Categories follow their Accepted lifecycle policy under ADR-005.
- Use the ADR-005 Data Category and lifecycle policy; Business Records use Audit Trail (`created_by`, `updated_by`, `deleted_by`) and soft delete by default.
- Use DB Transactions on all write operations.
- Every code must be production ready before commit.
- Never create duplicate code — extract to Core if shared.
- Always follow existing architecture — never introduce new patterns without team decision.
- No `dd()`, `dump()`, `var_dump()`, or hardcoded secrets in any file.
- No open `TODO` comments without a linked issue or justification.

---

## Platform Build Roadmap — FINAL (LOCKED)

> **Architectural Decision (Lead Software Architect):**
> We have stopped building *features*. We are building a *Platform*.
> Every step we take from now on MUST be reusable by the entire ERP.
>
> **This roadmap is FINAL and MUST NOT be changed.**
> All future work references the phase numbers below.

```
Phase 00  Project Foundation                     ✅
Phase 01  Core Framework                          ✅
Phase 02  Base Foundation                         ✅
Phase 03  Organization                            ✅
Phase 04  Branch                                  ✅
Phase 05  User                                    ✅
Phase 06  Role & Permission                       ✅
Phase 07  Platform Services                       ← current focus
Phase 08  Authentication
Phase 09  Master Data
Phase 10  Employee
Phase 11  Doctor
Phase 12  Patient
Phase 13  Appointment
Phase 14  EMR
Phase 15  Odontogram
Phase 16  Treatment
Phase 17  Billing
Phase 18  Inventory
Phase 19  Pharmacy
Phase 20  Laboratory
Phase 21  Procurement
Phase 22  Asset
Phase 23  HR
Phase 24  CRM
Phase 25  Reporting
Phase 26  Dashboard
Phase 27  Integration Hub
Phase 28  AI Engine
Phase 29  Deployment
```

**Platform-first principle:**
- Before writing anything domain-specific, ask: *"Can this be reused by other domains?"*
- If yes → build it in `app/Core/` or as a Platform Service (Phase 07).
- If it is truly domain-specific → build it inside the domain, but expose it via interface.
- No domain module may duplicate infrastructure that belongs in the Platform layer.

---

## SDLC Module Workflow — FINAL (LOCKED)

> **Architectural Decision (Lead Software Architect):**
> Every module MUST follow this Software Development Life Cycle in exact order.
> This sequence is FINAL and MUST NOT be changed, skipped, reordered, or merged.

Terminology:
- **Phase** refers only to the platform roadmap (Phase 00–29).
- **SDLC Stage** refers to the 20 mandatory stages below.

```
Stage 01  Requirement
Stage 02  Business Rules
Stage 03  Database Design (ERD)
Stage 04  API Contract (OpenAPI)
Stage 05  Folder Structure
Stage 06  Migration
Stage 07  Model
Stage 08  Repository Interface
Stage 09  Repository
Stage 10  Service Interface
Stage 11  Service
Stage 12  Request
Stage 13  Resource
Stage 14  Policy
Stage 15  Controller
Stage 16  Routes
Stage 17  Feature Test
Stage 18  Unit Test
Stage 19  Documentation
Stage 20  Git Commit
```

## Design and Implementation Separation — MANDATORY

> Design and implementation are separate deliverables and MUST NOT be mixed.

### Design Outputs

Design stages produce documentation only:

- Requirements, business rules, and flow: `docs/`
- Database design and ERD: `database_design/`
- API contract: `docs/openapi/`
- Folder and class architecture: documented in `docs/{Module}/Architecture.md`

During Stages 01–05, do NOT create or modify implementation artifacts in:

- `app/`
- `database/migrations/` or domain migration directories
- `routes/`
- `tests/`

A Flow document remains mandatory as a supporting design artifact. It is written during the design stages and reviewed before implementation, without adding or changing the locked 20-stage sequence.

### Implementation Outputs

Implementation begins at Stage 06 and may produce:

- Application code: `app/`
- Migrations and seeders: `database/` or approved domain migration directories
- Routes: `routes/` or approved domain route directories
- Tests: `tests/`

Implementation MUST conform to the approved Requirement, Business Rules, Flow, ERD, API Contract, and Architecture documents.

---
### SDLC Enforcement Rules

1. A stage starts only after the previous stage is complete and reviewed.
2. Stages 01–05 produce documentation only; no files may be created or modified in `app/`, migration, routes, or tests directories.
3. API behavior is governed by the OpenAPI contract written at Stage 04.
4. Business rules are implemented only in the Service layer and authorized through Policy.
5. Tests must validate the Requirement, Business Rules, and API Contract.
6. Documentation must be synchronized before commit.
7. Git Commit is the final stage, never an intermediate shortcut.
8. Any change to an earlier artifact requires reviewing all dependent later stages.

### Artifact Locations

| Stage | Artifact | Location |
|---|---|---|
| Requirement | `{Module}Requirement.md` | `docs/{Module}/` |
| Business Rules | `{Module}BusinessRule.md` | `docs/{Module}/` or `docs/` |
| Database Design / ERD | `NNN_{Module}.md` | `database_design/` |
| API Contract | `{module}.yaml` | `docs/openapi/paths/` |
| Folder Structure | `{Module}Architecture.md` | `docs/{Module}/` |
| Feature Test | `{Endpoint}Test.php` | `tests/Feature/Domains/{Module}/` |
| Unit Test | `{Class}Test.php` | `tests/Unit/Domains/{Module}/` |
| Flow | `{Module}Flow.md` | `docs/{Module}/` |
| Documentation | Module docs and implementation prompts | `docs/{Module}/`, `prompts/{Module}/` |

---

## SDLC Stage Checklists

### Stage 01 — Requirement

- [ ] Problem, actors, goals, scope, and exclusions are documented.
- [ ] Functional and non-functional requirements are explicit.
- [ ] Multi-organization and multi-branch implications are identified.
- [ ] Acceptance criteria are testable.

### Stage 02 — Business Rules

- [ ] Rules, invariants, permissions, state transitions, and delete guards are documented.
- [ ] Tenant isolation and audit requirements are explicit.
- [ ] Edge cases and failure conditions are defined.
- [ ] No implementation detail replaces a business rule.

### Stage 03 — Database Design (ERD)

- [ ] Tables, UUID keys, columns, types, indexes, and constraints are defined.
- [ ] Relationships and delete behavior are documented.
- [ ] Tenant and audit columns are included where applicable.
- [ ] PostgreSQL performance for 10–100 branches is considered.

### Stage 04 — API Contract (OpenAPI)

- [ ] Every endpoint has method, path, operationId, tags, and security.
- [ ] Parameters, request bodies, response schemas, examples, and error codes are complete.
- [ ] Standard `ApiResponse` envelope is used.
- [ ] OpenAPI is reviewed before implementation starts.

### Stage 05 — Folder Structure

- [ ] Domain boundary and namespaces are defined.
- [ ] Only required folders are documented in the architecture artifact.
- [ ] Reusable concerns are placed in Core or Platform, not duplicated in domains.
- [ ] No physical implementation folders or code are created at this stage.

### Stage 06 — Migration

- [ ] Laravel 12/PostgreSQL conventions are followed.
- [ ] UUID, indexes, FKs, audit columns, timestamps, and soft delete are correct.
- [ ] `down()` is rollback-safe.
- [ ] Migration matches the approved ERD exactly.

### Stage 07 — Model

- [ ] Extends the approved base model/auth model.
- [ ] Fillable, hidden, casts, enums, and relationships match the migration.
- [ ] No business logic exists in the model.
- [ ] PHPDoc and imports are complete and clean.

### Stage 08 — Repository Interface

- [ ] Interface only; no implementation.
- [ ] Strict parameter and return types are complete.
- [ ] Multi-tenant and domain query contracts are explicit.
- [ ] No Eloquent dependency leaks into the contract where avoidable.

### Stage 09 — Repository

- [ ] Implements the repository interface.
- [ ] Contains database access only; no validation or business decisions.
- [ ] Search/filter/sort are whitelisted and queries are tenant-scoped.
- [ ] Queries are efficient and duplicate query patterns are extracted.

### Stage 10 — Service Interface

- [ ] Public business operations are declared with immutable DTO inputs.
- [ ] Strict return types and documented domain exceptions are present.
- [ ] Interface contains no implementation.

### Stage 11 — Service

- [ ] Implements the service interface and injects repository interfaces.
- [ ] Business rules and transactions are implemented here.
- [ ] No direct Eloquent/database queries exist.
- [ ] Logging, audit dispatch, and exception handling are production-ready.

### Stage 12 — Request

- [ ] FormRequest validates input and authorization.
- [ ] Custom messages and attributes are defined.
- [ ] Validated input maps to a DTO.
- [ ] No business logic exists in validation rules.

### Stage 13 — Resource

- [ ] API output matches the OpenAPI schema exactly.
- [ ] Enums expose value and label consistently.
- [ ] Relationships use `whenLoaded()` to prevent N+1 queries.
- [ ] Sensitive fields are never exposed.

### Stage 14 — Policy

- [ ] Authorization rules are centralized in Policy.
- [ ] Organization, branch, ownership, role, and permission boundaries are enforced.
- [ ] Policy contains authorization decisions only, not business or database orchestration.

### Stage 15 — Controller

- [ ] Controller is thin: Request → DTO → Service → Resource/ApiResponse.
- [ ] Service interface only is injected.
- [ ] No business logic, direct model access, or direct database query exists.
- [ ] HTTP status codes match OpenAPI.

### Stage 16 — Routes

- [ ] Versioned `/api/v1` routes and names are consistent.
- [ ] Sanctum, permission, and relevant scope middleware are applied.
- [ ] Route parameters match Controller and OpenAPI definitions.

### Stage 17 — Feature Test

- [ ] Every endpoint covers happy path, validation, auth, authorization, not found, and business failure.
- [ ] Multi-tenant isolation is tested.
- [ ] HTTP status and JSON structure match OpenAPI.

### Stage 18 — Unit Test

- [ ] Service business rules are tested with mocked repositories.
- [ ] Repository behavior is tested against a database where applicable.
- [ ] DTOs, enums, policies, and reusable helpers are tested.
- [ ] Tests are deterministic and independent.

### Stage 19 — Documentation

- [ ] Requirement, Business Rules, ERD, OpenAPI, and implementation docs are synchronized.
- [ ] Every endpoint has Business Rule, OpenAPI entry, and Feature Test.
- [ ] Reusable implementation prompt exists at `prompts/{Module}/{Endpoint}.md` where required.
- [ ] No stale examples, empty descriptions, or undocumented behavior remain.

### Stage 20 — Git Commit

- [ ] Quality Gate and all previous stages pass.
- [ ] `git status`, diff, tests, and staged files are reviewed.
- [ ] Only intended files are staged.
- [ ] Commit message matches repository conventions.

---

## Architecture Governance — Mandatory Across All Domains

This governance applies to Platform, Authentication, Organization, Patient, Appointment, EMR, Odontogram, Finance, Inventory, Procurement, HR, CRM, AI, Laboratory, Asset, Integration Hub, Payment Gateway, SATUSEHAT bridging, Insurance bridging, and every future domain.

### Final Design Lifecycle

```text
Requirement
    ↓
Business Rules
    ↓
Decision Record
    ↓
ADR (when the decision affects multiple domains or the Platform)
    ↓
Database Design
    ↓
ERD
    ↓
API.md
    ↓
OpenAPI
    ↓
Traceability Matrix
    ↓
Full Drift Detection
    ↓
Architecture Review
    ↓
Design Freeze
    ↓
Implementation
    ↓
Testing
    ↓
Deployment
```

Architecture Review is an explicit gate. Design Freeze is forbidden until Architecture Review and Full Drift Detection both PASS.

### Decision Record Standard — Fixed Structure

Every Decision Record MUST use the following section order. Sections cannot be omitted; use `Not Applicable` with a reason when a section genuinely does not apply.

Canonical definitions are maintained in `docs/Architecture/Standards/index.md`. Decision Records MUST use those terms and must not redefine classification vocabulary locally.

```text
Problem
    ↓
Current State
    ↓
Options
    ↓
Decision
    ↓
Field Classification
    ↓
Exposure Classification
    ↓
Lifecycle Semantics
    ↓
Ownership Exceptions
    ↓
Consequences
    ↓
Affected Documents
    ↓
Review Status
    ↓
Traceability
```

Required content:

| Section | Required Content |
|---|---|
| **Problem** | The architecture/design conflict, risk, and question requiring a decision. |
| **Current State** | Existing approved artifacts, implementation state, and known drift. |
| **Options** | Viable alternatives with trade-offs; no false or impossible options. |
| **Decision** | Selected option and explicit final policy. Use `TBD` until reviewed. |
| **Field Classification** | Use `FieldClassification.md`: Core Identity, Tenant Ownership, Business Data, Enrichment Metadata, Lifecycle Generated, Audit Metadata, Sensitive, Secret, Derived. Include type, nullability, invariant, default, and formula where applicable. |
| **Exposure Classification** | Use `ExposureClassification.md`: Public API, Derived Public, Persistence Only, Audit Only, Sensitive, Secret, Excluded. Do not use ambiguous `Internal`. |
| **Lifecycle Semantics** | Creation, activation, transition, rotation, revocation, expiry, deletion, and retention behavior. |
| **Ownership Exceptions** | User/Organization/Branch/Platform ownership, tenant exceptions, and explicit rationale. |
| **Consequences** | Security, performance, migration, compatibility, operational, and testing consequences. |
| **Affected Documents** | Exact canonical paths that must be synchronized. |
| **Review Status** | Architecture, Security, Data, API, Performance, Compliance reviews as applicable; final status must be explicit. |
| **Traceability** | Requirement IDs, Business Rule IDs, ADRs, API operations, data entities, and planned tests. |

### Decision Status and Immutability

- `TBD` and `Proposed` Decision Records may be edited during review.
- `Accepted` Decision Records are immutable in intent and content.
- A material change to an Accepted Decision requires the next unused unique sequential Decision ID (for example `DD-AUTH-017`) and a `Supersedes: DD-AUTH-005` reference.
- Version suffixes such as `-v2`, `-v3`, or `-final` are forbidden.
- The old Decision is marked `Superseded` and links to the replacement.
- Never rewrite accepted history to hide a changed architecture decision.
- Downstream artifact synchronization may reference an Accepted Decision but must not alter it.

### Governance Rules

1. **Sequential Development** — each stage must PASS before the next stage begins.
2. **Downstream Invalidation** — any upstream change invalidates every affected downstream PASS until re-reviewed.
3. **Single Source of Truth** — Requirement owns business need; Business Rules own invariants; Decision Records own scoped design choices; ADRs own accepted cross-domain/platform decisions; Database Design owns persistent structure; API.md owns business API behavior; OpenAPI owns technical API specification; Traceability Matrix owns artifact relationships.
4. **No Orphan Artifacts** — every Requirement must trace to Business Rules, Decision/ADR, data design, API contract, implementation, and tests or be explicitly marked Not Applicable.
5. **Drift Detection** — every change must run the relevant cross-artifact comparisons.
6. **Design Freeze** — requires all artifacts PASS, relevant Decisions Accepted, no active drift, and Architecture Review PASS.
7. **Implementation Rule** — Migration, Model, Repository, Service, Controller, Routes, API implementation, and Tests are forbidden before Design Freeze PASS.
8. **Domain Standardization** — the same governance applies to all current and future domains.
9. **Explicit Gate Result** — every review returns only an explicit status such as `STEP_xxx_PASS` or `STEP_xxx_FAIL`; ambiguous statuses are forbidden.
10. **Architecture Decision Immutability** — Accepted Decision Records and ADRs are superseded, never rewritten.

### Global Architecture Standards

All domains MUST reference the canonical standards in `docs/Architecture/Standards/`:

- `FieldClassification.md`
- `ExposureClassification.md`
- `LifecycleSemantics.md`
- `OwnershipResolution.md`
- `AuditPolicy.md`
- `DecisionLifecycle.md`
- `TraceabilityRules.md`
- `DriftDetection.md`
- `ArchitectureReviewChecklist.md`

Domain-specific documents add context and decisions but cannot redefine these global terms.

### Decision Record Quality Gate

- [ ] Fixed section order is present.
- [ ] Decision status is explicit (`TBD`, `Proposed`, `Accepted`, `Superseded`, or `Rejected`).
- [ ] Field and exposure classifications cover all affected fields or explicitly state Not Applicable.
- [ ] Classifications use canonical terms from `docs/Architecture/Standards/`.
- [ ] Every Derived field includes an explicit formula and canonical source fields.
- [ ] Lifecycle semantics cover every relevant state transition.
- [ ] Allowed mutations are explicit; all unlisted fields inherit the entity default.
- [ ] Ownership/tenant exceptions are explicit.
- [ ] Ownership exceptions use Resolved, Partially Resolved, or Unresolved states.
- [ ] Consequences include security, performance, migration/compatibility, operations, and tests.
- [ ] Affected Documents use canonical paths.
- [ ] Review Status lists every required review and final result.
- [ ] Traceability maps to Requirement, Business Rules, ADR, data entities, API operations, and planned tests.
- [ ] Accepted records have not been edited directly; superseding decisions are used for material changes.
- [ ] Superseding decisions use a new sequential ID; version suffixes are not used.

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
| 6 | **Audit Trail** | Primary Data Category is explicit; immutable evidence is preserved; Business Record audit columns are populated and soft delete is used by default. |
| 7 | **API First** | Response matches ApiResponse envelope. OpenAPI spec written. |
| 8 | **Testable** | Unit + feature tests written and pass. |
| 9 | **Extensible** | New behavior addable without modifying existing code. |
| 10 | **Production Ready** | No dd(), dump(), hardcode, TODO, N+1, or unbounded query. |
| 11 | **SDLC Compliance** | Stages 01–20 completed in exact order; no stage skipped or reordered. |
| 12 | **Drift Detection** | All upstream design artifacts, implementation, tests, and documentation remain mutually consistent. |

## Drift Detection — Mandatory Quality Gate

Drift Detection is required at every stage review and before every Design Freeze, implementation transition, test sign-off, and Git Commit.

Required comparisons:

- [ ] Requirement ↔ Business Rules
- [ ] Requirement ↔ API Contract
- [ ] Business Rules ↔ Flow
- [ ] Business Rules ↔ OpenAPI
- [ ] ERD ↔ Flow
- [ ] ERD ↔ OpenAPI
- [ ] ERD ↔ ADR
- [ ] Flow ↔ OpenAPI
- [ ] ADR ↔ Business Rules
- [ ] ADR ↔ ERD
- [ ] Migration ↔ ERD
- [ ] Model ↔ Migration
- [ ] Repository Interface ↔ Repository
- [ ] Repository ↔ ERD
- [ ] Service Interface ↔ Service
- [ ] Service ↔ Business Rules
- [ ] Request ↔ OpenAPI request schema
- [ ] Resource ↔ OpenAPI response schema
- [ ] Policy ↔ Business Rules and authorization requirements
- [ ] Controller and Routes ↔ OpenAPI operations
- [ ] Feature Tests ↔ OpenAPI and Business Rules
- [ ] Unit Tests ↔ Service, Repository, DTO, Enum, and Policy behavior
- [ ] Documentation ↔ final implementation

Rules:

1. Every comparison must be marked PASS or FAIL with evidence.
2. A change to an upstream artifact invalidates all affected downstream PASS results until they are re-reviewed.
3. A single unresolved drift blocks the current stage, Design Freeze, implementation transition, or commit.
4. Accepted ADRs take precedence over conflicting implementation drafts; material decision changes require a new or superseding ADR.
5. Drift checks must include naming, fields, types, indexes, status values, endpoint count, HTTP behavior, tenant scope, security rules, examples, and traceability IDs.
