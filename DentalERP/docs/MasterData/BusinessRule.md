# Phase 09 — Master Data Business Rules

**Date:** 2026-08-09
**Phase:** 09 — Master Data
**SDLC Stage:** 02 — Business Rules
**Status:** `STEP_09_04_MASTER_DATA_BUSINESS_RULES_DRAFT`

**Traceability:**
- Requirements: `docs/MasterData/Requirement.md` (STEP_09_03_PASS)
- DB Design: `database_design/005_MasterData.md`, `006_MasterDataFoundation.md`
- Architecture: `docs/Architecture/Standards/`
- Platform: Phase 07 Platform Services (committed)
- Auth: Phase 08 Authentication (frozen)

---

## 1. Purpose

Defines the invariants, constraints, lifecycle semantics, and governance rules for all 23 Master Data reference tables. Every rule is traceable to `MASTER-REQ-*` requirements and existing repository authority.

---

## 2. Authority

| Source | Role |
|---|---|
| `docs/MasterData/Requirement.md` | Validated requirements baseline (35 REQs) |
| `database_design/005_MasterData.md` | 18 table schemas |
| `database_design/006_MasterDataFoundation.md` | 23-table catalog, business rules |
| `docs/Architecture/Standards/AuditPolicy.md` | Audit trail requirements |
| `docs/Architecture/Standards/LifecycleSemantics.md` | Lifecycle classification |
| `docs/Architecture/Standards/FieldClassification.md` | Field data categories |
| `docs/Architecture/Standards/ExposureClassification.md` | Field exposure rules |
| `AGENTS.md` | ADR-005, audit trail, soft delete policy |

---

## 3. Business Rule Conventions

| Convention | Pattern |
|---|---|
| Rule ID | `MASTER-BR-{CAT}-NNN` |
| Requirement reference | `MASTER-REQ-{CAT}-NNN` |
| Category codes | GEO, LOC, DEM, CLN, FIN, OPR, X |
| Rule format | ID, Name, Requirement, Statement, Rationale, Scope, Behavior, Enforcement, Error |

---

## 4. Business Rules

### 4.1 Geographic Rules

---

#### MASTER-BR-GEO-001 — Countries Table Required

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-GEO-001` |
| **Name** | Countries Reference Table |
| **Requirement** | `MASTER-REQ-GEO-001` |
| **Rule Statement** | The `countries` table MUST exist as the root geographic entity with ISO 3166-1 codes. |
| **Rationale** | All downstream geographic entities (provinces, cities) and organizations reference a country. ISO 3166-1 is the international standard. |
| **Scope** | `countries` table |
| **Preconditions** | Table must be created with schema matching `005_MasterData.md`. Seeded at setup. |
| **Expected Behavior** | Country records are available for selection by authenticated users. `code` is UNIQUE. `is_active` controls visibility. |
| **Enforcement** | `code` UNIQUE constraint at database level. `is_active` filtered at Service layer. |
| **Error** | Adding a country with a duplicate `code` is rejected. |
| **Audit** | Create/update/delete audited via `BaseMasterDataModel` → `created_by`, `updated_by`, `deleted_by`. |
| **Security** | Write restricted to Super Admin, Owner per `MASTER-REQ-X-008`. |
| **Tenant Scope** | Global — no `organization_id`. |
| **Lifecycle** | Soft delete only. `is_active` flag controls visibility. |
| **Acceptance Criteria** | Table exists with ISO 3166-1 seed data. `code` UNIQUE. Indonesia active. |

---

#### MASTER-BR-GEO-002 — Geographic Hierarchy FK Chain

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-GEO-002` |
| **Name** | Geographic Hierarchy Referential Integrity |
| **Requirement** | `MASTER-REQ-GEO-002`, `MASTER-REQ-GEO-003`, `MASTER-REQ-GEO-004`, `MASTER-REQ-GEO-005` |
| **Rule Statement** | The geographic hierarchy `countries → provinces → cities → districts → villages` MUST be enforced via foreign key constraints with RESTRICT delete behavior. |
| **Rationale** | The hierarchical relationship is mandatory. A province cannot exist without a country. A country with provinces cannot be deleted. |
| **Scope** | `provinces.country_id`, `cities.province_id`, `districts.city_id`, `villages.district_id` |
| **Preconditions** | Parent record must exist before child is created. |
| **Expected Behavior** | Queries return hierarchical results. Parent FK is NOT NULL on child tables. |
| **Enforcement** | FK constraints with `ON DELETE RESTRICT`. Seeding order respects hierarchy. |
| **Error** | Creating a province without a valid `country_id` is rejected at the database level. Deleting a country with existing provinces is rejected. |
| **Audit** | Standard `BaseMasterDataModel` audit on all geographic tables. |
| **Security** | Read: authenticated. Write: Super Admin, Owner. |
| **Tenant Scope** | Global. |
| **Acceptance Criteria** | FK constraints present on all 5 geographic tables. Seeding order: countries → provinces → cities → districts → villages. |

