# Phase 19 — Pharmacy ERD

**Date:** 2026-08-17 | **Phase:** 19 — Pharmacy | **Status:** STEP_19_04_DRAFT

## 1. Entity — `pharmacy_items`

```mermaid
erDiagram
    pharmacy_items {
        uuid id PK
        uuid organization_id FK "NOT NULL — RESTRICT"
        uuid branch_id FK "NULL — SET NULL"
        varchar50 drug_code UK "UNIQUE"
        varchar200 name "NOT NULL"
        varchar50 category "NULL"
        decimal12_2 quantity "NOT NULL DEFAULT 0"
        varchar20 unit "NULL"
        decimal12_2 unit_price "NULL"
        date expiry_date "NULL"
        varchar50 batch_number "NULL"
        boolean is_active "NOT NULL DEFAULT true"
        uuid created_by "NULL — HasAudit"
        uuid updated_by "NULL — HasAudit"
        uuid deleted_by "NULL — HasAudit"
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at "Soft delete"
    }

    pharmacy_items }o--|| organizations : "organization_id — RESTRICT"
    pharmacy_items }o--o| branches : "branch_id — SET NULL"
```

## 2. Entity Specification

| # | Column | Type | Nullable | Default | Key | FK |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK | — |
| 2 | `organization_id` | `uuid` | NOT NULL | — | — | → organizations.id RESTRICT |
| 3 | `branch_id` | `uuid` | NULL | — | — | → branches.id SET NULL |
| 4 | `drug_code` | `varchar(50)` | NOT NULL | — | UK | — |
| 5 | `name` | `varchar(200)` | NOT NULL | — | — | — |
| 6 | `category` | `varchar(50)` | NULL | — | — | — |
| 7 | `quantity` | `decimal(12,2)` | NOT NULL | `0` | — | — |
| 8 | `unit` | `varchar(20)` | NULL | — | — | — |
| 9 | `unit_price` | `decimal(12,2)` | NULL | — | — | — |
| 10 | `expiry_date` | `date` | NULL | — | — | — |
| 11 | `batch_number` | `varchar(50)` | NULL | — | — | — |
| 12 | `is_active` | `boolean` | NOT NULL | `true` | — | — |
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
| 1 | `pharmacy_items` | `organization_id` | `organizations` | N:1 | RESTRICT | No |
| 2 | `pharmacy_items` | `branch_id` | `branches` | N:1 | SET NULL | Yes |

**2 relationships. 0 CASCADE.**

## 4. Constraint & Index Inventory

| Entity | Constraint/Index | Columns | Type |
|---|---|---|---|
| `pharmacy_items` | PK | `(id)` | PRIMARY KEY |
| `pharmacy_items` | `pharmacy_items_drug_code_unique` | `(drug_code)` | UNIQUE |
| `pharmacy_items` | `pharmacy_items_org_is_active_idx` | `(organization_id, is_active)` | Composite B-tree |
| `pharmacy_items` | `pharmacy_items_expiry_date_idx` | `(expiry_date)` | B-tree |
| `pharmacy_items` | `pharmacy_items_batch_number_idx` | `(batch_number)` | B-tree |

**5 indexes (1 PK + 1 UK + 1 composite + 2 single).**

## 5. Model Guidance

```php
use App\Core\Base\BaseModel;

class Pharmacy extends BaseModel
{
    protected $table = 'pharmacy_items';

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'expiry_date' => 'date',
        'is_active'   => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}
```

- Extends `BaseModel` (HasUuid, HasAudit, SoftDeletes, $guarded=[])
- `is_active` cast to `boolean`
- `expiry_date` cast to `date`