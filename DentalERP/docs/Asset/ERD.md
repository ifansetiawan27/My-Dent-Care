# Phase 22 — Asset ERD

**Date:** 2026-08-17 | **Phase:** 22 — Asset | **Status:** STEP_22_04_DRAFT

## 1. Entity — `assets`

```mermaid
erDiagram
    assets {
        uuid id PK
        uuid organization_id FK "NOT NULL — RESTRICT"
        uuid branch_id FK "NULL — SET NULL"
        uuid category_id FK "NULL — SET NULL"
        varchar50 asset_code UK "UNIQUE"
        varchar200 name "NOT NULL"
        text description "NULL"
        date purchase_date "NULL"
        decimal12_2 purchase_price "NULL"
        varchar20 status "NOT NULL DEFAULT 'active'"
        date warranty_expiry "NULL"
        text notes "NULL"
        uuid created_by "NULL — HasAudit"
        uuid updated_by "NULL — HasAudit"
        uuid deleted_by "NULL — HasAudit"
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at "Soft delete"
    }

    assets }o--|| organizations : "organization_id — RESTRICT"
    assets }o--o| branches : "branch_id — SET NULL"
    assets }o--o| asset_categories : "category_id — SET NULL"
```

## 2. Entity Specification

| # | Column | Type | Nullable | Default | Key | FK |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK | — |
| 2 | `organization_id` | `uuid` | NOT NULL | — | — | → organizations.id RESTRICT |
| 3 | `branch_id` | `uuid` | NULL | — | — | → branches.id SET NULL |
| 4 | `category_id` | `uuid` | NULL | — | — | → asset_categories.id SET NULL |
| 5 | `asset_code` | `varchar(50)` | NOT NULL | — | UK | — |
| 6 | `name` | `varchar(200)` | NOT NULL | — | — | — |
| 7 | `description` | `text` | NULL | — | — | — |
| 8 | `purchase_date` | `date` | NULL | — | — | — |
| 9 | `purchase_price` | `decimal(12,2)` | NULL | — | — | — |
| 10 | `status` | `varchar(20)` | NOT NULL | `active` | — | — |
| 11 | `warranty_expiry` | `date` | NULL | — | — | — |
| 12 | `notes` | `text` | NULL | — | — | — |
| 13 | `created_by` | `uuid` | NULL | — | — | — |
| 14 | `updated_by` | `uuid` | NULL | — | — | — |
| 15 | `deleted_by` | `uuid` | NULL | — | — | — |
| 16 | `created_at` | `timestamptz` | NOT NULL | CURRENT_TIMESTAMP | — | — |
| 17 | `updated_at` | `timestamptz` | NULL | — | — | — |
| 18 | `deleted_at` | `timestamptz` | NULL | — | — | — |

**18 columns.**

## 3. Relationship Inventory

| # | Child | FK | Parent | Cardinality | ON DELETE | Optional? |
|---|---|---|---|---|---|---|
| 1 | `assets` | `organization_id` | `organizations` | N:1 | RESTRICT | No |
| 2 | `assets` | `branch_id` | `branches` | N:1 | SET NULL | Yes |
| 3 | `assets` | `category_id` | `asset_categories` | N:1 | SET NULL | Yes |

**3 relationships. 0 CASCADE.**

## 4. Constraint & Index Inventory

| Entity | Constraint/Index | Columns | Type |
|---|---|---|---|
| `assets` | PK | `(id)` | PRIMARY KEY |
| `assets` | `assets_asset_code_unique` | `(asset_code)` | UNIQUE |
| `assets` | `assets_org_status_idx` | `(organization_id, status)` | Composite B-tree |
| `assets` | `assets_category_id_idx` | `(category_id)` | B-tree |

**4 indexes (1 PK + 1 UK + 2 composite/single).**

## 5. Model Guidance

```php
use App\Core\Base\BaseModel;

class Asset extends BaseModel
{
    protected $table = 'assets';

    protected $casts = [
        'purchase_date'   => 'date',
        'purchase_price'  => 'decimal:2',
        'warranty_expiry' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];
}
```

- Extends `BaseModel` (HasUuid, HasAudit, SoftDeletes, $guarded=[])