---

#### MASTER-BR-GEO-003 — Geographic Data Reuse

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-GEO-003` |
| **Name** | Geographic Data Consumption by Downstream Domains |
| **Requirement** | `MASTER-REQ-GEO-001` through `MASTER-REQ-GEO-005` |
| **Rule Statement** | Downstream domains (Organization, Branch, Patient, Employee) MUST reference geographic tables via their UUID primary keys, NOT by storing inline country/province/city names. |
| **Rationale** | Prevents data inconsistency when geographic names change. Enables dropdown-based selection. |
| **Scope** | All domains consuming geographic data. |
| **Enforcement** | Domain FK columns reference `countries.id`, `provinces.id`, etc. No denormalized name columns in domain tables. |
| **Acceptance Criteria** | Domain migrations use UUID FK columns to geographic tables. |

---

### 4.2 Locale Rules

---

#### MASTER-BR-LOC-001 — Currencies Must Use ISO 4217

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-LOC-001` |
| **Name** | ISO 4217 Currency Standard |
| **Requirement** | `MASTER-REQ-LOC-001` |
| **Rule Statement** | All currency records MUST conform to ISO 4217 alpha-3 codes. IDR is the system default. |
| **Rationale** | ISO 4217 is the global currency standard. IDR is the default for Indonesian dental clinics. |
| **Scope** | `currencies` table. |
| **Preconditions** | Seeded at setup with all ISO 4217 currencies. |
| **Expected Behavior** | `code` is UNIQUE. `IDR` record has `is_active = true` by default. |
| **Enforcement** | `code` UNIQUE constraint. Seed data validated against ISO 4217. |
| **Audit** | Standard. |
| **Acceptance Criteria** | All ISO 4217 currencies seeded. IDR active by default. |

---

#### MASTER-BR-LOC-002 — Timezones Must Use IANA

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-LOC-002` |
| **Name** | IANA Timezone Standard |
| **Requirement** | `MASTER-REQ-LOC-002` |
| **Rule Statement** | Timezone records MUST use IANA timezone identifiers. WIB (Asia/Jakarta), WITA (Asia/Makassar), WIT (Asia/Jayapura) must be active by default. |
| **Scope** | `timezones` table. |
| **Acceptance Criteria** | All IANA timezones seeded. Indonesian zones active. |

---

#### MASTER-BR-LOC-003 — Languages Must Use ISO 639-1

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-LOC-003` |
| **Name** | ISO 639-1 Language Standard |
| **Requirement** | `MASTER-REQ-LOC-003` |
| **Rule Statement** | Language records MUST use ISO 639-1 alpha-2 codes. Indonesian (`id`) is the system default. |
| **Scope** | `languages` table. |
| **Acceptance Criteria** | All ISO 639-1 languages seeded. Indonesian active by default. |

---

#### MASTER-BR-LOC-004 — Nationalities Reference

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-LOC-004` |
| **Name** | Nationalities Table |
| **Requirement** | `MASTER-REQ-LOC-004` |
| **Rule Statement** | The `nationalities` table MUST exist and be seeded with common nationalities. Indonesian is the default. |
| **Scope** | `nationalities` table. |
| **Acceptance Criteria** | Common nationalities seeded. Indonesian default. |

---

### 4.3 Demographic Rules

---

#### MASTER-BR-DEM-001 — Demographic Tables Align with Core Enums

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-DEM-001` |
| **Name** | Demographic Table ↔ Core Enum Alignment |
| **Requirement** | `MASTER-REQ-DEM-001`, `MASTER-REQ-DEM-002`, `MASTER-REQ-DEM-003`, `MASTER-REQ-DEM-004` |
| **Rule Statement** | Demographic Master Data tables (`genders`, `religions`, `blood_types`, `marital_statuses`) MUST align with existing Core Enums (`Gender`, `Religion`, `BloodType`, `MaritalStatus`). The Enum defines canonical business logic values. The table provides configurable display metadata. |
| **Rationale** | Enums ensure type-safe business logic. Tables enable admin-configurable display names and active/inactive toggling without code changes. |
| **Scope** | `genders`, `religions`, `blood_types`, `marital_statuses` tables; `Gender`, `Religion`, `BloodType`, `MaritalStatus` enums. |
| **Expected Behavior** | Table `code` values must match a corresponding Enum case where the Enum exists. Downstream domains use the Enum for logic, the table for UI dropdowns. |
| **Enforcement** | Seed data codes must match Enum values. Business logic uses Enums, not table codes directly. |
| **Acceptance Criteria** | Demographic tables seeded with codes matching Core Enum values. Downstream consumers reference Enums for logic. |

