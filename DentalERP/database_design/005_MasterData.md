# Master Data Modules

## Overview

| Item         | Detail                                          |
|--------------|-------------------------------------------------|
| Domain       | `app/Domains/MasterData`                        |
| Engine       | PostgreSQL                                      |
| Primary Key  | `id` — UUID (ordered) on all tables             |
| Soft Delete  | Yes — `deleted_at` on all tables                |
| Audit Trail  | `created_by`, `updated_by`, `deleted_by`        |
| Purpose      | Shared reference / lookup tables used system-wide |
| Scope        | Global — not scoped to organization or branch   |

> Master Data tables are **read-heavy** reference tables.
> They are seeded at system setup and rarely mutated at runtime.
> All tables follow the same base structure: UUID PK, name, code, status, audit, timestamps, soft delete.

---

## Tables

---

### 1. `countries`

| # | Column       | Type         | Nullable | Default  | Description                    |
|---|--------------|--------------|----------|----------|--------------------------------|
| 1 | `id`         | uuid         | NO       | —        | Ordered UUID primary key       |
| 2 | `code`       | varchar(3)   | NO       | —        | ISO 3166-1 alpha-2/3 code (e.g. `ID`, `US`) |
| 3 | `name`       | varchar(100) | NO       | —        | Country name in English        |
| 4 | `name_local` | varchar(100) | YES      | NULL     | Country name in local language |
| 5 | `phone_code` | varchar(10)  | YES      | NULL     | International dialing code (e.g. `+62`) |
| 6 | `is_active`  | boolean      | NO       | `true`   | Whether this country is selectable |
| 7 | `created_by` | uuid         | YES      | NULL     | Audit — created by             |
| 8 | `updated_by` | uuid         | YES      | NULL     | Audit — updated by             |
| 9 | `deleted_by` | uuid         | YES      | NULL     | Audit — deleted by             |
| 10 | `created_at` | timestamptz | YES      | NULL     | Creation timestamp             |
| 11 | `updated_at` | timestamptz | YES      | NULL     | Last update timestamp          |
| 12 | `deleted_at` | timestamptz | YES      | NULL     | Soft delete timestamp          |

**Indexes:** `code` UNIQUE, `is_active`
**Seed:** All countries from ISO 3166-1. Default active: Indonesia.

---

### 2. `provinces`

| # | Column       | Type         | Nullable | Default | Description                      |
|---|--------------|--------------|----------|---------|----------------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key         |
| 2 | `country_id` | uuid         | NO       | —       | FK → countries.id                |
| 3 | `code`       | varchar(10)  | NO       | —       | Province code (e.g. `JK`, `JB`) |
| 4 | `name`       | varchar(100) | NO       | —       | Province name                    |
| 5 | `is_active`  | boolean      | NO       | `true`  | Selectable                       |
| 6 | `created_by` | uuid         | YES      | NULL    |                                  |
| 7 | `updated_by` | uuid         | YES      | NULL    |                                  |
| 8 | `deleted_by` | uuid         | YES      | NULL    |                                  |
| 9 | `created_at` | timestamptz  | YES      | NULL    |                                  |
| 10 | `updated_at` | timestamptz | YES      | NULL    |                                  |
| 11 | `deleted_at` | timestamptz | YES      | NULL    |                                  |

**Indexes:** `country_id`, `code` UNIQUE per country, `is_active`
**Seed:** 38 Indonesian provinces (BPS data).

---

### 3. `cities`

