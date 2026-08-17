# Phase 16 — Treatment ERD

**Date:** 2026-08-17 | **Phase:** 16 — Treatment | **Status:** STEP_16_04_DRAFT

## 1. Entity — `treatments`

```mermaid
erDiagram
    treatments {
        uuid id PK
        uuid organization_id FK "NOT NULL — RESTRICT"
        uuid patient_id FK "NOT NULL — RESTRICT"
        uuid doctor_id FK "NULL — SET NULL"
        uuid appointment_id FK "NULL — SET NULL"
        varchar50 treatment_type "NOT NULL"
        varchar20 status "NOT NULL DEFAULT planned"
        decimal12_2 cost "NULL"
        text description "NULL"
        jsonb procedure_data "NULL"
        uuid created_by "NULL — HasAudit"
        uuid updated_by "NULL — HasAudit"
        uuid deleted_by "NULL — HasAudit"
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at "Soft delete"
    }

    treatments }o--|| organizations : "organization_id — RESTRICT"
    treatments }o--|| patients : "patient_id — RESTRICT"
    treatments }o--o| doctors : "doctor_id — SET NULL"
    treatments }o--o| appointments : "appointment_id — SET NULL"
```

## 2. Entity Specification

| # | Column | Type | Nullable | Default | Key | FK |
|---|---|---|---|---|---|---|
| 1 | `id` | `uuid` | NOT NULL | — | PK | — |
| 2 | `organization_id` | `uuid` | NOT NULL | — | — | → organizations.id RESTRICT |
| 3 | `patient_id` | `uuid` | NOT NULL | — | — | → patients.id RESTRICT |
| 4 | `doctor_id` | `uuid` | NULL | — | — | → doctors.id SET NULL |
| 5 | `appointment_id` | `uuid` | NULL | — | — | → appointments.id SET NULL |
| 6 | `treatment_type` | `varchar(50)` | NOT NULL | — | — | — |
| 7 | `status` | `varchar(20)` | NOT NULL | `planned` | — | — |
| 8 | `cost` | `decimal(12,2)` | NULL | — | — | — |
| 9 | `description` | `text` | NULL | — | — | — |
| 10 | `procedure_data` | `jsonb` | NULL | — | — | — |
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
| 1 | `treatments` | `organization_id` | `organizations` | N:1 | RESTRICT | No |
| 2 | `treatments` | `patient_id` | `patients` | N:1 | RESTRICT | No |
| 3 | `treatments` | `doctor_id` | `doctors` | N:1 | SET NULL | Yes |
| 4 | `treatments` | `appointment_id` | `appointments` | N:1 | SET NULL | Yes |

**4 relationships. 0 CASCADE.**

## 4. Constraint & Index Inventory

| Entity | Constraint/Index | Columns | Type |
|---|---|---|---|
| `treatments` | PK | `(id)` | PRIMARY KEY |
| `treatments` | `treatments_org_patient_idx` | `(organization_id, patient_id)` | Composite B-tree |
| `treatments` | `treatments_org_status_idx` | `(organization_id, status)` | Composite B-tree |
| `treatments` | `treatments_type_idx` | `(treatment_type)` | B-tree |

**4 indexes (1 PK + 3 composite).**

## 5. Model Guidance

```php
use App\Core\Base\BaseModel;

class Treatment extends BaseModel
{
    protected $table = 'treatments';

    protected $casts = [
        'cost'           => 'decimal:2',
        'procedure_data' => 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}
```

- Extends `BaseModel` (HasUuid, HasAudit, SoftDeletes, $guarded=[])
- `procedure_data` cast to `array` for JSONB column
- `status` enum: `TreatmentStatus` (planned, in_progress, completed, cancelled)