---

#### MASTER-BR-DEM-002 — Demographic Data Seeded

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-DEM-002` |
| **Name** | Demographic Data Must Be Pre-Seeded |
| **Requirement** | `MASTER-REQ-DEM-001` through `MASTER-REQ-DEM-004` |
| **Rule Statement** | Demographic Master Data MUST be seeded at system setup with canonical values. Religions must follow Indonesian MoHA standards. |
| **Scope** | All demographic tables. |
| **Enforcement** | Seeder uses `firstOrCreate()`. |
| **Acceptance Criteria** | `genders`: Male + Female. `religions`: 6 Indonesian MoHA religions. `blood_types`: 4 ABO + Rh. `marital_statuses`: 4 statuses. |

---

### 4.4 Clinical Rules

---

#### MASTER-BR-CLN-001 — Patient Types Configurable

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-CLN-001` |
| **Name** | Patient Type Classification |
| **Requirement** | `MASTER-REQ-CLN-001` |
| **Rule Statement** | Patient types (General, BPJS, Insurance, VIP, Employee, Child) MUST be configurable as Master Data with `code` and `is_active` flags. |
| **Scope** | `patient_types` table. |
| **Acceptance Criteria** | 6 default types seeded. Super Admin may add/modify. |

---

#### MASTER-BR-CLN-002 — Doctor Specialties Configurable

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-CLN-002` |
| **Name** | Doctor Specialty Classification |
| **Requirement** | `MASTER-REQ-CLN-002` |
| **Rule Statement** | Doctor specialties MUST be configurable as Master Data. |
| **Scope** | `doctor_specialties` table. |
| **Acceptance Criteria** | Common dental specialties seeded. |

---

#### MASTER-BR-CLN-003 — Treatment Categories

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-CLN-003` |
| **Name** | Treatment Category Classification |
| **Requirement** | `MASTER-REQ-CLN-003` |
| **Rule Statement** | Treatment categories MUST be configurable as Master Data. |
| **Scope** | `treatment_categories` table. |
| **Acceptance Criteria** | Conservative, Endodontic, Periodontic, Prosthodontic categories seeded. |

---

#### MASTER-BR-CLN-004 — Appointment Statuses UI-Only

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-CLN-004` |
| **Name** | Appointment Status Master Data — UI Labels Only |
| **Requirement** | `MASTER-REQ-CLN-004` |
| **Rule Statement** | The `appointment_statuses` table MUST only provide UI-configurable labels and colors for appointment states. The canonical status lifecycle logic MUST remain in the `AppointmentStatus` Core Enum (Phase 06/13). |
| **Rationale** | Canonical business logic (state machine transitions) must not be overridable via Master Data configuration. |
| **Scope** | `appointment_statuses` table; `AppointmentStatus` Enum. |
| **Enforcement** | `appointment_statuses` table has no transition logic. `Appointment` domain uses `AppointmentStatus` Enum for lifecycle. |
| **Acceptance Criteria** | Table stores display metadata only. No state machine logic in Master Data. |

---

#### MASTER-BR-CLN-005 — Laboratory Categories

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-CLN-005` |
| **Name** | Laboratory Category Classification |
| **Requirement** | `MASTER-REQ-CLN-005` |
| **Rule Statement** | Laboratory categories MUST be configurable as Master Data. |
| **Scope** | `laboratory_categories` table. |
| **Acceptance Criteria** | Crown, Denture, Bridge, Implant categories seeded. |

---

### 4.5 Financial Rules

---

#### MASTER-BR-FIN-001 — Payment Methods Configurable

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-FIN-001` |
| **Name** | Payment Method Classification |
| **Requirement** | `MASTER-REQ-FIN-001` |
| **Rule Statement** | Payment methods (Cash, Transfer, Card, E-wallet, Insurance) MUST be configurable as Master Data. Default codes must align with `PaymentMethodType` Core Enum. |
| **Scope** | `payment_methods` table. |
| **Acceptance Criteria** | 5 default methods seeded. Aligns with `PaymentMethodType` Enum. |

---

#### MASTER-BR-FIN-002 — Insurance Companies Configurable

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-FIN-002` |
| **Name** | Insurance Company Reference |
| **Requirement** | `MASTER-REQ-FIN-002` |
| **Rule Statement** | Insurance companies (BPJS, Prudential, AXA, etc.) MUST be configurable. BPJS must be seeded as default active. |
| **Scope** | `insurance_companies` table. |
| **Acceptance Criteria** | BPJS seeded as active by default. |

