# Organization Table

## Overview

| Item         | Detail                                             |
|--------------|----------------------------------------------------|
| Table Name   | `organizations`                                    |
| Domain       | `app/Domains/Organization`                         |
| Model        | `App\Domains\Organization\Models\Organization`     |
| Engine       | PostgreSQL                                         |
| Primary Key  | `id` — UUID (ordered)                              |
| Soft Delete  | Yes — `deleted_at`                                 |
| Audit Trail  | `created_by`, `updated_by`, `deleted_by`           |
| Migration    | `2026_08_01_000001_create_organizations_table.php` |

---

## Columns

| #  | Column         | Type          | Nullable | Default        | Constraint  | Description                        |
|----|----------------|---------------|----------|----------------|-------------|------------------------------------|
| 1  | `id`           | uuid          | NO       | —              | PRIMARY KEY | Ordered UUID primary key           |
| 2  | `company_code` | varchar(20)   | NO       | —              | UNIQUE      | Unique company code                |
| 3  | `company_name` | varchar(150)  | NO       | —              |             | Trading / brand name               |
| 4  | `legal_name`   | varchar(200)  | YES      | NULL           |             | Legal registered company name      |
| 5  | `tax_number`   | varchar(30)   | YES      | NULL           | UNIQUE      | NPWP — tax identification number   |
| 6  | `email`        | varchar(150)  | YES      | NULL           | INDEX       | Company email address              |
| 7  | `phone`        | varchar(20)   | YES      | NULL           |             | Company phone number               |
| 8  | `website`      | varchar(255)  | YES      | NULL           |             | Company website URL                |
| 9  | `logo`         | varchar(500)  | YES      | NULL           |             | Logo relative file path or URL     |
| 10 | `address`      | text          | YES      | NULL           |             | Street address                     |
| 11 | `city`         | varchar(100)  | YES      | NULL           |             | City                               |
| 12 | `province`     | varchar(100)  | YES      | NULL           |             | Province / State                   |
| 13 | `country`      | varchar(100)  | NO       | `Indonesia`    |             | Country name                       |
| 14 | `postal_code`  | varchar(10)   | YES      | NULL           |             | Postal / ZIP code                  |
| 15 | `timezone`     | varchar(50)   | NO       | `Asia/Jakarta` |             | IANA timezone identifier           |
| 16 | `currency`     | varchar(10)   | NO       | `IDR`          |             | ISO 4217 currency code             |
| 17 | `status`       | varchar(20)   | NO       | `active`       | INDEX       | active / inactive / suspended      |
| 18 | `created_by`   | uuid          | YES      | NULL           |             | User UUID who created the record   |
| 19 | `updated_by`   | uuid          | YES      | NULL           |             | User UUID who last updated         |
| 20 | `deleted_by`   | uuid          | YES      | NULL           |             | User UUID who soft-deleted         |
| 21 | `created_at`   | timestamptz   | YES      | NULL           |             | Record creation timestamp          |
| 22 | `updated_at`   | timestamptz   | YES      | NULL           |             | Record last update timestamp       |
| 23 | `deleted_at`   | timestamptz   | YES      | NULL           |             | Soft delete timestamp              |

---

## Indexes

| Index Name                             | Column         | Type   | Description                     |
|----------------------------------------|----------------|--------|---------------------------------|
| `organizations_pkey`                   | `id`           | UNIQUE | Primary key                     |
| `organizations_company_code_unique`    | `company_code` | UNIQUE | Unique company code             |
| `organizations_tax_number_unique`      | `tax_number`   | UNIQUE | Unique NPWP                     |
| `organizations_email_index`            | `email`        | BTREE  | Lookup by email                 |
| `organizations_status_index`           | `status`       | BTREE  | Filter by status                |
| `organizations_country_index`          | `country`      | BTREE  | Filter by country               |

---

## Status Enum

| Value       | Label      | Operational | Description                              |
|-------------|------------|-------------|------------------------------------------|
| `active`    | Active     | Yes         | Organization is fully operational        |
| `inactive`  | Inactive   | No          | Organization is temporarily disabled     |
| `suspended` | Suspended  | No          | Organization is suspended due to issue   |

---

## Relationships

| Relation  | Type     | Foreign Table | FK Column                   | Description                        |
|-----------|----------|---------------|-----------------------------|------------------------------------|
| Branches  | Has Many | `branches`    | `branches.organization_id`  | Organization has many branches     |
| Users     | Has Many | `users`       | `users.organization_id`     | Users belong to an organization    |

---

## Business Rules

1. `company_code` must be unique across all organizations — used as external identifier.
2. `tax_number` (NPWP) must be unique when provided — format `XX.XXX.XXX.X-XXX.XXX`.
3. Organization with `status = suspended` cannot create new branches or transactions.
4. Organization with `status = inactive` cannot be used in any operational process.
5. `deleted_at` is used for soft delete — records are never permanently removed unless explicitly forced.
6. `created_by` and `updated_by` are automatically filled by `HasAudit` trait from the authenticated user.
7. `timezone` must be a valid IANA timezone string (e.g. `Asia/Jakarta`, `Asia/Makassar`, `Asia/Jayapura`).
8. `currency` must follow ISO 4217 standard (e.g. `IDR`, `USD`, `SGD`).
9. `logo` stores a relative storage path — resolved to full URL via `asset('storage/' . $logo)`.

---

## Sample Data

```sql
INSERT INTO organizations (
    id,
    company_code,
    company_name,
    legal_name,
    tax_number,
    email,
    phone,
    website,
    logo,
    address,
    city,
    province,
    country,
    postal_code,
    timezone,
    currency,
    status,
    created_at,
    updated_at
) VALUES (
    gen_random_uuid(),
    'ORG-0001',
    'My Dent Care',
    'PT My Dent Care Indonesia',
    '01.234.567.8-901.000',
    'info@mydentcare.com',
    '+62-21-1234567',
    'https://www.mydentcare.com',
    NULL,
    'Jl. Jenderal Sudirman No. 1',
    'Jakarta Selatan',
    'DKI Jakarta',
    'Indonesia',
    '12190',
    'Asia/Jakarta',
    'IDR',
    'active',
    NOW(),
    NOW()
);
```

---

## Notes

- UUID is generated using `Str::orderedUuid()` — time-based ordering for optimal PostgreSQL B-tree index performance.
- All datetime columns use `timestamptz` (timezone-aware) to support multi-timezone clinic deployments.
- `company_code` is the external business identifier used in integrations (SATUSEHAT, BPJS, etc).
- `tax_number` format follows Indonesian NPWP standard: `XX.XXX.XXX.X-XXX.XXX`.
