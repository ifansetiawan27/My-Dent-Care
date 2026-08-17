# Phase 26 — Dashboard ERD

**Date:** 2026-08-17 | **Phase:** 26 — Dashboard | **Status:** STEP_26_04_DRAFT

## 1. Entity — `dashboards`

```mermaid
erDiagram
    dashboards {
        uuid id PK
        uuid organization_id FK "NOT NULL — RESTRICT"
        uuid user_id FK "NULL — SET NULL"
        varchar200 name "NOT NULL"
        jsonb config "NULL"
        jsonb widgets "NULL"
        boolean is_default "NOT NULL DEFAULT false"
        uuid created_by "NULL — HasAudit"
        uuid updated_by "NULL — HasAudit"
        uuid deleted_by "NULL — HasAudit"
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at "Soft delete"
    }

    dashboards }o--|| organizations : "organization_id — RESTRICT"
    dashboards }o--o| users : "user_id — SET NULL"
```

## 2. Entity Specification

| # | Column | Type | Nullable | Default | Key | FK |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK | — |
| 2 | `organization_id` | `uuid` | NOT NULL | — | — | → organizations.id RESTRICT |
| 3 | `user_id` | `uuid` | NULL | — | — | → users.id SET NULL |
| 4 | `name` | `varchar(200)` | NOT NULL | — | — | — |
| 5 | `config` | `jsonb` | NULL | — | — | — |
| 6 | `widgets` | `jsonb` | NULL | — | — | — |
| 7 | `is_default` | `boolean` | NOT NULL | `false` | — | — |
| 8 | `created_by` | `uuid` | NULL | — | — | — |
| 9 | `updated_by` | `uuid` | NULL | — | — | — |
| 10 | `deleted_by` | `uuid` | NULL | — | — | — |
| 11 | `created_at` | `timestamptz` | NOT NULL | CURRENT_TIMESTAMP | — | — |
| 12 | `updated_at` | `timestamptz` | NULL | — | — | — |
| 13 | `deleted_at` | `timestamptz` | NULL | — | — | — |

**13 columns.**

## 3. Relationship Inventory

| # | Child | FK | Parent | Cardinality | ON DELETE | Optional? |
|---|---|---|---|---|---|---|
| 1 | `dashboards` | `organization_id` | `organizations` | N:1 | RESTRICT | No |
| 2 | `dashboards` | `user_id` | `users` | N:1 | SET NULL | Yes |

**2 relationships. 0 CASCADE.**

## 4. Constraint & Index Inventory

| Entity | Constraint/Index | Columns | Type |
|---|---|---|---|
| `dashboards` | PK | `(id)` | PRIMARY KEY |
| `dashboards` | `dashboards_org_user_id_idx` | `(organization_id, user_id)` | Composite B-tree |

**2 indexes (1 PK + 1 composite).**

## 5. Model Guidance

```php
use App\Core\Base\BaseModel;

class Dashboard extends BaseModel
{
    protected $table = 'dashboards';

    protected $casts = [
        'config'     => 'array',
        'widgets'    => 'array',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
```

- Extends `BaseModel` (HasUuid, HasAudit, SoftDeletes, $guarded=[])