| # | Column        | Type         | Nullable | Default | Description                    |
|---|---------------|--------------|----------|---------|--------------------------------|
| 1 | `id`          | uuid         | NO       | —       | Ordered UUID primary key       |
| 2 | `province_id` | uuid         | NO       | —       | FK → provinces.id              |
| 3 | `code`        | varchar(10)  | NO       | —       | City / Regency code (BPS)      |
| 4 | `name`        | varchar(100) | NO       | —       | City or Regency name           |
| 5 | `type`        | varchar(20)  | NO       | —       | `city` or `regency`            |
| 6 | `is_active`   | boolean      | NO       | `true`  | Selectable                     |
| 7 | `created_by`  | uuid         | YES      | NULL    |                                |
| 8 | `updated_by`  | uuid         | YES      | NULL    |                                |
| 9 | `deleted_by`  | uuid         | YES      | NULL    |                                |
| 10 | `created_at` | timestamptz  | YES      | NULL    |                                |
| 11 | `updated_at` | timestamptz  | YES      | NULL    |                                |
| 12 | `deleted_at` | timestamptz  | YES      | NULL    |                                |

**Indexes:** `province_id`, `is_active`
**Seed:** 514 cities/regencies in Indonesia (BPS data).

---

### 4. `districts`

| # | Column      | Type         | Nullable | Default | Description              |
|---|-------------|--------------|----------|---------|--------------------------|
| 1 | `id`        | uuid         | NO       | —       | Ordered UUID primary key |
| 2 | `city_id`   | uuid         | NO       | —       | FK → cities.id           |
| 3 | `code`      | varchar(10)  | NO       | —       | District code (BPS)      |
| 4 | `name`      | varchar(100) | NO       | —       | District (Kecamatan) name|
| 5 | `is_active` | boolean      | NO       | `true`  | Selectable               |
| 6 | `created_by`| uuid         | YES      | NULL    |                          |
| 7 | `updated_by`| uuid         | YES      | NULL    |                          |
| 8 | `deleted_by`| uuid         | YES      | NULL    |                          |
| 9 | `created_at`| timestamptz  | YES      | NULL    |                          |
| 10 | `updated_at`| timestamptz | YES      | NULL    |                          |
| 11 | `deleted_at`| timestamptz | YES      | NULL    |                          |

**Indexes:** `city_id`, `is_active`

---

### 5. `villages`

| # | Column        | Type         | Nullable | Default | Description                    |
|---|---------------|--------------|----------|---------|--------------------------------|
| 1 | `id`          | uuid         | NO       | —       | Ordered UUID primary key       |
| 2 | `district_id` | uuid         | NO       | —       | FK → districts.id              |
| 3 | `code`        | varchar(15)  | NO       | —       | Village code (BPS)             |
| 4 | `name`        | varchar(100) | NO       | —       | Village (Kelurahan/Desa) name  |
| 5 | `postal_code` | varchar(10)  | YES      | NULL    | Postal code for this village   |
| 6 | `is_active`   | boolean      | NO       | `true`  | Selectable                     |
| 7 | `created_by`  | uuid         | YES      | NULL    |                                |
| 8 | `updated_by`  | uuid         | YES      | NULL    |                                |
| 9 | `deleted_by`  | uuid         | YES      | NULL    |                                |
| 10 | `created_at` | timestamptz  | YES      | NULL    |                                |
| 11 | `updated_at` | timestamptz  | YES      | NULL    |                                |
| 12 | `deleted_at` | timestamptz  | YES      | NULL    |                                |

**Indexes:** `district_id`, `is_active`

---

### 6. `currencies`

| # | Column       | Type         | Nullable | Default | Description                         |
|---|--------------|--------------|----------|---------|-------------------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key            |
| 2 | `code`       | varchar(3)   | NO       | —       | ISO 4217 code (e.g. `IDR`, `USD`)  |
| 3 | `name`       | varchar(100) | NO       | —       | Currency name (e.g. Indonesian Rupiah) |
| 4 | `symbol`     | varchar(10)  | NO       | —       | Currency symbol (e.g. `Rp`, `$`)   |
| 5 | `decimal_places` | tinyint  | NO       | `2`     | Number of decimal places            |
| 6 | `is_active`  | boolean      | NO       | `true`  | Selectable                          |
| 7 | `created_by` | uuid         | YES      | NULL    |                                     |
| 8 | `updated_by` | uuid         | YES      | NULL    |                                     |
| 9 | `deleted_by` | uuid         | YES      | NULL    |                                     |
| 10 | `created_at` | timestamptz | YES      | NULL    |                                     |
| 11 | `updated_at` | timestamptz | YES      | NULL    |                                     |
| 12 | `deleted_at` | timestamptz | YES      | NULL    |                                     |

