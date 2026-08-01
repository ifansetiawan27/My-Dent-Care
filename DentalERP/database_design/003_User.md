# User Table

## Overview

| Item         | Detail                                                        |
|--------------|---------------------------------------------------------------|
| Table Name   | `users`                                                       |
| Domain       | `app/Domains/Authentication`                                  |
| Model        | `App\Domains\Authentication\Models\User`                      |
| Engine       | PostgreSQL                                                    |
| Primary Key  | `id` — UUID (ordered)                                         |
| Foreign Keys | `organization_id` → `organizations.id`                        |
|              | `branch_id` → `branches.id`                                   |
| Soft Delete  | Yes — `deleted_at`                                            |
| Audit Trail  | `created_by`, `updated_by`, `deleted_by`                      |
| Auth         | Laravel Sanctum                                               |
| Permission   | Spatie Laravel Permission                                     |
| Migration    | `2026_08_01_000003_create_users_table.php`                    |

---

## Columns

| #  | Column               | Type          | Nullable | Default   | Constraint  | Description                                        |
|----|----------------------|---------------|----------|-----------|-------------|----------------------------------------------------|
| 1  | `id`                 | uuid          | NO       | —         | PRIMARY KEY | Ordered UUID primary key                           |
| 2  | `organization_id`    | uuid          | NO       | —         | FK, INDEX   | References organizations.id                        |
| 3  | `branch_id`          | uuid          | NO       | —         | FK, INDEX   | References branches.id                             |
| 4  | `employee_code`      | varchar(30)   | NO       | —         | UNIQUE      | Unique employee / staff code across system         |
| 5  | `name`               | varchar(200)  | NO       | —         |             | Full name of the user                              |
| 6  | `username`           | varchar(100)  | NO       | —         | UNIQUE      | Unique login username                              |
| 7  | `email`              | varchar(150)  | NO       | —         | UNIQUE      | Unique email address                               |
| 8  | `phone`              | varchar(30)   | YES      | NULL      |             | Phone number                                       |
| 9  | `password`           | varchar(255)  | NO       | —         |             | Bcrypt hashed password — never stored in plaintext |
| 10 | `photo`              | varchar(500)  | YES      | NULL      |             | Profile photo relative storage path                |
| 11 | `gender`             | varchar(10)   | YES      | NULL      |             | male / female                                      |
| 12 | `birth_date`         | date          | YES      | NULL      |             | Date of birth                                      |
| 13 | `last_login_at`      | timestamptz   | YES      | NULL      |             | Timestamp of last successful login                 |
| 14 | `email_verified_at`  | timestamptz   | YES      | NULL      |             | Timestamp when email was verified                  |
| 15 | `status`             | varchar(20)   | NO       | `active`  | INDEX       | active / inactive                                  |
| 16 | `created_by`         | uuid          | YES      | NULL      |             | User UUID who created this record                  |
| 17 | `updated_by`         | uuid          | YES      | NULL      |             | User UUID who last updated this record             |
| 18 | `deleted_by`         | uuid          | YES      | NULL      |             | User UUID who soft-deleted this record             |
| 19 | `created_at`         | timestamptz   | YES      | NULL      |             | Record creation timestamp                          |
| 20 | `updated_at`         | timestamptz   | YES      | NULL      |             | Record last update timestamp                       |
| 21 | `deleted_at`         | timestamptz   | YES      | NULL      |             | Soft delete timestamp                              |

---

## Indexes

| Index Name                          | Column            | Type   | Description                             |
|-------------------------------------|-------------------|--------|-----------------------------------------|
| `users_pkey`                        | `id`              | UNIQUE | Primary key                             |
| `users_employee_code_unique`        | `employee_code`   | UNIQUE | Globally unique employee code           |
| `users_username_unique`             | `username`        | UNIQUE | Globally unique username                |
| `users_email_unique`                | `email`           | UNIQUE | Globally unique email address           |
| `users_organization_id_index`       | `organization_id` | BTREE  | Filter users by organization            |
| `users_branch_id_index`             | `branch_id`       | BTREE  | Filter users by branch                  |
| `users_status_index`                | `status`          | BTREE  | Filter users by status                  |

