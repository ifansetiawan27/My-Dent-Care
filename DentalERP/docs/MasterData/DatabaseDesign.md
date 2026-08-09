# Phase 09 — Master Data Database Design

**Date:** 2026-08-09
**Phase:** 09 — Master Data
**SDLC Stage:** 04 — Database Design
**Status:** `STEP_09_08_MASTER_DATA_DATABASE_DESIGN_DRAFT`

**Traceability:**
- Requirements: `docs/MasterData/Requirement.md` (STEP_09_03_PASS)
- Business Rules: `docs/MasterData/BusinessRule.md` (STEP_09_05_PASS)
- Flow: `docs/MasterData/Flow.md` (STEP_09_07_PASS)
- Source Schemas: `database_design/005_MasterData.md` (18 tables), `006_MasterDataFoundation.md` (23-table catalog)
- Conventions: `AGENTS.md`, `app/Core/Base/BaseModel.php`

---

## 1. Entity Inventory

### 1.1 Service Ownership

| Domain | Phase |
|---|---|
| **Master Data** | 09 |

All 23 tables belong exclusively to the Master Data domain.

### 1.2 Entity Catalog (23 Tables)

| # | Table | Group | Classification | FK Parent |
|---|---|---|---|---|
| 1 | `countries` | Geographic (A) | Core Master Data | — |
| 2 | `provinces` | Geographic (A) | Core Master Data | `countries.id` |
| 3 | `cities` | Geographic (A) | Core Master Data | `provinces.id` |
| 4 | `districts` | Geographic (A) | Core Master Data | `cities.id` |
| 5 | `villages` | Geographic (A) | Core Master Data | `districts.id` |
| 6 | `currencies` | Locale (B) | Reference Data | — |
| 7 | `timezones` | Locale (B) | Reference Data | — |
| 8 | `languages` | Locale (B) | Reference Data | — |
| 9 | `nationalities` | Locale (B) | Reference Data | — |
| 10 | `genders` | Demographic (C) | Reference Data | — |
| 11 | `religions` | Demographic (C) | Reference Data | — |
| 12 | `blood_types` | Demographic (C) | Reference Data | — |
| 13 | `marital_statuses` | Demographic (C) | Reference Data | — |
| 14 | `patient_types` | Clinical (D) | Reference Data | — |
| 15 | `doctor_specialties` | Clinical (D) | Reference Data | — |
| 16 | `treatment_categories` | Clinical (D) | Reference Data | — |
| 17 | `appointment_statuses` | Clinical (D) | Reference Data | — |
| 18 | `laboratory_categories` | Clinical (D) | Reference Data | — |
| 19 | `payment_methods` | Financial (E) | Reference Data | — |
| 20 | `insurance_companies` | Financial (E) | Reference Data | — |
| 21 | `tax_rates` | Financial (E) | Reference Data | — |
| 22 | `asset_categories` | Operational (F) | Reference Data | — |
| 23 | `inventory_categories` | Operational (F) | Reference Data | — |

---

## 2. Common Base Structure

All 23 tables share a **uniform base structure** defined by `BaseMasterDataModel` (`BR-X-001`).

### 2.1 Base Columns (every table)

| # | Column | PostgreSQL Type | Nullable | Default | Description |
|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | Primary key — ordered UUID (`Str::orderedUuid()`) |
| 2 | `code` | `varchar(N)` | NOT NULL | — | Machine-readable identifier — UNIQUE per table |
| 3 | `name` | `varchar(N)` | NOT NULL | — | Display name |
| 4 | `is_active` | `boolean` | NOT NULL | `true` | Controls dropdown visibility (`BR-X-004`) |
| 5 | `created_by` | `uuid` | NULL | — | Actor UUID — auto-populated via `HasAudit` |
| 6 | `updated_by` | `uuid` | NULL | — | Auto-populated via `HasAudit` |
| 7 | `deleted_by` | `uuid` | NULL | — | Auto-populated via `HasAudit` on soft delete |
| 8 | `created_at` | `timestamptz` | NOT NULL | — | Creation timestamp |
| 9 | `updated_at` | `timestamptz` | NOT NULL | — | Last update timestamp |
| 10 | `deleted_at` | `timestamptz` | NULL | — | Soft delete timestamp (`BR-X-003`) |

**10 base columns present on every table.**

### 2.2 Base Constraints

