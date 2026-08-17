# Phase 17 — Billing ERD

**Date:** 2026-08-17 | **Phase:** 17 — Billing | **Status:** STEP_17_04_DRAFT

## 1. Entity — `invoices`

```mermaid
erDiagram
    invoices {
        uuid id PK
        uuid organization_id FK "NOT NULL — RESTRICT"
        uuid patient_id FK "NULL — SET NULL"
        varchar50 invoice_number UK "UNIQUE"
        decimal12_2 total_amount "NOT NULL"
        decimal12_2 paid_amount "NOT NULL DEFAULT 0"
        varchar20 status "NOT NULL DEFAULT draft"
        date due_date "NULL"
        jsonb items "NULL"
        text notes "NULL"
        uuid created_by "NULL — HasAudit"
        uuid updated_by "NULL — HasAudit"
        uuid deleted_by "NULL — HasAudit"
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at "Soft delete"
    }

    invoices }o--|| organizations : "organization_id — RESTRICT"
    invoices }o--o| patients : "patient_id — SET NULL"
```

## 2. Entity Specification

| # | Column | Type | Nullable | Default | Key | FK |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK | — |
| 2 | `organization_id` | `uuid` | NOT NULL | — | — | → organizations.id RESTRICT |
| 3 | `patient_id` | `uuid` | NULL | — | — | → patients.id SET NULL |
| 4 | `invoice_number` | `varchar(50)` | NOT NULL | — | UK | — |
| 5 | `total_amount` | `decimal(12,2)` | NOT NULL | — | — | — |
| 6 | `paid_amount` | `decimal(12,2)` | NOT NULL | `0` | — | — |
| 7 | `status` | `varchar(20)` | NOT NULL | `draft` | — | — |
| 8 | `due_date` | `date` | NULL | — | — | — |
| 9 | `items` | `jsonb` | NULL | — | — | — |
| 10 | `notes` | `text` | NULL | — | — | — |
| 11 | `created_by` | `uuid` | NULL | — | — | — |
| 12 | `updated_by` | `uuid` | NULL | — | — | — |
| 13 | `deleted_by` | `uuid` | NULL | — | — | — |
| 14 | `created_at` | `timestamptz` | NOT NULL | CURRENT_TIMESTAMP | — | — |
| 15 | `updated_at` | `timestamptz` | NULL | — | — | — |
| 16 | `deleted_at` | `timestamptz` | NULL | — | — | — |

**16 columns.**

## 3. Relationship Inventory

| # | Child | FK | Parent | Cardinality | ON DELETE | Optional? |
|---|---|---|---|---|---|---|
| 1 | `invoices` | `organization_id` | `organizations` | N:1 | RESTRICT | No |
| 2 | `invoices` | `patient_id` | `patients` | N:1 | SET NULL | Yes |

**2 relationships. 0 CASCADE.**

## 4. Constraint & Index Inventory

| Entity | Constraint/Index | Columns | Type |
|---|---|---|---|
| `invoices` | PK | `(id)` | PRIMARY KEY |
| `invoices` | `invoices_invoice_number_unique` | `(invoice_number)` | UNIQUE |
| `invoices` | `invoices_org_status_idx` | `(organization_id, status)` | Composite B-tree |
| `invoices` | `invoices_org_created_at_idx` | `(organization_id, created_at)` | Composite B-tree |
| `invoices` | `invoices_due_date_idx` | `(due_date)` | B-tree |

**5 indexes (1 PK + 1 UK + 3 composite).**

## 5. Model Guidance

```php
use App\Core\Base\BaseModel;

class Billing extends BaseModel
{
    protected $table = 'invoices';

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'items'         => 'array',
        'due_date'      => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
}
```

- Extends `BaseModel` (HasUuid, HasAudit, SoftDeletes, $guarded=[])
- `items` cast to `array` for JSONB column
- `status` enum: `InvoiceStatus` (draft, sent, paid, overdue, cancelled)