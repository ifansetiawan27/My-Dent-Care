# Phase 24 — CRM ERD

**Date:** 2026-08-17 | **Phase:** 24 — CRM | **Status:** STEP_24_04_DRAFT

## 1. Entity — `crm_contacts`

```mermaid
erDiagram
    crm_contacts {
        uuid id PK
        uuid organization_id FK "NOT NULL — RESTRICT"
        uuid patient_id FK "NULL — SET NULL"
        varchar50 contact_type "NOT NULL"
        varchar50 channel "NULL"
        varchar200 subject "NULL"
        text message "NULL"
        varchar20 status "NOT NULL DEFAULT 'new'"
        date follow_up_date "NULL"
        text resolution "NULL"
        uuid created_by "NULL — HasAudit"
        uuid updated_by "NULL — HasAudit"
        uuid deleted_by "NULL — HasAudit"
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at "Soft delete"
    }

    crm_contacts }o--|| organizations : "organization_id — RESTRICT"
    crm_contacts }o--o| patients : "patient_id — SET NULL"
```

## 2. Entity Specification

| # | Column | Type | Nullable | Default | Key | FK |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK | — |
| 2 | `organization_id` | `uuid` | NOT NULL | — | — | → organizations.id RESTRICT |
| 3 | `patient_id` | `uuid` | NULL | — | — | → patients.id SET NULL |
| 4 | `contact_type` | `varchar(50)` | NOT NULL | — | — | — |
| 5 | `channel` | `varchar(50)` | NULL | — | — | — |
| 6 | `subject` | `varchar(200)` | NULL | — | — | — |
| 7 | `message` | `text` | NULL | — | — | — |
| 8 | `status` | `varchar(20)` | NOT NULL | `new` | — | — |
| 9 | `follow_up_date` | `date` | NULL | — | — | — |
| 10 | `resolution` | `text` | NULL | — | — | — |
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
| 1 | `crm_contacts` | `organization_id` | `organizations` | N:1 | RESTRICT | No |
| 2 | `crm_contacts` | `patient_id` | `patients` | N:1 | SET NULL | Yes |

**2 relationships. 0 CASCADE.**

## 4. Constraint & Index Inventory

| Entity | Constraint/Index | Columns | Type |
|---|---|---|---|
| `crm_contacts` | PK | `(id)` | PRIMARY KEY |
| `crm_contacts` | `crm_contacts_org_status_idx` | `(organization_id, status)` | Composite B-tree |
| `crm_contacts` | `crm_contacts_org_contact_type_idx` | `(organization_id, contact_type)` | Composite B-tree |
| `crm_contacts` | `crm_contacts_follow_up_date_idx` | `(follow_up_date)` | B-tree |

**4 indexes (1 PK + 3 composite/single).**

## 5. Model Guidance

```php
use App\Core\Base\BaseModel;

class CRM extends BaseModel
{
    protected $table = 'crm_contacts';

    protected $casts = [
        'follow_up_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}
```

- Extends `BaseModel` (HasUuid, HasAudit, SoftDeletes, $guarded=[])