| Constraint | Every Table |
|---|---|
| PK | `id` UUID primary key |
| UNIQUE | `code` unique index |
| NOT NULL | `id`, `code`, `name`, `is_active`, `created_at`, `updated_at` |
| DEFAULT | `is_active = true` |

### 2.3 Base Indexes

| Index | Columns | Purpose |
|---|---|---|
| `{table}_code_unique` | `(code)` | UNIQUE — machine lookup (`BR-X-005`) |
| `{table}_is_active_idx` | `(is_active)` | Active record filtering (`BR-X-004`) |

---

## 3. Per-Table Unique Columns

### 3.1 Group A — Geographic (Hierarchical)

#### `countries`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `name_local` | `varchar(100)` NULL | Localized country name |
| 12 | `phone_code` | `varchar(10)` NULL | International dialing code |

**Total cols:** 12 (10 base + 2 unique). **Source:** `005_MasterData.md` lines 25-44.

#### `provinces`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `country_id` | `uuid` NOT NULL | FK → `countries(id)` ON DELETE RESTRICT |

**Total cols:** 11. **Source:** `005_MasterData.md` lines 47-65.

#### `cities`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `province_id` | `uuid` NOT NULL | FK → `provinces(id)` ON DELETE RESTRICT |

**Total cols:** 11. **Source:** `005_MasterData.md`.

#### `districts`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `city_id` | `uuid` NOT NULL | FK → `cities(id)` ON DELETE RESTRICT |

**Total cols:** 11.

#### `villages`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `district_id` | `uuid` NOT NULL | FK → `districts(id)` ON DELETE RESTRICT |
| 12 | `postal_code` | `varchar(10)` NULL | Postal code |

**Total cols:** 12.

### 3.2 Group B — Locale

#### `currencies`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `symbol` | `varchar(10)` NULL | Currency symbol (e.g. `Rp`) |
| 12 | `decimal_places` | `smallint` NOT NULL DEFAULT `2` | Decimal precision |

#### `timezones`

| # | Column | Type | Extra |
|---|---|---|---|
| 11 | `offset_utc` | `varchar(10)` NULL | UTC offset (e.g. `+07:00`) |

#### `languages`

**Base columns only** (10).

#### `nationalities`

**Base columns only** (10).

### 3.3 Group C — Demographic

All 4 demographic tables (`genders`, `religions`, `blood_types`, `marital_statuses`) are **base-only** (10 columns each). Aligned with Core Enums (`BR-DEM-001`).

### 3.4 Group D — Clinical

| Table | Extra Columns |
|---|---|
| `patient_types` | Base-only (10 cols) |
| `doctor_specialties` | Base-only (10 cols) |
| `treatment_categories` | Base-only (10 cols) |
| `appointment_statuses` | `label_color` `varchar(20)` NULL — UI color hex |
| `laboratory_categories` | Base-only (10 cols) |

### 3.5 Group E — Financial

| Table | Extra Columns |
|---|---|
| `payment_methods` | Base-only (10 cols) |
| `insurance_companies` | `contact_info` `text` NULL |
| `tax_rates` | `rate_percentage` `decimal(5,2)` NOT NULL — Tax percentage; `effective_date` `date` NULL — Date when rate becomes effective (`BR-FIN-003`) |

### 3.6 Group F — Operational

Both `asset_categories` and `inventory_categories` are **base-only** (10 columns each).

---

## 4. Primary Key Strategy

| Attribute | Value |
|---|---|
| Type | `uuid` |
| Generation | `Str::orderedUuid()` via `HasUuid` trait in `BaseMasterDataModel` |
| External exposure | Yes — used in API paths `/api/v1/master-data/{table}/{id}` |
| Rationale | Consistent with all existing project tables (AGENTS.md convention) |

---

## 5. Foreign Key Strategy

| FK | Child | Parent | On Delete | Nullable | Scope Check |
|---|---|---|---|---|---|
| 1 | `provinces.country_id` | `countries.id` | RESTRICT | NOT NULL | ✅ Both global |
| 2 | `cities.province_id` | `provinces.id` | RESTRICT | NOT NULL | ✅ Both global |
| 3 | `districts.city_id` | `cities.id` | RESTRICT | NOT NULL | ✅ Both global |
| 4 | `villages.district_id` | `districts.id` | RESTRICT | NOT NULL | ✅ Both global |

**4 FKs total. 0 CASCADE. All RESTRICT. All global scope (consistent — no tenant cross-referencing).**

No FKs from non-geographic tables to any other table — all 18 non-geographic tables are independent.

---

## 6. Organization / Branch Scope

