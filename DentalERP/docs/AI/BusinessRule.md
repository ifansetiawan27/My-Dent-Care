# AI Engine Business Rules

## Rule Catalog

| Rule ID | Rule | Scope |
|---|---|---|
| `BR-AI-001` | Every AI query must belong to exactly one Organization. Organization assignment is derived from the authenticated User's context and cannot be overridden by API input. | Creation |
| `BR-AI-002` | User ID is optional on AI queries. When provided, it is set from the authenticated user. When null, the query is system-initiated. User FK uses SET NULL on delete. | Creation |
| `BR-AI-003` | Prompt and response text must be encrypted at rest using Laravel's encryption. Decryption is transparent through Eloquent casts. Plaintext must never be logged or stored in audit trails. | Data Security |
| `BR-AI-004` | AI query listing is always scoped to organization_id. Optional filters include query_type, status, date_from, and date_to. All filters are whitelisted; raw query strings are never passed to the database. | Listing |
| `BR-AI-005` | AI query listing is paginated with default 20 per page, max 100. Results are ordered by created_at DESC. | Pagination |
| `BR-AI-006` | AI query detail retrieval is scoped to organization_id. Queries from other organizations return 404 NotFoundException. | Show |
| `BR-AI-007` | Prompt and response text are truncated in API responses for display safety. Prompt truncated at 200 characters in list, 500 in show. Response truncated at 500 characters in show. | Display |
| `BR-AI-008` | Only AI queries with status `failed` can be retried. Retry transitions status to `pending` and clears error_message. Any other status transition from a non-failed state is rejected with BusinessException. | Retry |
| `BR-AI-009` | Retry is scoped to organization_id. Users can only retry queries within their own organization. | Retry |
| `BR-AI-010` | Only AI queries with status `pending` or `processing` can be cancelled. Cancel transitions status to `failed` and sets error_message to "Cancelled by user". | Cancel |
| `BR-AI-011` | Cancel is scoped to organization_id. Users can only cancel queries within their own organization. | Cancel |
| `BR-AI-012` | Status transitions follow a strict lifecycle: pending → processing, processing → completed, processing → failed, failed → pending (retry only), pending → failed (cancel only), processing → failed (cancel only). All other transitions are rejected. | Status Lifecycle |
| `BR-AI-013` | When a query transitions to `completed`, tokens_used must be a non-negative integer. When transitioning to `failed`, error_message must be populated. | Status Lifecycle |
| `BR-AI-014` | Status transitions are wrapped in database transactions. Partial transitions are rolled back on failure. | Transaction |
| `BR-AI-015` | query_type is required, max 50 characters. It is a free-text classification field for routing and filtering. | Query Type |
| `BR-AI-016` | model is optional, max 50 characters. When provided, it specifies the AI model to use. When null, the system default model is used by the AI processor. | Model Selection |
| `BR-AI-017` | tokens_used tracks the total token count (input + output) for completed queries. It is null for non-completed queries and must be a non-negative integer when set. | Token Usage |
| `BR-AI-018` | error_message captures failure diagnostics. It is null for non-failed queries and populated when status is `failed`. | Error Capture |
| `BR-AI-019` | All AI Engine operations are organization-scoped. Cross-organization data access is a security vulnerability. Every repository method that retrieves or mutates data includes organization_id in the query scope. | Multi-Tenant Isolation |
| `BR-AI-020` | AI queries are Business Records. They use soft delete (deleted_at) and audit trail (created_by, updated_by, deleted_by). Audit columns are auto-filled via HasAudit trait. Soft-deleted records are retained for compliance. | Audit Trail |

## Status Lifecycle

```
                    ┌──────────────┐
                    │   pending    │
                    └──────┬───────┘
                           │
                           ▼
                    ┌──────────────┐
                    │  processing  │
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              ▼                         ▼
     ┌────────────────┐       ┌────────────────┐
     │   completed    │       │     failed     │
     └────────────────┘       └───────┬────────┘
                                      │
                              (retry) │
                                      ▼
                              ┌────────────────┐
                              │    pending     │
                              └────────────────┘
```

### Valid Transitions

| From | To | Trigger | Notes |
|---|---|---|---|
| `pending` | `processing` | System | AI processor picks up query |
| `processing` | `completed` | System | AI inference succeeds |
| `processing` | `failed` | System / User | AI inference fails or user cancels |
| `failed` | `pending` | User | Retry operation; clears error_message |
| `pending` | `failed` | User | Cancel operation; sets error_message |

### Invalid Transitions

| From | To | Reason |
|---|---|---|
| `completed` | Any | Terminal state; immutable |
| `failed` | `processing` | Must go through pending via retry |
| `pending` | `completed` | Must go through processing |
| `processing` | `pending` | Cannot revert from processing |
| Any | Any other | Not in lifecycle |

## Data Classification

| Field | Classification |
|---|---|
| `id` | Core Identity |
| `organization_id` | Tenant Ownership |
| `user_id` | Business Data |
| `query_type` | Business Data |
| `prompt` | Sensitive — encrypted at rest |
| `response` | Sensitive — encrypted at rest |
| `model` | Enrichment Metadata |
| `tokens_used` | Business Data |
| `status` | Lifecycle Generated |
| `error_message` | Business Data |
| `created_by` | Audit Metadata |
| `updated_by` | Audit Metadata |
| `deleted_by` | Audit Metadata |
| `created_at` | Audit Metadata |
| `updated_at` | Audit Metadata |
| `deleted_at` | Audit Metadata |

## Organization Delete Behavior

When an Organization is deleted:
- FK `organization_id → organizations.id` uses `ON DELETE RESTRICT`.
- Organization deletion is blocked while AI queries exist.
- This preserves audit trail integrity.

## User Delete Behavior

When a User is deleted:
- FK `user_id → users.id` uses `ON DELETE SET NULL`.
- The AI query is preserved with user_id set to null.
- The query remains scoped to its organization.