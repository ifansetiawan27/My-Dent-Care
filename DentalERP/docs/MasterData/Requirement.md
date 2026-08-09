# Phase 09 — Master Data Requirements

**Date:** 2026-08-09
**Phase:** 09 — Master Data
**SDLC Stage:** 01 — Requirement
**Status:** `STEP_09_02_MASTER_DATA_REQUIREMENTS_DRAFT`

**Traceability:**
- Roadmap: `AGENTS.md` FINAL LOCKED (line 332)
- DB Design: `database_design/005_MasterData.md`, `006_MasterDataFoundation.md`
- Seeder Strategy: `docs/MasterDataSeederStrategy.md`
- Existing Code: `app/Domains/MasterData/` (17 modules + base architecture)
- Existing Enums: `app/Core/Enums/` (15 canonical enums)
- Architecture Standards: `docs/Architecture/Standards/`

---

## 1. Purpose

Phase 09 — Master Data establishes the centralized reference/lookup data layer for the DentalERP platform. All shared classification tables, enumerations, geographic data, and configurability features that are consumed by multiple downstream domains reside here.

Master Data is **global** (not scoped to organization/branch), **read-heavy**, **seed-managed**, and **rarely mutated** at runtime.

---

## 2. Scope

### 2.1 In Scope

- 23 reference tables across 6 groups (Geographic, Locale, Demographic, Clinical, Financial, Operational)
- Reusable base architecture (`BaseMasterDataModel`, `BaseMasterDataRepository`, `BaseMasterDataService`)
- Canonical Core Enums (matching existing 15 shared enums)
- Seeder orchestration with idempotent seeding
- REST API for Super Admin / Owner CRUD of Master Data records
- Read endpoints for all authenticated users (global, no tenant scoping)
- Redis caching strategy (read-heavy optimization)

### 2.2 Out of Scope

- Transactional data (patient records, appointments, invoices)
- Domain-specific configuration (clinic settings, user preferences)
- Authentication / authorization engine (Phase 08 — frozen)
- Platform Services (Phase 07 — complete)
- IntegrationHub (Phase 27)
- AI Engine (Phase 28)

---

## 3. Authority

| Artifact | Location | Purpose |
|---|---|---|
| AGENTS.md | `AGENTS.md` | FINAL LOCKED roadmap, Phase 09 definition |
| 005_MasterData.md | `database_design/005_MasterData.md` | 18 table schemas |
| 006_MasterDataFoundation.md | `database_design/006_MasterDataFoundation.md` | 23-table catalog, groups, business rules |
| MasterDataSeederStrategy.md | `docs/MasterDataSeederStrategy.md` | Seeder orchestration, data sources |
| Architecture Standards | `docs/Architecture/Standards/` | Field classification, exposure, lifecycle, audit |
| Existing Core Enums | `app/Core/Enums/` | 15 canonical enums |
| Existing Base Architecture | `app/Domains/MasterData/` | Reusable base classes |

---

## 4. Master Data Boundary

### 4.1 Classification

| Data Type | Definition | In Phase 09? |
|---|---|---|
| **Master Data** | Global reference/lookup tables shared system-wide. Seeded, rarely mutated, read-heavy. | **YES** |
| **Transactional Data** | Business events (appointments, invoices, treatments). Created at runtime, org-scoped. | No — Phase 10+ |
| **Operational Data** | Application state (sessions, locks, queue jobs). Ephemeral, not reference data. | No |
| **Configuration** | System settings (app config, feature flags). Environment-driven. | No |
| **Reference/Lookup** | Same as Master Data — synonym in this context. | **YES** |

### 4.2 Distinction from Core Enums

| Aspect | Master Data Table | Core Enum |
|---|---|---|
| Storage | PostgreSQL table | PHP code |
| Mutability | Runtime (CRUD API for admins) | Deploy-time (code change) |
| Extensibility | Admin adds new rows via UI | Developer adds enum cases |
| Examples | countries, payment_methods, insurance_companies | AppointmentStatus, PaymentStatus, Gender |
| Purpose | Long, configurable lists with metadata | Fixed business logic constants |

Both coexist: the Enum defines canonical business logic behavior. The table provides a configurable superset with display metadata (names, labels, active/inactive status).

---

## 5. Domain Inventory

### Group A — Geographic (Hierarchical)