| Decision | Authority |
|---|---|
| **No `organization_id` column** on any Master Data table | `MASTER-REQ-X-002`, `MASTER-BR-X-002`, `005_MasterData.md` line 13 |
| **No `branch_id` column** on any Master Data table | Same authority |
| Scope = **Global** | All 23 tables share this invariant |

---

## 7. Unique Constraints

| Table(s) | Constraint | Scope | Authority |
|---|---|---|---|
| All 23 | `code` UNIQUE | Per-table, global | `MASTER-BR-X-005`, `005_MasterData.md` |

No composite uniqueness across tables. No organization-scoped uniqueness (global scope).

---

## 8. Index Strategy

### 8.1 Base Indexes (every table)

| Index | Columns | Type | Purpose |
|---|---|---|---|
| `{table}_code_unique` | `(code)` | UNIQUE | Machine lookup, uniqueness enforcement |
| `{table}_is_active_idx` | `(is_active)` | B-tree | Filter active/inactive records |

### 8.2 Geographic-Specific Indexes

| Table | Index | Columns | Type | Purpose |
|---|---|---|---|---|
| `provinces` | `provinces_country_id_idx` | `(country_id)` | B-tree | Cascading dropdown |
| `cities` | `cities_province_id_idx` | `(province_id)` | B-tree | Cascading dropdown |
| `districts` | `districts_city_id_idx` | `(city_id)` | B-tree | Cascading dropdown |
| `villages` | `villages_district_id_idx` | `(district_id)` | B-tree | Cascading dropdown |

**Total: (23 × 2 base) + 4 geographic = 50 indexes.** Every index has a documented query rationale.

---

## 9. Lifecycle / Status Strategy

| Aspect | Implementation |
|---|---|
| Active/Inactive | `is_active` `boolean` — `true` by default. Toggle at service layer (`BR-X-004`) |
| Soft Delete | `deleted_at` `timestamptz` — set on delete. `SoftDeletes` trait via `BaseMasterDataModel` (`BR-X-003`) |
| Hard Delete | Never performed |
| Tax Rate Versioning | New record on rate change. Old record preserved (`BR-FIN-003`) |

---

## 10. Timestamp Strategy

| Column | Type | Convention |
|---|---|---|
| `created_at` | `timestamptz` | Standard — set on creation |
| `updated_at` | `timestamptz` | Standard — set on update |
| `deleted_at` | `timestamptz` | Soft delete — set on delete |
| `created_by` | `uuid` NULL | Auto-populated via `HasAudit` |
| `updated_by` | `uuid` NULL | Auto-populated via `HasAudit` |
| `deleted_by` | `uuid` NULL | Auto-populated via `HasAudit` |

All datetime columns use `timestamptz` — consistent with repository standard.

---

## 11. Soft-Delete Strategy

| Entity | Soft Delete? | Authority |
|---|---|---|
| **All 23 tables** | **Yes** — `deleted_at` via `SoftDeletes` trait | `MASTER-BR-X-003`, `005_MasterData.md` line 10 |

Implications:
- `code` UNIQUE must account for soft-deleted records (database UNIQUE constraint includes `WHERE deleted_at IS NULL` for true uniqueness checking)
- Queries exclude soft-deleted records by default via `SoftDeletes` scope
- FK integrity preserved — downstream domains can continue to reference soft-deleted records

---

## 12. Referential Integrity

| Relationship | Constraint |
|---|---|
| `countries → provinces` | FK NOT NULL, RESTRICT on delete |
| `provinces → cities` | FK NOT NULL, RESTRICT on delete |
| `cities → districts` | FK NOT NULL, RESTRICT on delete |
| `districts → villages` | FK NOT NULL, RESTRICT on delete |
| All other 18 tables | Independent — no FKs to other Master Data tables |

No CASCADE deletes. Application-level pre-check provides user-friendly 409 before database RESTRICT fires.

---

## 13. Enum / Code Strategy

| Aspect | Decision |
|---|---|
| DB representation | `varchar(N)` — string codes, not native PostgreSQL ENUM |
| Validation | Service layer validates against Core Enums where applicable |
| Demographic alignment | Table `code` values match Core Enum case values (`BR-DEM-001`) |
| Canonical vocabulary | Derived from `app/Core/Enums/` and international standards |

---

## 14. Nullability Summary