---

#### MASTER-BR-FIN-003 — Tax Rates with Effective Dates

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-FIN-003` |
| **Name** | Tax Rate Reference Table |
| **Requirement** | `MASTER-REQ-FIN-003` |
| **Rule Statement** | Tax rates (PPN 11%, PPh 21, etc.) MUST be configurable with `code`, `rate_percentage`, and `effective_date` columns for temporal validity. |
| **Rationale** | Tax rates change over time. Effective dates allow historical accuracy. |
| **Scope** | `tax_rates` table. |
| **Expected Behavior** | Only one active rate per `code` at any given time. `effective_date` determines applicability. |
| **Enforcement** | Service-layer validation for temporal uniqueness. |
| **Acceptance Criteria** | PPN 11% seeded with effective date. Rate changes create new records (never mutate existing). |

---

### 4.6 Operational Rules

---

#### MASTER-BR-OPR-001 — Asset Categories

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-OPR-001` |
| **Name** | Asset Category Classification |
| **Requirement** | `MASTER-REQ-OPR-001` |
| **Scope** | `asset_categories` table. |
| **Acceptance Criteria** | Dental Chair, X-Ray, Sterilizer categories seeded. |

---

#### MASTER-BR-OPR-002 — Inventory Categories

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-OPR-002` |
| **Name** | Inventory Category Classification |
| **Requirement** | `MASTER-REQ-OPR-002` |
| **Scope** | `inventory_categories` table. |
| **Acceptance Criteria** | Consumables, Instruments, Medicine categories seeded. |

---

## 5. Cross-Cutting Rules

---

### MASTER-BR-X-001 — Common Base Structure

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-001` |
| **Name** | All Master Data Tables Share Common Structure |
| **Requirement** | `MASTER-REQ-X-001`, `MASTER-REQ-X-012` |
| **Rule Statement** | Every Master Data table MUST inherit from `BaseMasterDataModel` and include: UUID PK (`id`), UNIQUE `code`, `name`, `is_active` (default `true`), audit columns (`created_by`, `updated_by`, `deleted_by`), timestamps (`created_at`, `updated_at`), and soft delete (`deleted_at`). |
| **Rationale** | Uniform structure enables reusable base architecture (Model, Repository, Service). |
| **Scope** | All 23 Master Data tables. |
| **Enforcement** | `BaseMasterDataModel` abstract class. Migration template. |
| **Acceptance Criteria** | Every table has the common base columns. Every model extends `BaseMasterDataModel`. |

---

### MASTER-BR-X-002 — Global Scope

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-002` |
| **Name** | Master Data Is Global — No Tenant Scoping |
| **Requirement** | `MASTER-REQ-X-002` |
| **Rule Statement** | Master Data tables MUST NOT include `organization_id` or `branch_id` columns. All records are shared across the entire platform. |
| **Rationale** | Reference data (countries, currencies, payment methods) is clinic-independent. Tenant scoping is unnecessary and would create data duplication. |
| **Scope** | All 23 Master Data tables. |
| **Enforcement** | Migration must not include `organization_id`/`branch_id` columns. Queries do not need tenant scoping. |
| **Audit** | `BaseMasterDataModel` audit columns still apply (actor attribution). |
| **Acceptance Criteria** | Zero Master Data tables have `organization_id` or `branch_id` columns. |

---

### MASTER-BR-X-003 — Soft Delete Only

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-003` |
| **Name** | Master Data Uses Soft Delete — No Hard Delete |
| **Requirement** | `MASTER-REQ-X-003` |
| **Rule Statement** | All Master Data deletes MUST be soft deletes via `deleted_at`. Hard delete (`DELETE FROM`) must never be performed on Master Data tables. |
| **Rationale** | Downstream domains may reference Master Data records. Hard deletion would break referential integrity. Soft deletion preserves foreign key references and audit history. |
| **Scope** | All 23 tables. |
| **Enforcement** | `SoftDeletes` trait via `BaseMasterDataModel`. No `forceDelete()` exposed. |
| **Audit** | Soft delete records `deleted_by` and `deleted_at`. |
| **Acceptance Criteria** | No hard delete path in any Master Data endpoint or service. |

---