| # | Table | Parent FK | Data Source | Downstream Consumers |
|---|---|---|---|---|
| 1 | `countries` | — | ISO 3166-1 | Organization, Patient referral |
| 2 | `provinces` | `country_id` | BPS Indonesia | Organization, Branch, Patient |
| 3 | `cities` | `province_id` | BPS Indonesia | Organization, Branch, Patient |
| 4 | `districts` | `city_id` | BPS Indonesia | Patient, Employee |
| 5 | `villages` | `district_id` | BPS + MoHA Indonesia | Patient, Employee |

### Group B — Locale

| # | Table | Data Source | Downstream Consumers |
|---|---|---|---|
| 6 | `currencies` | ISO 4217 | Finance, Billing, Organization |
| 7 | `timezones` | IANA | Organization, Branch, Appointment |
| 8 | `languages` | ISO 639-1 | Organization, Patient preference |
| 9 | `nationalities` | National authorities | Patient, Employee |

### Group C — Demographic

| # | Table | Seed Values | Downstream Consumers |
|---|---|---|---|
| 10 | `genders` | Male, Female | Patient, Employee, HR |
| 11 | `religions` | Islam, Christian, Catholic, Hindu, Buddha, Konghucu | Patient, Employee |
| 12 | `blood_types` | A, B, AB, O (with Rh) | Patient, EMR |
| 13 | `marital_statuses` | Single, Married, Divorced, Widowed | Patient, Employee |

### Group D — Clinical

| # | Table | Downstream Consumers |
|---|---|---|
| 14 | `patient_types` | Patient, Appointment, Billing (BPJS vs General rates) |
| 15 | `doctor_specialties` | Doctor, Appointment |
| 16 | `treatment_categories` | Treatment, Billing |
| 17 | `appointment_statuses` | Appointment (UI-configured labels) |
| 18 | `laboratory_categories` | Laboratory |

### Group E — Financial

| # | Table | Downstream Consumers |
|---|---|---|
| 19 | `payment_methods` | Billing, Appointment, Finance, Payment Gateway |
| 20 | `insurance_companies` | Patient, Billing, Appointment (BPJS/private) |
| 21 | `tax_rates` | Billing, Finance, Procurement |

### Group F — Operational

| # | Table | Downstream Consumers |
|---|---|---|
| 22 | `asset_categories` | Asset |
| 23 | `inventory_categories` | Inventory, Pharmacy, Procurement |

---

## 6. Requirements

### 6.1 Geographic

#### MASTER-REQ-GEO-001 — Countries

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-GEO-001` |
| **Name** | Countries Reference Table |
| **Description** | Provide a global list of countries with ISO 3166-1 codes, phone codes, and localized names. |
| **Business Purpose** | Country selection for organizations, patient nationality/address, and multi-country support. |
| **Data Ownership** | Master Data — global, no tenant scoping. |
| **Lifecycle** | Seeded at setup. Super Admin may add/activate/deactivate. Soft delete only. |
| **Tenant Scope** | Global — no `organization_id` / `branch_id`. |
| **Audit** | `created_by`, `updated_by`, `deleted_by` via `BaseMasterDataModel`. |
| **Authorization** | Read: authenticated users. Write: Super Admin, Owner. |
| **Acceptance Criteria** | Seeded with all ISO 3166-1 countries. Indonesia active by default. CRUD API for Super Admin. |

#### MASTER-REQ-GEO-002 — Provinces

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-GEO-002` |
| **Name** | Provinces Reference Table |
| **Description** | Provinces/states linked to a country. Uses BPS Indonesia codes. |
| **Business Purpose** | Province selection for organizations, branches, patient addresses. |
| **FK** | `country_id` → `countries.id` |
| **Acceptance Criteria** | Seeded with Indonesian provinces. Hierarchical query via `country_id`. |

#### MASTER-REQ-GEO-003 — Cities

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-GEO-003` |
| **Name** | Cities/Regencies Reference Table |
| **FK** | `province_id` → `provinces.id` |
| **Acceptance Criteria** | Seeded with Indonesian cities/regencies (BPS). Hierarchical query. |

#### MASTER-REQ-GEO-004 — Districts

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-GEO-004` |
| **Name** | Districts Reference Table |
| **FK** | `city_id` → `cities.id` |
| **Acceptance Criteria** | Seeded with Indonesian kecamatan data. |

#### MASTER-REQ-GEO-005 — Villages

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-GEO-005` |
| **Name** | Villages Reference Table (Kelurahan/Desa) |
| **FK** | `district_id` → `districts.id` |
| **Note** | Includes postal code column. |
| **Acceptance Criteria** | Seeded with Indonesian desa/kelurahan data. |

### 6.2 Locale

#### MASTER-REQ-LOC-001 — Currencies

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-LOC-001` |
| **Description** | ISO 4217 currencies with symbols and decimal places. |
| **Acceptance Criteria** | IDR as default active. All major currencies seeded. |

