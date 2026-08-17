# AI Engine — Entity Relationship Diagram

## Entity: ai_queries

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `id` | `uuid` | NO | — | Primary key (ordered UUID) |
| `organization_id` | `uuid` | NO | — | FK → organizations(id) ON DELETE RESTRICT |
| `user_id` | `uuid` | YES | — | FK → users(id) ON DELETE SET NULL |
| `query_type` | `varchar(50)` | NO | — | Classification: diagnosis_suggestion, treatment_recommendation, etc. |
| `prompt` | `text` | NO | — | Encrypted at rest via Laravel encryption |
| `response` | `text` | YES | — | Encrypted at rest via Laravel encryption |
| `model` | `varchar(50)` | YES | — | AI model identifier (e.g. gpt-4, claude-3) |
| `tokens_used` | `integer` | YES | — | Total tokens (input + output) |
| `status` | `varchar(20)` | NO | `pending` | CHECK: pending, processing, completed, failed |
| `error_message` | `text` | YES | — | Error detail when status is failed |
| `created_by` | `uuid` | YES | — | FK → users(id) |
| `updated_by` | `uuid` | YES | — | FK → users(id) |
| `deleted_by` | `uuid` | YES | — | FK → users(id) |
| `created_at` | `timestamptz` | NO | `CURRENT_TIMESTAMP` | |
| `updated_at` | `timestamptz` | YES | — | |
| `deleted_at` | `timestamptz` | YES | — | Soft delete |

## Relationships

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│ organizations│       │  ai_queries  │       │    users     │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id (PK)      │───→──│organization_id│       │ id (PK)      │
│ company_name │       │ (FK, NOT NULL)│←───│              │
│ ...          │       │              │       │ name         │
└──────────────┘       │ user_id      │───→──│ email        │
                       │ (FK, NULLABLE)│      │ ...          │
                       │              │       └──────────────┘
                       │ query_type   │
                       │ prompt       │
                       │ response     │
                       │ model        │
                       │ tokens_used  │
                       │ status       │
                       │ error_message│
                       │ created_by   │
                       │ updated_by   │
                       │ deleted_by   │
                       │ created_at   │
                       │ updated_at   │
                       │ deleted_at   │
                       └──────────────┘
```

## Indexes

| Index | Columns | Purpose |
|---|---|---|
| `idx_ai_queries_org_type` | `(organization_id, query_type)` | List filtering by org + query type |
| `idx_ai_queries_org_created` | `(organization_id, created_at)` | List filtering by org + date range |

## Constraints

| Constraint | Definition |
|---|---|
| `pk_ai_queries` | `PRIMARY KEY (id)` |
| `fk_ai_queries_org` | `FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE RESTRICT` |
| `fk_ai_queries_user` | `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL` |
| `ck_ai_queries_status` | `CHECK (status IN ('pending', 'processing', 'completed', 'failed'))` |

## PostgreSQL DDL

```sql
CREATE TABLE ai_queries (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    user_id UUID,
    query_type VARCHAR(50) NOT NULL,
    prompt TEXT NOT NULL,
    response TEXT,
    model VARCHAR(50),
    tokens_used INTEGER,
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'processing', 'completed', 'failed')),
    error_message TEXT,
    created_by UUID,
    updated_by UUID,
    deleted_by UUID,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ,
    deleted_at TIMESTAMPTZ,
    CONSTRAINT fk_ai_queries_org FOREIGN KEY (organization_id)
        REFERENCES organizations(id) ON DELETE RESTRICT,
    CONSTRAINT fk_ai_queries_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ai_queries_org_type ON ai_queries (organization_id, query_type);
CREATE INDEX idx_ai_queries_org_created ON ai_queries (organization_id, created_at);
```