**Indexes:** `code` UNIQUE, `is_active`
**Default:** IDR (Indonesian Rupiah)

---

### 7. `timezones`

| # | Column       | Type         | Nullable | Default | Description                              |
|---|--------------|--------------|----------|---------|------------------------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key                 |
| 2 | `identifier` | varchar(100) | NO       | —       | IANA timezone (e.g. `Asia/Jakarta`)     |
| 3 | `label`      | varchar(100) | NO       | —       | Human-readable label (e.g. `WIB (UTC+7)`) |
| 4 | `offset`     | varchar(10)  | NO       | —       | UTC offset (e.g. `+07:00`)              |
| 5 | `is_active`  | boolean      | NO       | `true`  | Selectable                               |
| 6 | `created_by` | uuid         | YES      | NULL    |                                          |
| 7 | `updated_by` | uuid         | YES      | NULL    |                                          |
| 8 | `deleted_by` | uuid         | YES      | NULL    |                                          |
| 9 | `created_at` | timestamptz  | YES      | NULL    |                                          |
| 10 | `updated_at` | timestamptz | YES      | NULL    |                                          |
| 11 | `deleted_at` | timestamptz | YES      | NULL    |                                          |

**Indexes:** `identifier` UNIQUE, `is_active`
**Seed:** WIB (Asia/Jakarta), WITA (Asia/Makassar), WIT (Asia/Jayapura)

---

### 8. `religions`

| # | Column       | Type         | Nullable | Default | Description              |
|---|--------------|--------------|----------|---------|--------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key |
| 2 | `code`       | varchar(20)  | NO       | —       | Religion code            |
| 3 | `name`       | varchar(100) | NO       | —       | Religion name            |
| 4 | `is_active`  | boolean      | NO       | `true`  | Selectable               |
| 5 | `created_by` | uuid         | YES      | NULL    |                          |
| 6 | `updated_by` | uuid         | YES      | NULL    |                          |
| 7 | `deleted_by` | uuid         | YES      | NULL    |                          |
| 8 | `created_at` | timestamptz  | YES      | NULL    |                          |
| 9 | `updated_at` | timestamptz  | YES      | NULL    |                          |
| 10 | `deleted_at` | timestamptz | YES      | NULL    |                          |

**Seed:** islam, christian, catholic, hindu, buddha, konghucu (per Indonesian MoHA)

---

### 9. `genders`

| # | Column       | Type         | Nullable | Default | Description              |
|---|--------------|--------------|----------|---------|--------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key |
| 2 | `code`       | varchar(10)  | NO       | —       | Gender code (e.g. `M`, `F`) |
| 3 | `name`       | varchar(50)  | NO       | —       | Gender name              |
| 4 | `is_active`  | boolean      | NO       | `true`  | Selectable               |
| 5 | `created_by` | uuid         | YES      | NULL    |                          |
| 6 | `updated_by` | uuid         | YES      | NULL    |                          |
| 7 | `deleted_by` | uuid         | YES      | NULL    |                          |
| 8 | `created_at` | timestamptz  | YES      | NULL    |                          |
| 9 | `updated_at` | timestamptz  | YES      | NULL    |                          |
| 10 | `deleted_at` | timestamptz | YES      | NULL    |                          |

**Seed:** Male, Female

---

### 10. `blood_types`

