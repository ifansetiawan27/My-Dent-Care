# Role & Permission

## Overview

| Item          | Detail                                                        |
|---------------|---------------------------------------------------------------|
| Package       | `spatie/laravel-permission`                                   |
| Domain        | `app/Domains/RolePermission`                                  |
| Engine        | PostgreSQL                                                    |
| Guard         | `sanctum`                                                     |
| Scope         | Organization-scoped — roles are assigned per organization     |
| Migration     | Published by Spatie — customized for UUID & multi-tenant      |

---

## Tables (Created by Spatie)

### `roles`

| Column       | Type         | Description                          |
|--------------|--------------|--------------------------------------|
| `id`         | bigint PK    | Auto-increment (Spatie default)      |
| `name`       | varchar(125) | Role name (unique per guard)         |
| `guard_name` | varchar(125) | Auth guard — `sanctum`               |
| `created_at` | timestamp    |                                      |
| `updated_at` | timestamp    |                                      |

### `permissions`

| Column       | Type         | Description                          |
|--------------|--------------|--------------------------------------|
| `id`         | bigint PK    | Auto-increment                       |
| `name`       | varchar(125) | Permission name — `domain.action`    |
| `guard_name` | varchar(125) | Auth guard — `sanctum`               |
| `created_at` | timestamp    |                                      |
| `updated_at` | timestamp    |                                      |

### `model_has_roles` (User ↔ Role)

| Column       | Type         | Description                         |
|--------------|--------------|-------------------------------------|
| `role_id`    | bigint FK    | References `roles.id`               |
| `model_type` | varchar(255) | Morphable model class (User)        |
| `model_id`   | uuid         | UUID of the User                    |

### `model_has_permissions` (User ↔ Permission direct)

| Column          | Type         | Description                     |
|-----------------|--------------|---------------------------------|
| `permission_id` | bigint FK    | References `permissions.id`     |
| `model_type`    | varchar(255) | Morphable model class           |
| `model_id`      | uuid         | UUID of the User                |

### `role_has_permissions` (Role ↔ Permission)

| Column          | Type      | Description                     |
|-----------------|-----------|---------------------------------|
| `permission_id` | bigint FK | References `permissions.id`     |
| `role_id`       | bigint FK | References `roles.id`           |

---

## Roles

| # | Role                | Slug                  | Description                                           |
|---|---------------------|-----------------------|-------------------------------------------------------|
| 1 | Super Admin         | `super_admin`         | Full system access — cannot be deleted or deactivated |
| 2 | Owner               | `owner`               | Organization owner — full access to own organization  |
| 3 | Branch Manager      | `branch_manager`      | Full access to assigned branch                        |
| 4 | Doctor              | `doctor`              | Clinical access — EMR, treatment, appointments        |
| 5 | Dentist Specialist  | `dentist_specialist`  | Senior clinical access — same as doctor + referrals   |
| 6 | Nurse               | `nurse`               | Assists doctors — limited clinical write access       |
| 7 | Receptionist        | `receptionist`        | Patient registration, appointments, front desk        |
| 8 | Cashier             | `cashier`             | Finance transactions, invoicing, payments             |
| 9 | Pharmacist          | `pharmacist`          | Pharmacy & medication dispensing                      |
| 10 | Laboratory          | `laboratory`          | Lab requests and results                              |
| 11 | Inventory Staff     | `inventory_staff`     | Stock management and procurement                      |
| 12 | HR                  | `hr`                  | Staff management, attendance, payroll                 |
| 13 | Finance             | `finance`             | Full financial reporting and management               |
| 14 | Marketing           | `marketing`           | CRM, promotions, patient communications               |
| 15 | Customer Service    | `customer_service`    | Patient inquiries, complaints, CRM read access        |

---

## Permissions

Permission naming convention: **`{domain}.{action}`**

Actions: `view` · `create` · `update` · `delete` · `restore` · `export`

### Organization Permissions (`organization.*`)

| Permission              | Description                          |
|-------------------------|--------------------------------------|
| `organization.view`     | View organization profile            |
| `organization.update`   | Update organization profile          |

### Branch Permissions (`branch.*`)