#### MASTER-REQ-LOC-002 — Timezones

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-LOC-002` |
| **Description** | IANA timezones. WIB, WITA, WIT as default active. |
| **Acceptance Criteria** | All IANA timezones seeded. Indonesian zones active. |

#### MASTER-REQ-LOC-003 — Languages

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-LOC-003` |
| **Description** | ISO 639-1 languages. |
| **Acceptance Criteria** | All ISO 639-1 languages seeded. Indonesian active. |

#### MASTER-REQ-LOC-004 — Nationalities

| Attribute | Value |
|---|---|
| **ID** | `MASTER-REQ-LOC-004` |
| **Description** | Nationalities for patient/employee records. |
| **Acceptance Criteria** | Common nationalities seeded. Indonesian default. |

### 6.3 Demographic

#### MASTER-REQ-DEM-001 — Genders
**ID:** `MASTER-REQ-DEM-001`. Male, Female seeded. Aligns with `Gender` Core Enum.

#### MASTER-REQ-DEM-002 — Religions
**ID:** `MASTER-REQ-DEM-002`. Islam, Christian, Catholic, Hindu, Buddha, Konghucu seeded per Indonesian MoHA. Aligns with `Religion` Core Enum.

#### MASTER-REQ-DEM-003 — Blood Types
**ID:** `MASTER-REQ-DEM-003`. A, B, AB, O with Rh variants. Aligns with `BloodType` Core Enum.

#### MASTER-REQ-DEM-004 — Marital Statuses
**ID:** `MASTER-REQ-DEM-004`. Single, Married, Divorced, Widowed. Aligns with `MaritalStatus` Core Enum.

### 6.4 Clinical

#### MASTER-REQ-CLN-001 — Patient Types
**ID:** `MASTER-REQ-CLN-001`. General, BPJS, Insurance, VIP, Employee, Child.

#### MASTER-REQ-CLN-002 — Doctor Specialties
**ID:** `MASTER-REQ-CLN-002`. Orthodontist, Periodontist, Oral Surgeon, etc.

#### MASTER-REQ-CLN-003 — Treatment Categories
**ID:** `MASTER-REQ-CLN-003`. Conservative, Endodontic, Periodontic, Prosthodontic, etc.

#### MASTER-REQ-CLN-004 — Appointment Statuses
**ID:** `MASTER-REQ-CLN-004`. UI-configurable labels/colors for appointment states.

#### MASTER-REQ-CLN-005 — Laboratory Categories
**ID:** `MASTER-REQ-CLN-005`. Crown, Denture, Bridge, Implant, etc.

### 6.5 Financial

#### MASTER-REQ-FIN-001 — Payment Methods
**ID:** `MASTER-REQ-FIN-001`. Cash, Transfer, Card, E-wallet, Insurance.

#### MASTER-REQ-FIN-002 — Insurance Companies
**ID:** `MASTER-REQ-FIN-002`. BPJS, Prudential, AXA, Allianz, etc.

#### MASTER-REQ-FIN-003 — Tax Rates
**ID:** `MASTER-REQ-FIN-003`. PPN 11%, PPh 21, etc. with effective dates.

### 6.6 Operational

#### MASTER-REQ-OPR-001 — Asset Categories
**ID:** `MASTER-REQ-OPR-001`. Dental Chair, X-Ray, Sterilizer, etc.

#### MASTER-REQ-OPR-002 — Inventory Categories
**ID:** `MASTER-REQ-OPR-002`. Consumables, Instruments, Medicine, etc.

---

## 7. Cross-Cutting Requirements

### MASTER-REQ-X-001 — Common Base Structure
Every Master Data table inherits from `BaseMasterDataModel`: UUID PK, `code` (unique), `name`, `is_active`, audit columns (`created_by`, `updated_by`, `deleted_by`), timestamps (`created_at`, `updated_at`, `deleted_at` soft delete).

### MASTER-REQ-X-002 — Global Scope
Master Data tables are NOT scoped to `organization_id` or `branch_id`. They are shared reference data across the entire platform.

### MASTER-REQ-X-003 — Soft Delete Only
All Master Data records use soft delete (`deleted_at`). Hard delete is never performed.

### MASTER-REQ-X-004 — Active/Inactive Lifecycle
`is_active = false` records are excluded from selection dropdowns and lists by default. Read API may filter by active status.

### MASTER-REQ-X-005 — Unique Code
Every table has a UNIQUE `code` column used as a machine-readable identifier.