| # | Column       | Type         | Nullable | Default | Description              |
|---|--------------|--------------|----------|---------|--------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key |
| 2 | `code`       | varchar(5)   | NO       | —       | Blood type code (e.g. `A+`, `O-`) |
| 3 | `name`       | varchar(20)  | NO       | —       | Blood type name          |
| 4 | `is_active`  | boolean      | NO       | `true`  | Selectable               |
| 5 | `created_by` | uuid         | YES      | NULL    |                          |
| 6 | `updated_by` | uuid         | YES      | NULL    |                          |
| 7 | `deleted_by` | uuid         | YES      | NULL    |                          |
| 8 | `created_at` | timestamptz  | YES      | NULL    |                          |
| 9 | `updated_at` | timestamptz  | YES      | NULL    |                          |
| 10 | `deleted_at` | timestamptz | YES      | NULL    |                          |

**Seed:** A, B, AB, O (with Rh+ and Rh- variants = 8 records)

---

### 11. `marital_statuses`

| # | Column       | Type         | Nullable | Default | Description              |
|---|--------------|--------------|----------|---------|--------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key |
| 2 | `code`       | varchar(20)  | NO       | —       | Status code              |
| 3 | `name`       | varchar(50)  | NO       | —       | Status name              |
| 4 | `is_active`  | boolean      | NO       | `true`  | Selectable               |
| 5 | `created_by` | uuid         | YES      | NULL    |                          |
| 6 | `updated_by` | uuid         | YES      | NULL    |                          |
| 7 | `deleted_by` | uuid         | YES      | NULL    |                          |
| 8 | `created_at` | timestamptz  | YES      | NULL    |                          |
| 9 | `updated_at` | timestamptz  | YES      | NULL    |                          |
| 10 | `deleted_at` | timestamptz | YES      | NULL    |                          |

**Seed:** Single, Married, Divorced, Widowed

---

### 12. `patient_types`

| # | Column        | Type         | Nullable | Default | Description                              |
|---|---------------|--------------|----------|---------|------------------------------------------|
| 1 | `id`          | uuid         | NO       | —       | Ordered UUID primary key                 |
| 2 | `code`        | varchar(20)  | NO       | —       | Patient type code                        |
| 3 | `name`        | varchar(100) | NO       | —       | Patient type name                        |
| 4 | `description` | text         | YES      | NULL    | Additional description                   |
| 5 | `is_active`   | boolean      | NO       | `true`  | Selectable                               |
| 6 | `created_by`  | uuid         | YES      | NULL    |                                          |
| 7 | `updated_by`  | uuid         | YES      | NULL    |                                          |
| 8 | `deleted_by`  | uuid         | YES      | NULL    |                                          |
| 9 | `created_at`  | timestamptz  | YES      | NULL    |                                          |
| 10 | `updated_at` | timestamptz  | YES      | NULL    |                                          |
| 11 | `deleted_at` | timestamptz  | YES      | NULL    |                                          |

**Seed:** General, BPJS, Insurance, VIP, Employee, Child

---

### 13. `doctor_specialties`

| # | Column        | Type         | Nullable | Default | Description              |
|---|---------------|--------------|----------|---------|--------------------------|
| 1 | `id`          | uuid         | NO       | —       | Ordered UUID primary key |
| 2 | `code`        | varchar(20)  | NO       | —       | Specialty code           |
| 3 | `name`        | varchar(100) | NO       | —       | Specialty name           |
| 4 | `description` | text         | YES      | NULL    | Specialty description    |
| 5 | `is_active`   | boolean      | NO       | `true`  | Selectable               |
| 6 | `created_by`  | uuid         | YES      | NULL    |                          |
| 7 | `updated_by`  | uuid         | YES      | NULL    |                          |
| 8 | `deleted_by`  | uuid         | YES      | NULL    |                          |
| 9 | `created_at`  | timestamptz  | YES      | NULL    |                          |
| 10 | `updated_at` | timestamptz  | YES      | NULL    |                          |
| 11 | `deleted_at` | timestamptz  | YES      | NULL    |                          |

**Seed:** General Dentist, Orthodontist, Periodontist, Endodontist, Prosthodontist, Oral Surgeon, Pediatric Dentist

---

### 14. `payment_methods`

