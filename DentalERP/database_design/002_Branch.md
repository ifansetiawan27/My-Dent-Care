# Branch Table

## Overview

| Item         | Detail                                             |
|--------------|----------------------------------------------------|
| Table Name   | `branches`                                         |
| Domain       | `app/Domains/Branch`                               |
| Model        | `App\Domains\Branch\Models\Branch`                 |
| Engine       | PostgreSQL                                         |
| Primary Key  | `id` — UUID (ordered)                              |
| Foreign Key  | `organization_id` — references `organizations.id`  |
| Soft Delete  | Yes — `deleted_at`                                 |
| Audit Trail  | `created_by`, `updated_by`, `deleted_by`           |
| Migration    | `2026_08_01_000002_create_branches_table.php`      |

---

## Columns

| #  | Column            | Type          | Nullable | Default        | Constraint             | Description                              |
|----|-------------------|---------------|----------|----------------|------------------------|------------------------------------------|
| 1  | `id`              | uuid          | NO       | —              | PRIMARY KEY            | Ordered UUID primary key                 |
| 2  | `organization_id` | uuid          | NO       | —              | FK, INDEX              | References organizations.id              |
| 3  | `branch_code`     | varchar(30)   | NO       | —              | UNIQUE per org, INDEX  | Branch code — unique within organization |
| 4  | `branch_name`     | varchar(200)  | NO       | —              |                        | Branch / clinic name                     |
| 5  | `branch_type`     | varchar(50)   | NO       | —              |                        | Type of branch (e.g. clinic, mobile)     |
| 6  | `email`           | varchar(150)  | NO       | —              |                        | Branch email address                     |
| 7  | `phone`           | varchar(30)   | NO       | —              |                        | Branch phone number                      |
| 8  | `address`         | text          | NO       | —              |                        | Street address                           |
| 9  | `city`            | varchar(100)  | NO       | —              | INDEX                  | City                                     |
| 10 | `province`        | varchar(100)  | NO       | —              |                        | Province or state                        |
| 11 | `country`         | varchar(100)  | NO       | `Indonesia`    |                        | Country name                             |
| 12 | `postal_code`     | varchar(20)   | NO       | —              |                        | Postal or ZIP code                       |
| 13 | `latitude`        | decimal(10,7) | YES      | NULL           |                        | Geographic latitude coordinate           |
| 14 | `longitude`       | decimal(10,7) | YES      | NULL           |                        | Geographic longitude coordinate          |
| 15 | `timezone`        | varchar(100)  | NO       | `Asia/Jakarta` |                        | IANA timezone identifier                 |
| 16 | `status`          | varchar(20)   | NO       | `active`       | INDEX                  | active / inactive                        |
| 17 | `created_by`      | uuid          | YES      | NULL           |                        | User UUID who created this record        |
| 18 | `updated_by`      | uuid          | YES      | NULL           |                        | User UUID who last updated this record   |
| 19 | `deleted_by`      | uuid          | YES      | NULL           |                        | User UUID who soft-deleted this record   |
| 20 | `created_at`      | timestamptz   | YES      | NULL           |                        | Record creation timestamp                |
| 21 | `updated_at`      | timestamptz   | YES      | NULL           |                        | Record last update timestamp             |
| 22 | `deleted_at`      | timestamptz   | YES      | NULL           |                        | Soft delete timestamp                    |

---

## Indexes

| Index Name                                       | Column                              | Type   | Description                                    |
|--------------------------------------------------|-------------------------------------|--------|------------------------------------------------|
| `branches_pkey`                                  | `id`                                | UNIQUE | Primary key                                    |
| `branches_organization_id_branch_code_unique`    | `organization_id`, `branch_code`    | UNIQUE | branch_code unique per organization            |
| `branches_organization_id_index`                 | `organization_id`                   | BTREE  | Filter branches by organization                |
| `branches_city_index`                            | `city`                              | BTREE  | Filter by city                                 |
| `branches_status_index`                          | `status`                            | BTREE  | Filter by status                               |

---

## Status Enum

| Value      | Label    | Operational | Description                     |
|------------|----------|-------------|---------------------------------|
| `active`   | Active   | Yes         | Branch is fully operational     |
| `inactive` | Inactive | No          | Branch is temporarily disabled  |

---

## Branch Type (Reference Values)

| Value      | Description                        |
|------------|------------------------------------|
| `clinic`   | Fixed physical clinic location     |
| `mobile`   | Mobile dental unit                 |
| `hospital` | Hospital-based dental department   |

---

## Foreign Keys

| Constraint Name                    | Column            | References         | On Delete |
|------------------------------------|-------------------|--------------------|-----------|
| `branches_organization_id_foreign` | `organization_id` | `organizations.id` | RESTRICT  |

> `RESTRICT` — Branch cannot exist without an Organization. Drop is prevented if branches still exist.

---

## Relationships

| Relation      | Type       | Foreign Table   | FK Column                  | Description                         |
|---------------|------------|-----------------|----------------------------|-------------------------------------|
| Organization  | Belongs To | `organizations` | `branches.organization_id` | Branch belongs to one Organization  |
| Users         | Has Many   | `users`         | `users.branch_id`          | Users assigned to this branch       |
| Patients      | Has Many   | `patients`      | `patients.branch_id`       | Patients registered at this branch  |
| Appointments  | Has Many   | `appointments`  | `appointments.branch_id`   | Appointments at this branch         |

---

## Business Rules

1. `branch_code` must be unique within the same Organization — two different organizations may use the same code.
2. Every branch must belong to exactly one Organization (`organization_id` is required).
3. A Branch can only be created under an Organization with `status = active`.
4. A Branch with `status = inactive` cannot accept new Patients or Appointments.
5. A Branch cannot be soft-deleted if it still has active Patients or Appointments.
6. `latitude` and `longitude` are optional but recommended for map and geolocation features.
7. `timezone` defaults to `Asia/Jakarta` but can be overridden per branch.
8. `deleted_at` is used for soft delete — records are never permanently removed unless explicitly forced.
9. `created_by` and `updated_by` are automatically filled by `HasAudit` trait from the authenticated user.

---

## Sample Data

```sql
INSERT INTO branches (
    id,
    organization_id,
    branch_code,
    branch_name,
    branch_type,
    email,
    phone,
    address,
    city,
    province,
    country,
    postal_code,
    latitude,
    longitude,
    timezone,
    status,
    created_at,
    updated_at
) VALUES (
    gen_random_uuid(),
    '<organization_id>',
    'BRC-0001',
    'My Dent Care - Sudirman',
    'clinic',
    'sudirman@mydentcare.com',
    '+62-21-1234568',
    'Jl. Jenderal Sudirman No. 10',
    'Jakarta Selatan',
    'DKI Jakarta',
    'Indonesia',
    '12190',
    -6.2088000,
    106.8456000,
    'Asia/Jakarta',
    'active',
    NOW(),
    NOW()
);
```

---

## Notes

- UUID is generated using `Str::orderedUuid()` — time-based ordering for optimal PostgreSQL B-tree index performance.
- All datetime columns use `timestamptz` (timezone-aware) to support multi-timezone clinic deployments.
- `branch_code` uniqueness is enforced at the composite index level `(organization_id, branch_code)` — not globally.
- `latitude` and `longitude` use `decimal(10,7)` for 7 decimal places of precision (~1.1 cm accuracy).
- `organization_id` foreign key uses `RESTRICT` on delete to prevent orphaned branches.