| Column Group | NULL? | Reason |
|---|---|---|
| `id`, `code`, `name`, `is_active` | NOT NULL | Mandatory business state |
| `created_at`, `updated_at` | NOT NULL | Always populated |
| `created_by`, `updated_by`, `deleted_by` | NULL | System/seed operations may not have an actor |
| `deleted_at` | NULL | Only set on soft delete |
| Geographic extra columns | NULL | Not all data sources provide complete metadata |
| `rate_percentage`, `effective_date` | NOT NULL (tax rates) | Mandatory for tax calculation |

---

## 15. Default Values

| Column | Default | Authority |
|---|---|---|
| `is_active` | `true` | `MASTER-BR-X-004` — records are active by default |
| `decimal_places` (currencies) | `2` | ISO 4217 convention |

---

## 16. Transaction Integrity

| Operation | Tables Modified | Transaction? |
|---|---|---|
| Create/Update | Single table | Yes — `DB::transaction()` around persistence |
| Delete (Soft) | Single table | Yes — `DB::transaction()` around soft delete |
| FK pre-check for delete | Read dependent table(s) | Inside transaction — lightweight `SELECT COUNT(*)` |
| Cache flush | Redis | Outside transaction |
| Audit | `audit_logs` | Outside transaction — post-commit Queue |

Single-table mutations only (no multi-table writes needed). FK pre-checks are idempotent reads.

---

## 17. Concurrency Considerations

| Scenario | Handling |
|---|---|
| Duplicate `code` creation | UNIQUE constraint rejects at database level |
| Concurrent toggle of `is_active` | Last-write-wins — idempotent (boolean toggle) |
| Concurrent delete + update | Optimistic locking not required (reference data, low contention) |

---

## 18. Platform Service Relationships

| Platform Service | Master Data Usage |
|---|---|
| **Audit** | All write operations record via `AuditServiceInterface` |
| **Logging** | `LoggerServiceInterface::info()` for normal ops, `::error()` for failures |
| **FileStorage** | Not used by Master Data |
| **Notification** | Not used by Master Data |

Master Data does not store references to Platform Services tables. No FKs to `audit_logs` or `system_logs`.

---

## 19. Authentication Boundary

| Check | Status |
|---|---|
| No `users` table duplication | ✅ Master Data does not define user tables |
| No session/token tables | ✅ Phase 08 ownership |
| `created_by` references User UUID | ✅ Uses existing `users.id` convention |
| No FK to `users` | ✅ Reference is by UUID value only (no DB FK) — avoids coupling master data to user lifecycle |

`created_by`, `updated_by`, `deleted_by` store UUID values matching `users.id` but do not have formal FK constraints. This prevents Master Data from failing when users are deleted (SET NULL semantics enforced at application layer via `HasAudit`).

---

## 20. Downstream Domain Protection

Master Data tables do NOT duplicate:
- Organization tables ✅
- Branch tables ✅
- User tables ✅
- Role & Permission tables ✅
- Patient tables ✅
- Appointment tables ✅
- EMR tables ✅
- Finance tables ✅

All downstream entities reference Master Data via UUID FK to the relevant Master Data table. Relationship direction is always **Downstream → Master Data**.

---

## 21. Naming Conventions

| Convention | Example | Consistency |
|---|---|---|
| Table: snake_case plural | `insurance_companies` | ✅ All 23 tables |
| Column: snake_case | `created_by`, `is_active`, `country_id` | ✅ All columns |
| PK: `id` (uuid) | `id` | ✅ |
| FK: `{parent}_id` | `country_id` | ✅ |
| Index: `{table}_{columns}_idx` | `provinces_country_id_idx` | ✅ |
| Unique: `{table}_{column}_unique` | `countries_code_unique` | ✅ |
| FK constraint: Laravel default | Generated by Blueprint | ✅ |

---

## 22. Traceability Matrix