| # | Column        | Type         | Nullable | Default | Description                                   |
|---|---------------|--------------|----------|---------|-----------------------------------------------|
| 1 | `id`          | uuid         | NO       | —       | Ordered UUID primary key                      |
| 2 | `code`        | varchar(20)  | NO       | —       | Payment method code                           |
| 3 | `name`        | varchar(100) | NO       | —       | Payment method name                           |
| 4 | `type`        | varchar(30)  | NO       | —       | `cash`, `transfer`, `card`, `ewallet`, `insurance` |
| 5 | `description` | text         | YES      | NULL    | Additional description                        |
| 6 | `is_active`   | boolean      | NO       | `true`  | Selectable                                    |
| 7 | `created_by`  | uuid         | YES      | NULL    |                                               |
| 8 | `updated_by`  | uuid         | YES      | NULL    |                                               |
| 9 | `deleted_by`  | uuid         | YES      | NULL    |                                               |
| 10 | `created_at` | timestamptz  | YES      | NULL    |                                               |
| 11 | `updated_at` | timestamptz  | YES      | NULL    |                                               |
| 12 | `deleted_at` | timestamptz  | YES      | NULL    |                                               |

**Seed:** Cash, Bank Transfer, Credit Card, Debit Card, GoPay, OVO, Dana, BPJS, Insurance

---

### 15. `insurance_companies`

| # | Column           | Type         | Nullable | Default | Description                    |
|---|------------------|--------------|----------|---------|--------------------------------|
| 1 | `id`             | uuid         | NO       | —       | Ordered UUID primary key       |
| 2 | `code`           | varchar(20)  | NO       | —       | Insurance company code         |
| 3 | `name`           | varchar(150) | NO       | —       | Insurance company name         |
| 4 | `type`           | varchar(30)  | NO       | —       | `government`, `private`        |
| 5 | `phone`          | varchar(30)  | YES      | NULL    | Contact phone                  |
| 6 | `email`          | varchar(150) | YES      | NULL    | Contact email                  |
| 7 | `website`        | varchar(255) | YES      | NULL    | Company website                |
| 8 | `claim_procedure`| text         | YES      | NULL    | Claim submission procedure     |
| 9 | `is_active`      | boolean      | NO       | `true`  | Selectable                     |
| 10 | `created_by`    | uuid         | YES      | NULL    |                                |
| 11 | `updated_by`    | uuid         | YES      | NULL    |                                |
| 12 | `deleted_by`    | uuid         | YES      | NULL    |                                |
| 13 | `created_at`    | timestamptz  | YES      | NULL    |                                |
| 14 | `updated_at`    | timestamptz  | YES      | NULL    |                                |
| 15 | `deleted_at`    | timestamptz  | YES      | NULL    |                                |

**Seed:** BPJS Kesehatan, Prudential, AXA Mandiri, Allianz, Manulife, Great Eastern

---

### 16. `tax_rates`

| # | Column        | Type           | Nullable | Default | Description                           |
|---|---------------|----------------|----------|---------|---------------------------------------|
| 1 | `id`          | uuid           | NO       | —       | Ordered UUID primary key              |
| 2 | `code`        | varchar(20)    | NO       | —       | Tax code (e.g. `PPN11`, `PPH21`)     |
| 3 | `name`        | varchar(100)   | NO       | —       | Tax name                              |
| 4 | `rate`        | decimal(5,2)   | NO       | —       | Tax rate in percentage (e.g. `11.00`) |
| 5 | `description` | text           | YES      | NULL    | Tax description                       |
| 6 | `is_active`   | boolean        | NO       | `true`  | Selectable                            |
| 7 | `created_by`  | uuid           | YES      | NULL    |                                       |
| 8 | `updated_by`  | uuid           | YES      | NULL    |                                       |
| 9 | `deleted_by`  | uuid           | YES      | NULL    |                                       |
| 10 | `created_at` | timestamptz    | YES      | NULL    |                                       |
| 11 | `updated_at` | timestamptz    | YES      | NULL    |                                       |
| 12 | `deleted_at` | timestamptz    | YES      | NULL    |                                       |