### MASTER-REQ-X-006 — Idempotent Seeding
All seed data uses `firstOrCreate()` and is safe to run multiple times. Seeders load canonical data from ISO/BPS/MoHA/IANA standards.

### MASTER-REQ-X-007 — Audit Trail
All Master Data tables have `created_by`, `updated_by`, `deleted_by` (uuid nullable) populated via `HasAudit` trait inherited from `BaseMasterDataModel`.

### MASTER-REQ-X-008 — Authorization
Read: all authenticated users. Write (create, update, delete): Super Admin and Owner only. All endpoints protected by `permission:master_data.view` and relevant write permissions.

### MASTER-REQ-X-009 — Redis Caching
Master Data tables are read-heavy. Caching strategy uses Redis with cache invalidation on write operations.

### MASTER-REQ-X-010 — Platform Services Integration
Master Data modules use `AuditServiceInterface` and `LoggerServiceInterface` from Phase 07 Platform Services.

### MASTER-REQ-X-011 — API Convention
All endpoints follow the standard `ApiResponse` envelope. Versioned under `/api/v1/master-data/`. OpenAPI 3.1 documented.

### MASTER-REQ-X-012 — Reusable Architecture
All 23 tables reuse `BaseMasterDataModel`, `BaseMasterDataRepository`, `BaseMasterDataService`. Adding a new table requires only Model + Repository + Service extending the base.

---

## 8. Dependency Matrix

| Phase | Domain | Dependency Direction | Purpose |
|---|---|---|---|
| 07 | Platform Services | Master Data → Audit, Logging | Audit trail and operational logging |
| 08 | Authentication | Master Data → Auth (permission) | Authorization via Spatie permissions |
| 03 | Organization | Organization → Master Data | Country, province, city, currency, timezone selection |
| 04 | Branch | Branch → Master Data | Timezone, province/city |
| 05 | User | User → Master Data | Gender, religion (via Core Enum) |
| 10 | Employee | Employee → Master Data | Gender, religion, marital status, nationality |
| 11 | Doctor | Doctor → Master Data | Doctor specialty |
| 12 | Patient | Patient → Master Data | Gender, religion, blood type, marital status, patient type, nationality |
| 13 | Appointment | Appointment → Master Data | Appointment status labels, patient type |
| 14 | EMR | EMR → Master Data | Blood type |
| 17 | Billing | Billing → Master Data | Payment method, tax rate, insurance company |
| 18 | Inventory | Inventory → Master Data | Inventory categories |
| 22 | Asset | Asset → Master Data | Asset categories |
| 27 | Integration Hub | Integration Hub → Master Data | Payment method types (external mapping) |

---

## 9. Downstream Consumers

| Consumer Domain | Master Data Tables Consumed |
|---|---|
| Organization | countries, provinces, cities, currencies, timezones |
| Branch | timezones |
| User | genders, religions (via Core Enum) |
| Patient | genders, religions, blood_types, marital_statuses, patient_types, nationalities |
| Doctor | doctor_specialties |
| Employee | genders, religions, marital_statuses, nationalities |
| Appointment | appointment_statuses, patient_types |
| EMR | blood_types |
| Billing | payment_methods, tax_rates, insurance_companies |
| Finance | payment_methods, currencies |
| Inventory | inventory_categories |
| Asset | asset_categories |
| Laboratory | laboratory_categories |
| Treatment | treatment_categories |

---

## 10. Non-Goals

Phase 09 does NOT implement:

1. Transactional workflows (appointment booking, billing, treatment recording).
2. Patient clinical data (medical history, odontogram, EMR charts).
3. Authentication or authorization engine modifications.
4. IntegrationHub implementation (Phase 27).
5. Domain-specific business rules — domains consume Master Data but own their own logic.
6. Dynamic form generation based on Master Data schemas.
7. Multi-language UI driven by languages table (future enhancement).
8. Audit Platform modifications (Phase 07 is complete).
9. Custom reporting or dashboard analytics.

---

## 11. Open Questions