| Permission          | Description               |
|---------------------|---------------------------|
| `branch.view`       | View branches             |
| `branch.create`     | Create new branch         |
| `branch.update`     | Update branch             |
| `branch.delete`     | Delete branch (soft)      |
| `branch.restore`    | Restore deleted branch    |

### User Permissions (`user.*`)

| Permission      | Description               |
|-----------------|---------------------------|
| `user.view`     | View users                |
| `user.create`   | Create new user           |
| `user.update`   | Update user               |
| `user.delete`   | Delete user (soft)        |
| `user.restore`  | Restore deleted user      |

### Role & Permission (`role.*`, `permission.*`)

| Permission           | Description                  |
|----------------------|------------------------------|
| `role.view`          | View roles                   |
| `role.assign`        | Assign roles to users        |
| `permission.view`    | View permissions             |
| `permission.assign`  | Assign permissions to roles  |

### Patient Permissions (`patient.*`)

| Permission         | Description                |
|--------------------|----------------------------|
| `patient.view`     | View patient list & detail |
| `patient.create`   | Register new patient       |
| `patient.update`   | Update patient data        |
| `patient.delete`   | Delete patient (soft)      |
| `patient.export`   | Export patient data        |

### Appointment Permissions (`appointment.*`)

| Permission             | Description               |
|------------------------|---------------------------|
| `appointment.view`     | View appointments         |
| `appointment.create`   | Create appointment        |
| `appointment.update`   | Update appointment        |
| `appointment.delete`   | Cancel appointment        |
| `appointment.export`   | Export appointment data   |

### Medical Record Permissions (`medical_record.*`)

| Permission               | Description               |
|--------------------------|---------------------------|
| `medical_record.view`    | View EMR / medical record |
| `medical_record.create`  | Create EMR entry          |
| `medical_record.update`  | Update EMR entry          |
| `medical_record.delete`  | Delete EMR entry          |

### Odontogram Permissions (`odontogram.*`)

| Permission            | Description              |
|-----------------------|--------------------------|
| `odontogram.view`     | View odontogram          |
| `odontogram.create`   | Create odontogram entry  |
| `odontogram.update`   | Update odontogram        |

### Treatment Permissions (`treatment.*`)

| Permission           | Description              |
|----------------------|--------------------------|
| `treatment.view`     | View treatments          |
| `treatment.create`   | Create treatment plan    |
| `treatment.update`   | Update treatment         |
| `treatment.delete`   | Delete treatment         |

### Inventory Permissions (`inventory.*`)

| Permission            | Description              |
|-----------------------|--------------------------|
| `inventory.view`      | View inventory           |
| `inventory.create`    | Add inventory item       |
| `inventory.update`    | Update stock             |
| `inventory.delete`    | Delete inventory item    |
| `inventory.export`    | Export inventory report  |

### Finance Permissions (`finance.*`)

| Permission          | Description                   |
|---------------------|-------------------------------|
| `finance.view`      | View invoices & transactions  |
| `finance.create`    | Create invoice / transaction  |
| `finance.update`    | Update finance record         |
| `finance.delete`    | Void transaction              |
| `finance.export`    | Export financial reports      |

### Asset Permissions (`asset.*`)

| Permission       | Description         |
|------------------|---------------------|
| `asset.view`     | View clinic assets  |
| `asset.create`   | Register asset      |
| `asset.update`   | Update asset        |
| `asset.delete`   | Delete asset        |

### CRM Permissions (`crm.*`)

| Permission     | Description              |
|----------------|--------------------------|
| `crm.view`     | View CRM data            |
| `crm.create`   | Create CRM entry         |
| `crm.update`   | Update CRM entry         |
| `crm.delete`   | Delete CRM entry         |

### Dashboard Permissions (`dashboard.*`)

| Permission          | Description                 |
|---------------------|-----------------------------|
| `dashboard.view`    | Access dashboard            |
| `dashboard.export`  | Export dashboard data       |

### Report Permissions (`report.*`)

| Permission        | Description              |
|-------------------|--------------------------|
| `report.view`     | View reports             |
| `report.export`   | Export reports           |

---

## Role–Permission Matrix