---

## Status Enum

| Value      | Label    | Can Login | Description                        |
|------------|----------|-----------|------------------------------------|
| `active`   | Active   | Yes       | User is fully operational          |
| `inactive` | Inactive | No        | User account is disabled           |

---

## Gender Enum (Reference Values)

| Value    | Label  |
|----------|--------|
| `male`   | Male   |
| `female` | Female |

---

## Foreign Keys

| Constraint Name               | Column            | References         | On Delete |
|-------------------------------|-------------------|--------------------|-----------|
| `users_organization_id_foreign` | `organization_id` | `organizations.id` | RESTRICT  |
| `users_branch_id_foreign`     | `branch_id`       | `branches.id`      | RESTRICT  |

> `RESTRICT` — User cannot exist without a valid Organization and Branch. Prevents orphaned user records.

---

## Relationships

| Relation      | Type       | Foreign Table    | FK Column                   | Description                              |
|---------------|------------|------------------|-----------------------------|------------------------------------------|
| Organization  | Belongs To | `organizations`  | `users.organization_id`     | User belongs to one Organization         |
| Branch        | Belongs To | `branches`       | `users.branch_id`           | User is assigned to one Branch           |
| Roles         | Many-Many  | `roles`          | via `model_has_roles`       | Spatie roles assigned to this user       |
| Permissions   | Many-Many  | `permissions`    | via `model_has_permissions` | Spatie permissions assigned to this user |

---

## Business Rules

1. `username` must be globally unique across all organizations.
2. `email` must be globally unique across all organizations.
3. `employee_code` must be globally unique — used as the official staff identifier.
4. Every user must belong to exactly one Organization (`organization_id` is required).
5. Every user must be assigned to exactly one Branch (`branch_id` is required).
6. The assigned Branch MUST belong to the same Organization as the User.
7. A User with `status = inactive` MUST NOT be able to log in.
8. `password` is always stored as a bcrypt hash — plain text is never acceptable.
9. `email_verified_at` should be set before a user can access clinical features.
10. `last_login_at` is updated automatically on every successful authentication.
11. `photo` stores a relative storage path — the full URL is resolved via `asset('storage/' . $photo)`.
12. A User cannot be soft-deleted if they have active clinical records (Appointments, Treatments, EMR entries).
13. Roles and permissions are managed via Spatie Laravel Permission — never stored as raw columns.
14. `deleted_at` is used for soft delete — records are never permanently removed.
15. `created_by` and `updated_by` are automatically filled by `HasAudit` trait.

---

## Sample Data

```sql
INSERT INTO users (
    id,
    organization_id,
    branch_id,
    employee_code,
    name,
    username,
    email,
    phone,
    password,
    photo,
    gender,
    birth_date,
    last_login_at,
    email_verified_at,
    status,
    created_at,
    updated_at
) VALUES (
    gen_random_uuid(),
    '<organization_id>',
    '<branch_id>',
    'EMP-0001',
    'Dr. Budi Santoso',
    'budi.santoso',
    'budi.santoso@mydentcare.com',
    '+62-812-0000-0001',
    '$2y$12$hashedpassword...',
    NULL,
    'male',
    '1985-06-15',
    NULL,
    NOW(),
    'active',
    NOW(),
    NOW()
);
```

---

## Notes

- UUID is generated using `Str::orderedUuid()` — time-based ordering for optimal PostgreSQL B-tree index performance.
- All datetime columns use `timestamptz` (timezone-aware) to support multi-timezone branch deployments.
- `password` uses Laravel's `hashed` cast — automatically bcrypt-hashed on assignment.
- `username` and `email` are globally unique — even across different organizations. This supports SSO and cross-org admin access in future.
- `employee_code` is the official HR identifier — used in payroll, attendance, and clinical records linkage.
- `branch_id` determines the user's default operational scope (which branch they log into).
- The composite check (branch belongs to user's organization) is enforced at the Service layer.
- Roles (e.g. `dentist`, `admin`, `receptionist`) are managed via Spatie and are organization-scoped.
- `gender` and `birth_date` are used for HR reporting and are not exposed publicly.