**Seed:** PPN 11%, PPh 21 (various rates), Non-Taxable (0%)

---

### 17. `languages`

| # | Column       | Type         | Nullable | Default | Description                              |
|---|--------------|--------------|----------|---------|------------------------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key                 |
| 2 | `code`       | varchar(5)   | NO       | —       | ISO 639-1 code (e.g. `id`, `en`, `zh`) |
| 3 | `name`       | varchar(100) | NO       | —       | Language name in English                 |
| 4 | `name_local` | varchar(100) | YES      | NULL    | Language name in its own language        |
| 5 | `is_active`  | boolean      | NO       | `true`  | Selectable                               |
| 6 | `created_by` | uuid         | YES      | NULL    |                                          |
| 7 | `updated_by` | uuid         | YES      | NULL    |                                          |
| 8 | `deleted_by` | uuid         | YES      | NULL    |                                          |
| 9 | `created_at` | timestamptz  | YES      | NULL    |                                          |
| 10 | `updated_at` | timestamptz | YES      | NULL    |                                          |
| 11 | `deleted_at` | timestamptz | YES      | NULL    |                                          |

**Seed:** Indonesian (Bahasa Indonesia), English, Mandarin, Arabic, Dutch

---

### 18. `nationalities`

| # | Column       | Type         | Nullable | Default | Description                              |
|---|--------------|--------------|----------|---------|------------------------------------------|
| 1 | `id`         | uuid         | NO       | —       | Ordered UUID primary key                 |
| 2 | `code`       | varchar(3)   | NO       | —       | ISO 3166-1 alpha-2 code (e.g. `ID`)    |
| 3 | `name`       | varchar(100) | NO       | —       | Nationality name (e.g. Indonesian)      |
| 4 | `is_active`  | boolean      | NO       | `true`  | Selectable                               |
| 5 | `created_by` | uuid         | YES      | NULL    |                                          |
| 6 | `updated_by` | uuid         | YES      | NULL    |                                          |
| 7 | `deleted_by` | uuid         | YES      | NULL    |                                          |
| 8 | `created_at` | timestamptz  | YES      | NULL    |                                          |
| 9 | `updated_at` | timestamptz  | YES      | NULL    |                                          |
| 10 | `deleted_at` | timestamptz | YES      | NULL    |                                          |

**Indexes:** `code` UNIQUE, `is_active`
**Seed:** Indonesian + major nationalities.

---

## Common Patterns

All Master Data tables share the following structure:

```sql
id           uuid         PRIMARY KEY
code         varchar(N)   UNIQUE / indexed
name         varchar(N)   NOT NULL
is_active    boolean      DEFAULT true
created_by   uuid         nullable
updated_by   uuid         nullable
deleted_by   uuid         nullable
created_at   timestamptz
updated_at   timestamptz
deleted_at   timestamptz  (soft delete)
```

---

## Business Rules

1. Master Data is **global** — not scoped to any organization or branch.
2. Master Data records should **never be hard-deleted** — use soft delete only.
3. Records with `is_active = false` must not appear in selection dropdowns.
4. Only **Super Admin** and **Owner** can create or modify Master Data.
5. All codes (`code` column) must be UNIQUE within their table.
6. Seed data is managed via Laravel Seeders — not via API.
7. `countries`, `provinces`, `cities`, `districts`, `villages` follow **Indonesian BPS (Badan Pusat Statistik)** data standards.
8. `currencies` follow ISO 4217 standard.
9. `languages` follow ISO 639-1 standard.
10. `timezones` follow IANA timezone database.

---

## Notes

- Master Data tables are candidates for Redis caching due to their read-heavy nature.
- A `MasterDataCacheService` should be implemented to cache these values on first load.
- All Master Data endpoints should use `permission:master_data.view` middleware.
- Avoid duplicating Master Data values in domain tables — always reference via FK or value from Master Data.