| Permission Group | super_admin | owner | branch_manager | doctor | dentist_specialist | nurse | receptionist | cashier | pharmacist | laboratory | inventory_staff | hr | finance | marketing | customer_service |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `organization.*`   | ✅ | ✅ | — | — | — | — | — | — | — | — | — | — | — | — | — |
| `branch.*`         | ✅ | ✅ | view | — | — | — | — | — | — | — | — | — | — | — | — |
| `user.*`           | ✅ | ✅ | view,create,update | — | — | — | — | — | — | — | — | ✅ | — | — | — |
| `role.*`           | ✅ | ✅ | — | — | — | — | — | — | — | — | — | — | — | — | — |
| `permission.*`     | ✅ | — | — | — | — | — | — | — | — | — | — | — | — | — | — |
| `patient.*`        | ✅ | ✅ | ✅ | ✅ | ✅ | view,update | view,create,update | — | — | — | — | — | — | view | view |
| `appointment.*`    | ✅ | ✅ | ✅ | ✅ | ✅ | view,update | ✅ | — | — | — | — | — | — | — | view |
| `medical_record.*` | ✅ | ✅ | view | ✅ | ✅ | view,create | — | — | — | — | — | — | — | — | — |
| `odontogram.*`     | ✅ | ✅ | view | ✅ | ✅ | view | — | — | — | — | — | — | — | — | — |
| `treatment.*`      | ✅ | ✅ | view | ✅ | ✅ | view,create | — | — | — | — | — | — | — | — | — |
| `inventory.*`      | ✅ | ✅ | view | — | — | — | — | — | — | — | ✅ | — | — | — | — |
| `finance.*`        | ✅ | ✅ | view | — | — | — | — | view,create | — | — | — | — | ✅ | — | — |
| `asset.*`          | ✅ | ✅ | view | — | — | — | — | — | — | — | view,create,update | — | — | — | — |
| `crm.*`            | ✅ | ✅ | view | — | — | — | view | — | — | — | — | — | — | ✅ | view |
| `dashboard.*`      | ✅ | ✅ | ✅ | view | view | view | view | view | view | view | view | view | ✅ | view | view |
| `report.*`         | ✅ | ✅ | view,export | — | — | — | — | — | — | — | view,export | view,export | ✅ | view | — |

> ✅ = Full access (view + create + update + delete + restore + export where applicable)

---

## Business Rules

1. **Super Admin** cannot be deleted, deactivated, or have their role removed.
2. **Super Admin** has access to all permissions across all organizations.
3. **Owner** has full access within their own organization — cannot access other organizations.
4. Roles are always scoped to `guard_name = 'sanctum'`.
5. A user can have **multiple roles** within the same organization.
6. Permissions can be assigned directly to a user OR inherited via a role.
7. Direct user permissions **override** role permissions (Spatie behavior).
8. Roles and permissions are seeded at system setup — not created via UI by default.
9. New modules MUST define their permissions in a seeder before going to production.
10. Permission names follow strict `{domain}.{action}` convention — never deviate.

---

## Implementation Notes

```php
// Assign role to user
$user->assignRole('doctor');

// Assign multiple roles
$user->assignRole(['receptionist', 'cashier']);

// Check permission
$user->can('patient.view');
$user->hasPermissionTo('patient.create');

// Check role
$user->hasRole('doctor');
$user->hasAnyRole(['doctor', 'nurse']);

// Middleware (Route)
Route::middleware('permission:patient.view')
Route::middleware('role:doctor|nurse')
```

---

## Seeder Structure

```
database/seeders/
└── RolePermissionSeeder.php
    ├── createPermissions()   — creates all permissions
    ├── createRoles()         — creates all roles
    └── assignPermissions()   — assigns permissions to roles
```

---

## Notes

- Spatie uses `bigint` for role/permission IDs (not UUID). This is intentional and Spatie-standard.
- The `model_id` column in pivot tables uses `uuid` type since our User model uses UUID primary key.
- The Spatie migration must be published and modified to support UUID model keys.
- `guard_name` is set to `sanctum` to match the Laravel Sanctum authentication driver.
- Cache is cleared automatically after role/permission changes via `php artisan permission:cache-reset`.