| Entity | Table | REQ | BR | Flow |
|---|---|---|---|---|
| Countries | `countries` | `GEO-001`, `X-001`–`X-011` | `BR-GEO-001`, `BR-X-001`–`X-011` | §2–§7 |
| Provinces | `provinces` | `GEO-002` | `BR-GEO-002`, `BR-GEO-003` | §2–§4 |
| Cities | `cities` | `GEO-003` | `BR-GEO-002` | §2–§4 |
| Districts | `districts` | `GEO-004` | `BR-GEO-002` | §2–§4 |
| Villages | `villages` | `GEO-005` | `BR-GEO-002` | §2–§4 |
| Currencies | `currencies` | `LOC-001` | `BR-LOC-001` | §2–§3 |
| Timezones | `timezones` | `LOC-002` | `BR-LOC-002` | §2–§3 |
| Languages | `languages` | `LOC-003` | `BR-LOC-003` | §2–§3 |
| Nationalities | `nationalities` | `LOC-004` | `BR-LOC-004` | §2–§3 |
| Genders | `genders` | `DEM-001` | `BR-DEM-001`, `BR-DEM-002` | §2–§3 |
| Religions | `religions` | `DEM-002` | `BR-DEM-001`, `BR-DEM-002` | §2–§3 |
| Blood Types | `blood_types` | `DEM-003` | `BR-DEM-001`, `BR-DEM-002` | §2–§3 |
| Marital Statuses | `marital_statuses` | `DEM-004` | `BR-DEM-001`, `BR-DEM-002` | §2–§3 |
| Patient Types | `patient_types` | `CLN-001` | `BR-CLN-001` | §2–§3 |
| Doctor Specialties | `doctor_specialties` | `CLN-002` | `BR-CLN-002` | §2–§3 |
| Treatment Categories | `treatment_categories` | `CLN-003` | `BR-CLN-003` | §2–§3 |
| Appointment Statuses | `appointment_statuses` | `CLN-004` | `BR-CLN-004` | §2–§3 |
| Laboratory Categories | `laboratory_categories` | `CLN-005` | `BR-CLN-005` | §2–§3 |
| Payment Methods | `payment_methods` | `FIN-001` | `BR-FIN-001` | §2–§3 |
| Insurance Companies | `insurance_companies` | `FIN-002` | `BR-FIN-002` | §2–§3 |
| Tax Rates | `tax_rates` | `FIN-003` | `BR-FIN-003` | §2–§3 |
| Asset Categories | `asset_categories` | `OPR-001` | `BR-OPR-001` | §2–§3 |
| Inventory Categories | `inventory_categories` | `OPR-002` | `BR-OPR-002` | §2–§3 |

**23/23 tables have requirement + BR + flow traceability.**

---

## 23. Cross-Document Drift Analysis

| Comparison | Result |
|---|---|
| DatabaseDesign ↔ Requirement.md | ✅ 23 tables match 23 requirements |
| DatabaseDesign ↔ BusinessRule.md | ✅ Base structure, global scope, UNIQUE code, soft delete all align |
| DatabaseDesign ↔ Flow.md | ✅ Universal CRUD + geographic hierarchy supported |
| DatabaseDesign ↔ 005_MasterData.md | ✅ Column sets match exactly |
| DatabaseDesign ↔ 006_MasterDataFoundation.md | ✅ 23-table catalog matches |
| DatabaseDesign ↔ Architecture Standards | ✅ timestamptz, uuid, SoftDeletes, HasAudit |
| DatabaseDesign ↔ Phase 07 | ✅ Audit/Logging integration documented |

**0 drifts.**

---

## 24. Architectural Compliance

| Check | Result |
|---|---|
| No circular ownership | ✅ Master Data owns all 23 tables |
| No domain table duplication | ✅ Organizations, Branches, Users not duplicated |
| Downstream → Master Data direction | ✅ |
| Platform-first maintained | ✅ Master Data is reusable by all future domains |
| BaseModel pattern preserved | ✅ `BaseMasterDataModel` extends `BaseModel` |
| Convention compliance | ✅ timestamptz, uuid, CHECK, indexes |

---

## 25. Frozen Artifact Verification

| Artifact | Modified? |
|---|---|
| Authentication | No |
| ADRs | No |
| AGENTS.md | No |
| Phase 07 | No |

---

## Governance Record

| Check | Result |
|---|---|
| 23 entities defined with full traceability | ✅ |
| Common base structure documented (10 cols) + per-table unique cols | ✅ |
| PK: UUID `Str::orderedUuid()` consistent | ✅ |
| 4 FKs (geographic) — RESTRICT, no CASCADE | ✅ |
| Global scope (no org/branch columns) | ✅ |
| `code` UNIQUE per table | ✅ |
| 50 indexes with documented rationales | ✅ |
| Soft delete on all tables | ✅ |
| Timestamptz on all datetimes | ✅ |
| Audit via `HasAudit` trait | ✅ |
| No duplicated domain tables | ✅ |
| 0 design drifts | ✅ |
| Frozen artifacts untouched | ✅ |
| Implementation not started | ✅ |

STEP_09_08_MASTER_DATA_DATABASE_DESIGN_DRAFT_PASS