| # | Question | Status |
|---|---|---|
| 1 | `organization_id` / `branch_id` column on Master Data tables? | **RESOLVED BY EXISTING AUTHORITY** — No. `005_MasterData.md` and `006_MasterDataFoundation.md` both state "Global — not scoped to organization or branch." |
| 2 | Domain-level enums vs Master Data tables — which takes precedence? | **RESOLVED BY EXISTING AUTHORITY** — Core Enum defines canonical business logic. Master Data table provides configurable superset with display metadata. Both coexist. |
| 3 | Should `appointment_statuses` own status lifecycle logic? | **RESOLVED BY EXISTING AUTHORITY** — No. Canonical status logic remains in `AppointmentStatus` Enum. Table provides UI labels/colors only. |
| 4 | Migration for tables already existing as code (17 modules)? | **REQUIRES IMPLEMENTATION DECISION** — 17 modules have Model/Repo/Service code but NO migration files (no DB tables exist). Migration creation is part of SDLC Stage 06. |
| 5 | 6 additional foundation tables (nationalities, treatment_categories, appointment_statuses, laboratory_categories, asset_categories, inventory_categories) | **REQUIRES IMPLEMENTATION DECISION** — These tables have design docs but NO code implementation exists yet. |
| 6 | Domain-level status enums that duplicate Core Enums (OrganizationStatus, BranchStatus, UserStatus) | **OUT OF SCOPE** — Deprecation/migration of domain enums to Core is a Phase 03-05 concern, not Phase 09. |
| 7 | Redis caching strategy implementation | **REQUIRES IMPLEMENTATION DECISION** — Strategy designed but not implemented. Cache invalidation on write operations. |

---

## 12. Traceability

| Requirement | Source |
|---|---|
| MASTER-REQ-GEO-001 through GEO-005 | `005_MasterData.md` lines 25-170 |
| MASTER-REQ-LOC-001 through LOC-004 | `005_MasterData.md` lines 171-270 |
| MASTER-REQ-DEM-001 through DEM-004 | `005_MasterData.md` lines 271-340 |
| MASTER-REQ-CLN-001 through CLN-005 | `005_MasterData.md` lines 341-430; `006_MasterDataFoundation.md` lines 73-81 |
| MASTER-REQ-FIN-001 through FIN-003 | `005_MasterData.md` lines 171-270 (extended); `006_MasterDataFoundation.md` lines 83-95 |
| MASTER-REQ-OPR-001 through OPR-002 | `006_MasterDataFoundation.md` lines 100-110 |
| MASTER-REQ-X-001 through X-008 | `005_MasterData.md` lines 1-14; `006_MasterDataFoundation.md` lines 1-13, 153-165 |
| MASTER-REQ-X-009 (Redis caching) | `006_MasterDataFoundation.md` line 187 |
| MASTER-REQ-X-010 (Platform Services) | Phase 07 contracts |
| MASTER-REQ-X-011 (API convention) | `AGENTS.md` API First standard |
| MASTER-REQ-X-012 (Reusable architecture) | `b3cd576` commit (17 modules) |

---

## 13. Acceptance Criteria

1. All 23 tables have corresponding migrations, models, repositories, and services.
2. All 23 tables have seeders with canonical data from approved sources.
3. Geographic hierarchy (country → province → city → district → village) works correctly.
4. Geographic seeding order respects FK dependencies.
5. All other 18 tables support independent seed ordering (no cross-table FK).
6. `code` column is UNIQUE on every table.
7. `is_active = false` records excluded from dropdown endpoints.
8. Read endpoints available to all authenticated users.
9. Write endpoints restricted to Super Admin and Owner.
10. All endpoints return `ApiResponse` envelope.
11. All business logic in Service layer. No Eloquent queries in Controllers.
12. All tests cover happy path, authorization, validation, tenant isolation (N/A for global), and soft delete.
13. OpenAPI 3.1 documentation complete for all endpoints.
14. Redis caching enabled for read-heavy endpoints.

---

## 14. Change Control

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-08-09 | Platform Architect | Initial Requirements Draft (STEP_09_02) |

---

## Governance Record

| Check | Result |
|---|---|
| All requirements have unique MASTER-REQ-* IDs | ✅ 23 module + 12 cross-cutting = **35 requirements** |
| No duplicate requirement IDs | ✅ |
| All requirements traceable to repository authority | ✅ 005/006 design docs, existing code, AGENTS.md |
| No invented functionality | ✅ All derived from existing design docs |
| Master Data clearly separated from transactional data | ✅ §4.1 |
| Dependencies evidence-based | ✅ §8 — 13 downstream consumer domains identified |
| Tenant boundaries explicitly addressed | ✅ Global scope; no org/branch columns per existing authority |
| Audit/security requirements addressed | ✅ MASTER-REQ-X-007, X-008, X-010 |
| Implementation not started | ✅ Design stage only |
| Files created | `docs/MasterData/Requirement.md` |
| Protected artifacts untouched | ✅ Authentication, ADRs, AGENTS.md, Phase 07 |

STEP_09_02_MASTER_DATA_REQUIREMENTS_DRAFT
