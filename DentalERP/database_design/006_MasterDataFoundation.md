# Master Data Foundation

## Overview

| Item         | Detail                                          |
|--------------|-------------------------------------------------|
| Domain       | `app/Domains/MasterData`                        |
| Engine       | PostgreSQL                                      |
| Primary Key  | `id` — UUID (ordered) on all tables             |
| Soft Delete  | Yes — `deleted_at` on all tables                |
| Audit Trail  | `created_by`, `updated_by`, `deleted_by`        |
| Architecture | `BaseMasterDataModel` (reusable — see MasterData architecture) |
| Scope        | Global — not scoped to organization or branch   |
| Total Tables | 23 reference tables                             |

> This is the **foundation catalog** of all Master Data reference tables.
> Detailed per-table schemas are documented in `005_MasterData.md`.
> This document defines the full table set, groupings, dependencies, and seeding order.

---

## Common Base Structure

Every Master Data table inherits this structure from `BaseMasterDataModel`:

```sql
id           uuid         PRIMARY KEY        -- ordered UUID
code         varchar(N)   UNIQUE / indexed   -- machine code
name         varchar(N)   NOT NULL           -- display name
is_active    boolean      DEFAULT true       -- selectable flag
created_by   uuid         nullable           -- audit
updated_by   uuid         nullable           -- audit
deleted_by   uuid         nullable           -- audit
created_at   timestamptz
updated_at   timestamptz
deleted_at   timestamptz                     -- soft delete
```

Tables with hierarchy or extra attributes add their own columns on top of this base.

---

## Table Catalog (23 Tables)

### Group A — Geographic (Hierarchical)

| # | Table         | Parent FK        | Description                          |
|---|---------------|------------------|--------------------------------------|
| 1 | `countries`   | —                | ISO 3166 countries                   |
| 2 | `provinces`   | `country_id`     | Provinces / states                   |
| 3 | `cities`      | `province_id`    | Cities / regencies (BPS)             |
| 4 | `districts`   | `city_id`        | Districts (Kecamatan)                |
| 5 | `villages`    | `district_id`    | Villages (Kelurahan/Desa) + postal   |

### Group B — Locale

| # | Table           | Description                             |
|---|-----------------|-----------------------------------------|
| 6 | `currencies`    | ISO 4217 currencies (IDR default)       |
| 7 | `timezones`     | IANA timezones (WIB, WITA, WIT)         |
| 8 | `languages`     | ISO 639-1 languages                     |
| 9 | `nationalities` | Nationalities                           |

### Group C — Demographic

| #  | Table              | Description                          |
|----|--------------------|--------------------------------------|
| 10 | `genders`          | Male, Female                         |
| 11 | `religions`        | Islam, Christian, Catholic, etc.     |
| 12 | `blood_types`      | A, B, AB, O (with Rh variants)       |
| 13 | `marital_statuses` | Single, Married, Divorced, Widowed   |

### Group D — Clinical

| #  | Table                  | Description                                    |
|----|------------------------|------------------------------------------------|
| 14 | `patient_types`        | General, BPJS, Insurance, VIP, Employee, Child |
| 15 | `doctor_specialties`   | Orthodontist, Periodontist, etc.               |
| 16 | `treatment_categories` | Categories of dental treatment                 |
| 17 | `appointment_statuses` | Scheduled, Confirmed, Completed, etc.          |
| 18 | `laboratory_categories`| Categories of lab work (crown, denture, etc.)  |

### Group E — Financial

| #  | Table                 | Description                              |
|----|-----------------------|------------------------------------------|
| 19 | `payment_methods`     | Cash, Transfer, Card, E-wallet, Insurance|
| 20 | `insurance_companies` | BPJS, Prudential, AXA, etc.              |
| 21 | `tax_rates`           | PPN 11%, PPh 21, etc.                    |

### Group F — Operational

| #  | Table                  | Description                              |
|----|------------------------|------------------------------------------|
| 22 | `asset_categories`     | Categories of clinic assets (equipment)  |
| 23 | `inventory_categories` | Categories of inventory / supplies       |

---

## New Tables (Added in this Foundation)

These 5 tables extend the original 18 to complete the Master Data foundation:

| Table                  | Group      | Used By Domain      | Purpose                          |
|------------------------|------------|---------------------|----------------------------------|
| `asset_categories`     | Operational| Asset               | Kategori aset (dental chair, dll)|
| `inventory_categories` | Operational| Inventory, Pharmacy | Kategori stok/supply             |
| `laboratory_categories`| Clinical   | Laboratory          | Kategori pekerjaan lab           |
| `appointment_statuses` | Clinical   | Appointment         | Status janji temu (lookup)       |
| `treatment_categories` | Clinical   | Treatment           | Kategori perawatan               |

> Note: `appointment_statuses` as a Master Data table provides UI-configurable
> labels/colors, while the canonical status logic remains in the `AppointmentStatus` Enum.

---

## Dependency Graph

```
countries
   └── provinces
          └── cities
                 └── districts
                        └── villages

(all other tables are independent — no cross-table FK)
```

Only the geographic group is hierarchical.
All other 18 tables are flat and independent of each other.

---

## Seeding Order

Seed in this order to satisfy foreign key dependencies:

```
1. countries          →  provinces  →  cities  →  districts  →  villages
2. currencies, timezones, languages, nationalities        (locale — any order)
3. genders, religions, blood_types, marital_statuses      (demographic — any order)
4. patient_types, doctor_specialties, treatment_categories,
   appointment_statuses, laboratory_categories            (clinical — any order)
5. payment_methods, insurance_companies, tax_rates        (financial — any order)
6. asset_categories, inventory_categories                 (operational — any order)
```

> Only the geographic group requires strict order.
> All other groups can be seeded in parallel/any order.

---

## Business Rules

1. Master Data is **global** — not scoped to any organization or branch.
2. Master Data records are **soft-deleted only** — never hard-deleted.
3. Records with `is_active = false` must not appear in selection dropdowns.
4. Only **Super Admin** and **Owner** may create or modify Master Data.
5. Every `code` must be UNIQUE within its table.
6. Seed data is managed via Laravel Seeders — never created via UI in production.
7. Geographic tables follow Indonesian **BPS** standards.
8. `currencies` → ISO 4217, `languages` → ISO 639-1, `timezones` → IANA.
9. New modules that need a lookup table MUST add it here — never inline domain-specific lookups.
10. All 23 tables reuse `BaseMasterDataModel`, `BaseMasterDataRepository`, `BaseMasterDataService`.

---

## Architecture Reference

All 23 tables are powered by the reusable MasterData architecture:

| Layer      | Base Class                          |
|------------|-------------------------------------|
| Model      | `BaseMasterDataModel`               |
| Repository | `BaseMasterDataRepository`          |
| Service    | `BaseMasterDataService`             |
| Interface  | `MasterDataRepositoryInterface`, `MasterDataServiceInterface` |
| DTO        | `MasterDataFilterDTO`               |

Adding one table = 3 minimal files (Model + Repository + Service) that extend the base.

---

## Notes

- Master Data tables are strong candidates for Redis caching (read-heavy).
- Geographic data (`villages` especially) can be large — paginate and cache aggressively.
- All Master Data endpoints use `permission:master_data.view` for read access.
- Category tables (`*_categories`) allow clinics to extend classifications without code changes.