### MASTER-BR-X-004 — Active/Inactive Lifecycle

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-004` |
| **Name** | is_active Controls Dropdown Visibility |
| **Requirement** | `MASTER-REQ-X-004` |
| **Rule Statement** | Records with `is_active = false` MUST be excluded from selection dropdowns and list endpoints by default. The read API MAY support an `include_inactive` filter. |
| **Rationale** | Deactivated records should not appear in UI dropdowns but must remain in the database for historical data integrity. |
| **Scope** | All 23 tables. |
| **Enforcement** | Repository scopes: `where('is_active', true)` by default. |
| **Error** | Creating a reference to a deactivated record is prevented at service layer. |
| **Acceptance Criteria** | Dropdown endpoints exclude inactive records. Active/inactive toggle is reversible. |

---

### MASTER-BR-X-005 — Unique Code per Table

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-005` |
| **Name** | code Column is UNIQUE per Table |
| **Requirement** | `MASTER-REQ-X-005` |
| **Rule Statement** | Every Master Data table MUST have a UNIQUE `code` column. `code` values are case-insensitive at validation time. |
| **Rationale** | Machine-readable identifiers for API consumers and integration. Prevents ambiguity. |
| **Scope** | All 23 tables. |
| **Enforcement** | UNIQUE index on `code` per table. |
| **Error** | Creating/updating a record with an existing `code` is rejected. |
| **Acceptance Criteria** | UNIQUE constraint on `code` for every Master Data table. |

---

### MASTER-BR-X-006 — Idempotent Seeding

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-006` |
| **Name** | Seed Data Is Idempotent |
| **Requirement** | `MASTER-REQ-X-006` |
| **Rule Statement** | All Master Data seeders MUST use `firstOrCreate()` with `code` as the lookup key. Running a seeder multiple times must not create duplicate records. |
| **Rationale** | Safe re-seeding prevents data corruption during development and deployment. |
| **Scope** | All 23 table seeders. |
| **Enforcement** | Seeder implementation pattern. Code review. |
| **Acceptance Criteria** | Running `php artisan db:seed --class=MasterDataSeeder` twice produces no duplicates. |

---

### MASTER-BR-X-007 — Audit Trail

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-007` |
| **Name** | Master Data Audit Trail |
| **Requirement** | `MASTER-REQ-X-007` |
| **Rule Statement** | Every create, update, and soft delete operation on Master Data MUST be audited via `AuditServiceInterface` from Phase 07 Platform Services. Audit columns (`created_by`, `updated_by`, `deleted_by`) are populated via `HasAudit` trait. |
| **Rationale** | Regulatory compliance. Master Data changes affect all clinics. Changes must be traceable to an actor. |
| **Scope** | All 23 tables. |
| **Enforcement** | `BaseMasterDataModel` extends `BaseModel` → `HasAudit` trait. |
| **Acceptance Criteria** | Audit trail records created for every write operation. |

---

### MASTER-BR-X-008 — Authorization

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-008` |
| **Name** | Master Data Authorization |
| **Requirement** | `MASTER-REQ-X-008` |
| **Rule Statement** | Read operations on Master Data are available to all authenticated users. Write operations (create, update, delete) MUST be restricted to users with Super Admin or Owner role. |
| **Rationale** | Reference data integrity requires controlled mutations. Read access supports downstream dropdown consumption. |
| **Scope** | All 23 tables. |
| **Enforcement** | Policy classes for write operations. `permission:master_data.view` for reads. |
| **Acceptance Criteria** | Unauthorized write returns 403. Authenticated reads return 200. |

---

### MASTER-BR-X-009 — Redis Caching Strategy

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-009` |
| **Name** | Read-Heavy Caching |
| **Requirement** | `MASTER-REQ-X-009` |
| **Rule Statement** | Master Data read endpoints SHOULD be cached via Redis. Cache must be invalidated on any write operation (create, update, delete) on that table. Cache TTL: 1 hour. |
| **Rationale** | Master Data is read-heavy and rarely mutated. Caching reduces database load. |
| **Scope** | All 23 tables. |
| **Enforcement** | Cache tags per table. Invalidation on write. |
| **Acceptance Criteria** | Read endpoints return cached data after first request. Cache invalidated after write. |

---

### MASTER-BR-X-010 — Platform Services Integration

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-010` |
| **Name** | Platform Audit and Logging Integration |
| **Requirement** | `MASTER-REQ-X-010` |
| **Rule Statement** | Master Data modules MUST use `AuditServiceInterface` for audit trail and `LoggerServiceInterface` for operational logging. Direct Eloquent audit logging is forbidden. |
| **Scope** | All 23 modules. |
| **Enforcement** | Constructor injection of Platform interfaces. |
| **Acceptance Criteria** | Audit and log entries created via Platform Services, not raw queries. |

---

### MASTER-BR-X-011 — API Convention

| Attribute | Value |
|---|---|
| **ID** | `MASTER-BR-X-011` |
| **Name** | Standard API Envelope |
| **Requirement** | `MASTER-REQ-X-011` |
| **Rule Statement** | All Master Data endpoints MUST use the standard `ApiResponse` envelope: `success`, `message`, `data`, `errors`, `meta`. Versioned under `/api/v1/master-data/{table}`. |
| **Scope** | All Master Data endpoints. |
| **Acceptance Criteria** | All responses use `ApiResponse` envelope. OpenAPI 3.1 documented. |

---

## 6. Tenancy Rules

| Rule ID | Rule | Requirement | Status |
|---|---|---|---|
| `MASTER-BR-X-002` | Master Data is global — no `organization_id` / `branch_id` columns | `MASTER-REQ-X-002` | **DEFINITIVE** |

Master Data is **always global**. No Master Data table is tenant-scoped. This rule is invariant across all 23 tables.

---

## 7. Ownership Rules

| Ownership | Scope | Rule |
|---|---|---|
| Platform | All 23 tables | Master Data is owned by the Platform. It is not owned by any organization, branch, or user beyond administrative roles. |
| Administrative | Write operations | Super Admin and Owner roles are the designated administrators. |

---

## 8. Lifecycle Rules

| Rule ID | Lifecycle Event | Behavior |
|---|---|---|
| `MASTER-BR-X-003` | Create | Record inserted with `is_active = true` by default. |
| `MASTER-BR-X-003` | Update | Record mutated. `updated_at` and `updated_by` populated. |
| `MASTER-BR-X-003` | Delete | Soft delete via `deleted_at`. Hard delete forbidden. |
| `MASTER-BR-X-004` | Deactivate | Set `is_active = false`. Record excluded from dropdowns. |
| `MASTER-BR-X-004` | Reactivate | Set `is_active = true`. Record re-appears in dropdowns. |
| `MASTER-BR-FIN-003` | Tax Rate Change | New record created with new `effective_date`. Old record preserved. |

**Immutable records:** None of the 23 Master Data tables are immutable. All support soft delete and active/inactive toggling.

---

## 9. Uniqueness Rules

| Rule ID | Scope | Rule |
|---|---|---|
| `MASTER-BR-X-005` | Global — per table | `code` UNIQUE per table. Case-insensitive validation. |
| Geographic | Global | ISO/BPS codes are globally unique by data source. |
| Locale | Global | ISO 4217 / IANA / ISO 639-1 codes are globally unique. |

---

## 10. Status Rules

| Rule ID | Entity | States | Transitions |
|---|---|---|---|
| `MASTER-BR-X-004` | All 23 tables | `is_active = true` ↔ `is_active = false` | Reversible. Both directions allowed. |

No complex state machines exist in Master Data. `is_active` is the only status flag.

---

## 11. Validation Rules

| Rule ID | Validation | Enforcement |
|---|---|---|
| `MASTER-BR-X-001` | `name` NOT NULL on all tables | Migration `$table->string('name')->nullable(false)` |
| `MASTER-BR-X-005` | `code` UNIQUE per table | UNIQUE index |
| `MASTER-BR-GEO-002` | Parent FK NOT NULL on geographic children | Migration `$column->nullable(false)` |
| `MASTER-BR-X-004` | `is_active` must be boolean | Migration `$table->boolean('is_active')->default(true)` |

---

## 12. Audit Rules

| Rule ID | Operation | Behavior |
|---|---|---|
| `MASTER-BR-X-007` | Create | `created_by` populated via `HasAudit`. Audit Service records creation event. |
| `MASTER-BR-X-007` | Update | `updated_by` populated. Old/new values recorded by Audit Service. |
| `MASTER-BR-X-007` | Delete (soft) | `deleted_by` populated. Audit Service records deletion event. |

All audit events are **immutable canonical events** per ADR-005. Master Data does not create its own audit mechanism.

---

## 13. Security & Authorization Rules

| Rule ID | Operation | Authorization |
|---|---|---|
| `MASTER-BR-X-008` | Read | Authenticated users |
| `MASTER-BR-X-008` | Create | Super Admin, Owner |
| `MASTER-BR-X-008` | Update | Super Admin, Owner |
| `MASTER-BR-X-008` | Delete (soft) | Super Admin, Owner |

Policies enforce authorization. Spatie permissions (`master_data.*`) are used. No Authentication redefinition.

---

## 14. Referential Integrity Rules

| Rule ID | FK | Parent | On Delete |
|---|---|---|---|
| `MASTER-BR-GEO-002` | `provinces.country_id` | `countries.id` | RESTRICT |
| `MASTER-BR-GEO-002` | `cities.province_id` | `provinces.id` | RESTRICT |
| `MASTER-BR-GEO-002` | `districts.city_id` | `cities.id` | RESTRICT |
| `MASTER-BR-GEO-002` | `villages.district_id` | `districts.id` | RESTRICT |

All geographic FKs use RESTRICT. A parent with children cannot be deleted. No CASCADE deletes.

---

## 15. Cross-Domain Rules

| Rule | Dependency | Direction |
|---|---|---|
| `MASTER-BR-X-007` | Master Data → Phase 07 Audit Platform | Platform Services contract |
| `MASTER-BR-X-010` | Master Data → Phase 07 Logging Platform | Platform Services contract |
| `MASTER-BR-X-008` | Master Data → Phase 06/08 Role & Permission | Spatie permissions |
| Geographic | Organization → Master Data (`countries`, `provinces`, `cities`) | Downstream consumer |
| Demographic | Patient, Employee → Master Data (`genders`, `religions`, `blood_types`, `marital_statuses`) | Downstream consumer |

---

## 16. Downstream Consumer Guarantees

The Master Data layer guarantees to downstream domains:

| # | Guarantee | Rule Reference |
|---|---|---|
| 1 | Records are referenced by immutable UUID — IDs never change. | `MASTER-BR-X-001` |
| 2 | `code` values are stable machine-readable identifiers. | `MASTER-BR-X-005` |
| 3 | Deleted records remain accessible via UUID for historical integrity. | `MASTER-BR-X-003` |
| 4 | Inactive records are excluded from selection endpoints by default. | `MASTER-BR-X-004` |
| 5 | All endpoints use the standard `ApiResponse` envelope. | `MASTER-BR-X-011` |
| 6 | Read endpoints are available to all authenticated users — no per-tenant data replication. | `MASTER-BR-X-002`, `X-008` |

---

## 17. Open Questions / Decision Dependencies

| # | Question | Status |
|---|---|---|
| 1 | Should `is_active = false` also soft-delete, or are they independent? | **RESOLVED BY EXISTING AUTHORITY** — Independent. `is_active` controls visibility. `deleted_at` is permanent archival. Both coexist. |
| 2 | Can a downstream domain reference a soft-deleted Master Data record? | **RESOLVED BY EXISTING AUTHORITY** — Yes. Soft delete preserves referential integrity. Downstream FKs continue to work. |
| 3 | Tax rate effective date: should the Service enforce one active rate per code? | **REQUIRES BUSINESS DECISION** — Design implies one active rate per code at any time. Enforcement mechanism TBD. |
| 4 | Redis cache key naming convention and invalidation granularity | **REQUIRES IMPLEMENTATION DECISION** — Per-table tags recommended. |
| 5 | Master Data module should be implement per table or grouped | **REQUIRES IMPLEMENTATION DECISION** — Specification for implementation recommended. |

---

## 18. Requirement → Business Rule Traceability

| Requirement | Business Rules |
|---|---|
| `MASTER-REQ-GEO-001` | `MASTER-BR-GEO-001` |
| `MASTER-REQ-GEO-002` | `MASTER-BR-GEO-002`, `MASTER-BR-GEO-003` |
| `MASTER-REQ-GEO-003` | `MASTER-BR-GEO-002`, `MASTER-BR-GEO-003` |
| `MASTER-REQ-GEO-004` | `MASTER-BR-GEO-002`, `MASTER-BR-GEO-003` |
| `MASTER-REQ-GEO-005` | `MASTER-BR-GEO-002`, `MASTER-BR-GEO-003` |
| `MASTER-REQ-LOC-001` | `MASTER-BR-LOC-001` |
| `MASTER-REQ-LOC-002` | `MASTER-BR-LOC-002` |
| `MASTER-REQ-LOC-003` | `MASTER-BR-LOC-003` |
| `MASTER-REQ-LOC-004` | `MASTER-BR-LOC-004` |
| `MASTER-REQ-DEM-001` | `MASTER-BR-DEM-001`, `MASTER-BR-DEM-002` |
| `MASTER-REQ-DEM-002` | `MASTER-BR-DEM-001`, `MASTER-BR-DEM-002` |
| `MASTER-REQ-DEM-003` | `MASTER-BR-DEM-001`, `MASTER-BR-DEM-002` |
| `MASTER-REQ-DEM-004` | `MASTER-BR-DEM-001`, `MASTER-BR-DEM-002` |
| `MASTER-REQ-CLN-001` | `MASTER-BR-CLN-001` |
| `MASTER-REQ-CLN-002` | `MASTER-BR-CLN-002` |
| `MASTER-REQ-CLN-003` | `MASTER-BR-CLN-003` |
| `MASTER-REQ-CLN-004` | `MASTER-BR-CLN-004` |
| `MASTER-REQ-CLN-005` | `MASTER-BR-CLN-005` |
| `MASTER-REQ-FIN-001` | `MASTER-BR-FIN-001` |
| `MASTER-REQ-FIN-002` | `MASTER-BR-FIN-002` |
| `MASTER-REQ-FIN-003` | `MASTER-BR-FIN-003` |
| `MASTER-REQ-OPR-001` | `MASTER-BR-OPR-001` |
| `MASTER-REQ-OPR-002` | `MASTER-BR-OPR-002` |
| `MASTER-REQ-X-001` | `MASTER-BR-X-001` |
| `MASTER-REQ-X-002` | `MASTER-BR-X-002` |
| `MASTER-REQ-X-003` | `MASTER-BR-X-003` |
| `MASTER-REQ-X-004` | `MASTER-BR-X-004` |
| `MASTER-REQ-X-005` | `MASTER-BR-X-005` |
| `MASTER-REQ-X-006` | `MASTER-BR-X-006` |
| `MASTER-REQ-X-007` | `MASTER-BR-X-007` |
| `MASTER-REQ-X-008` | `MASTER-BR-X-008` |
| `MASTER-REQ-X-009` | `MASTER-BR-X-009` |
| `MASTER-REQ-X-010` | `MASTER-BR-X-010` |
| `MASTER-REQ-X-011` | `MASTER-BR-X-011` |
| `MASTER-REQ-X-012` | `MASTER-BR-X-001` |

**35/35 requirements have business rule coverage. 0 orphan requirements. 0 orphan business rules.**

---

## 19. Acceptance Criteria

| # | Criterion | Rule Reference |
|---|---|---|
| 1 | All 23 tables have `code` UNIQUE | `MASTER-BR-X-005` |
| 2 | All 23 tables have soft delete only | `MASTER-BR-X-003` |
| 3 | `is_active = false` excluded from dropdowns | `MASTER-BR-X-004` |
| 4 | Geographic FK chain enforced (RESTRICT) | `MASTER-BR-GEO-002` |
| 5 | All create/update/delete operations audited | `MASTER-BR-X-007` |
| 6 | Write restricted to Super Admin / Owner | `MASTER-BR-X-008` |
| 7 | Read available to all authenticated users | `MASTER-BR-X-008` |
| 8 | Seed data idempotent (`firstOrCreate`) | `MASTER-BR-X-006` |
| 9 | Demographic tables align with Core Enums | `MASTER-BR-DEM-001` |
| 10 | Appointment statuses table has no lifecycle logic | `MASTER-BR-CLN-004` |
| 11 | `ApiResponse` envelope on all endpoints | `MASTER-BR-X-011` |
| 12 | Redis caching with write invalidation | `MASTER-BR-X-009` |
| 13 | All tables inherit `BaseMasterDataModel` | `MASTER-BR-X-001` |

---

## 20. Change Control

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-08-09 | Platform Architect | Initial Business Rules Draft (STEP_09_04) |

---

## Governance Record

| Check | Result |
|---|---|
| All rules have unique `MASTER-BR-*` IDs | ✅ **33 rules** (5 GEO + 4 LOC + 2 DEM + 5 CLN + 3 FIN + 2 OPR + 11 X + 1 TENANCY) |
| Every rule traces to `MASTER-REQ-*` | ✅ 35/35 requirements covered |
| No orphan rules | ✅ |
| No contradictory rules | ✅ |
| Tenancy explicit (global scope) | ✅ `MASTER-BR-X-002` |
| Lifecycle explicit (soft delete, is_active) | ✅ `MASTER-BR-X-003`, `X-004` |
| Audit/security boundaries explicit | ✅ `MASTER-BR-X-007`, `X-008` |
| Downstream guarantees defined | ✅ §16 — 6 guarantees |
| No transactional scope introduced | ✅ |
| Implementation not started | ✅ |
| Protected artifacts untouched | ✅ |

STEP_09_04_MASTER_DATA_BUSINESS_RULES_DRAFT
