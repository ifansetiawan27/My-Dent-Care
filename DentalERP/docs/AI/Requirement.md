# AI Engine Module Requirements

## Objective

Provide a multi-organization scoped AI query engine that allows authenticated users to submit prompts to AI models, track processing status, and retrieve results. The AI Engine serves as the foundation for all AI-assisted features across the Dental ERP platform.

## Actors

### Super Admin / Organization Owner

- List all AI queries within organization.
- View any AI query detail.
- Create new AI queries.
- Retry failed queries.
- Cancel pending or processing queries.

### Dentist / Staff

- List AI queries within organization.
- View own AI query details.
- Create new AI queries.
- Retry own failed queries.
- Cancel own pending or processing queries.

### System / AI Processor (Background)

- Pick up pending queries.
- Transition status to processing.
- Execute AI model inference.
- Record response and token usage.
- Mark completed or failed.

## Functional Scope

The AI Engine module covers:

- AI query submission with prompt, query type, and optional model selection.
- Status lifecycle management: pending → processing → completed | failed.
- Retry of failed queries.
- Cancellation of pending or processing queries.
- Organization-scoped listing and filtering.
- Token usage tracking.
- Error message capture for failed queries.

## Requirement Catalog

### AI-REQ-001 — Submit AI Query

- **Requirement Statement:** An authenticated User can submit a text prompt with a query type and optional model selection. The query is created with status `pending` and scoped to the user's organization.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Query is persisted with UUID, organization_id, user_id, prompt, query_type, optional model, status `pending`, and audit fields. Returns 201.
- **Business Rule Reference:** `BR-AI-001`, `BR-AI-002`, `BR-AI-003`.

### AI-REQ-002 — List AI Queries

- **Requirement Statement:** An authenticated User can list all AI queries within their organization, with optional filters by query_type, status, and date range.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Paginated response scoped to organization_id. Supports filtering by query_type, status, date_from, date_to. Default sort by created_at DESC.
- **Business Rule Reference:** `BR-AI-004`, `BR-AI-005`.

### AI-REQ-003 — Show AI Query

- **Requirement Statement:** An authenticated User can view a single AI query by ID, scoped to their organization.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Returns full query detail including prompt (truncated if > 200 chars), response (truncated if > 500 chars), status, model, tokens_used, error_message. Returns 404 if not found or not in user's organization.
- **Business Rule Reference:** `BR-AI-006`, `BR-AI-007`.

### AI-REQ-004 — Retry Failed AI Query

- **Requirement Statement:** An authenticated User can retry a failed AI query, resetting its status to `pending` and clearing the error message.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Only queries with status `failed` can be retried. Status transitions to `pending`. Error message is cleared. Returns 200 with updated query.
- **Business Rule Reference:** `BR-AI-008`, `BR-AI-009`.

### AI-REQ-005 — Cancel AI Query

- **Requirement Statement:** An authenticated User can cancel a pending or processing AI query, setting its status to `failed` with an error message indicating cancellation.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Only queries with status `pending` or `processing` can be cancelled. Status transitions to `failed`. Error message is set to "Cancelled by user". Returns 200 with updated query.
- **Business Rule Reference:** `BR-AI-010`, `BR-AI-011`.

### AI-REQ-006 — Process AI Query (Background)

- **Requirement Statement:** The system must be able to pick up pending queries, transition them to processing, execute AI inference, and record the result with token usage.
- **Actor / Scope:** System; internal.
- **Acceptance Criteria:** Pending queries are picked up by a background processor. Status transitions to `processing`. On success, status becomes `completed` with response and tokens_used recorded. On failure, status becomes `failed` with error_message.
- **Business Rule Reference:** `BR-AI-012`, `BR-AI-013`, `BR-AI-014`.

### AI-REQ-007 — Query Type Classification

- **Requirement Statement:** Every AI query must be classified with a query_type to enable filtering, analytics, and routing to appropriate AI handlers.
- **Actor / Scope:** All actors; system.
- **Acceptance Criteria:** query_type is required, max 50 characters. Common types include: `diagnosis_suggestion`, `treatment_recommendation`, `report_summary`, `patient_insight`, `scheduling_optimization`.
- **Business Rule Reference:** `BR-AI-015`.

### AI-REQ-008 — Model Selection

- **Requirement Statement:** Users may optionally specify which AI model to use for their query. If not specified, the system default model is used.
- **Actor / Scope:** All authenticated actors.
- **Acceptance Criteria:** model is optional, max 50 characters. Stored as-is for the AI processor to interpret.
- **Business Rule Reference:** `BR-AI-016`.

### AI-REQ-009 — Token Usage Tracking

- **Requirement Statement:** Token usage (input + output) must be recorded for every completed AI query for cost tracking and analytics.
- **Actor / Scope:** System; internal.
- **Acceptance Criteria:** tokens_used is recorded as a non-negative integer when the query completes. Null for non-completed queries.
- **Business Rule Reference:** `BR-AI-017`.

### AI-REQ-010 — Error Capture

- **Requirement Statement:** When an AI query fails, the error message must be captured for diagnostics and display to the user.
- **Actor / Scope:** System; internal.
- **Acceptance Criteria:** error_message is populated when status transitions to `failed`. Null for non-failed queries.
- **Business Rule Reference:** `BR-AI-018`.

### AI-REQ-011 — Multi-Organization Data Isolation

- **Requirement Statement:** All AI queries are strictly scoped to their organization. Users must never see queries from other organizations.
- **Actor / Scope:** All actors; all queries.
- **Acceptance Criteria:** Every query includes organization_id. Every list, show, retry, and cancel operation is scoped to the authenticated user's organization_id. Cross-organization data access is impossible.
- **Business Rule Reference:** `BR-AI-019`.

### AI-REQ-012 — Audit Trail

- **Requirement Statement:** Every AI query records created_by, updated_by, deleted_by and timestamps for full traceability.
- **Actor / Scope:** All actors; all queries.
- **Acceptance Criteria:** created_by is set on creation. updated_by is set on status changes, retry, and cancel. deleted_by is set on soft delete. All timestamps are timestamptz.
- **Business Rule Reference:** `BR-AI-020`.

### AI-REQ-013 — Soft Delete

- **Requirement Statement:** AI queries support soft deletion. Deleted queries are excluded from listings but retained for audit.
- **Actor / Scope:** Super Admin, Organization Owner; own organization scope.
- **Acceptance Criteria:** Soft delete via deleted_at. Queries with deleted_at IS NOT NULL are excluded from normal queries. Soft-deleted records are retained for audit.
- **Business Rule Reference:** `BR-AI-020`.

### AI-REQ-014 — Status Lifecycle

- **Requirement Statement:** AI queries follow a strict status lifecycle: pending → processing → completed | failed. Invalid transitions are rejected.
- **Actor / Scope:** All actors; system.
- **Acceptance Criteria:** Valid transitions: pending→processing, processing→completed, processing→failed, failed→pending (retry), pending→failed (cancel), processing→failed (cancel). All other transitions are rejected with BusinessException.
- **Business Rule Reference:** `BR-AI-008`, `BR-AI-009`, `BR-AI-010`, `BR-AI-011`.

### AI-REQ-015 — Prompt Encryption

- **Requirement Statement:** AI prompt text must be encrypted at rest to protect sensitive clinical information that may be included in prompts.
- **Actor / Scope:** System; data at rest.
- **Acceptance Criteria:** The `prompt` column is encrypted using Laravel's encryption. Decryption is transparent via Eloquent cast.
- **Business Rule Reference:** `BR-AI-003`.

### AI-REQ-016 — Response Encryption

- **Requirement Statement:** AI response text must be encrypted at rest to protect potentially sensitive AI-generated content.
- **Actor / Scope:** System; data at rest.
- **Acceptance Criteria:** The `response` column is encrypted using Laravel's encryption. Decryption is transparent via Eloquent cast.
- **Business Rule Reference:** `BR-AI-003`.

### AI-REQ-017 — Filtering by Query Type

- **Requirement Statement:** The list endpoint supports filtering by query_type for efficient query management.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** An optional `query_type` query parameter filters results. Multiple values are not supported.
- **Business Rule Reference:** `BR-AI-004`.

### AI-REQ-018 — Filtering by Status

- **Requirement Statement:** The list endpoint supports filtering by status for monitoring and management.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** An optional `status` query parameter filters results. Must be one of: pending, processing, completed, failed.
- **Business Rule Reference:** `BR-AI-004`.

### AI-REQ-019 — Filtering by Date Range

- **Requirement Statement:** The list endpoint supports filtering by creation date range.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Optional `date_from` and `date_to` query parameters filter by created_at. Both are ISO 8601 dates.
- **Business Rule Reference:** `BR-AI-005`.

### AI-REQ-020 — Pagination

- **Requirement Statement:** The list endpoint supports pagination with configurable page size.
- **Actor / Scope:** All authenticated actors; own organization scope.
- **Acceptance Criteria:** Default 20 per page, max 100. Optional `per_page` and `page` query parameters.
- **Business Rule Reference:** `BR-AI-005`.

### AI-REQ-021 — Unauthenticated Access Denied

- **Requirement Statement:** All AI Engine endpoints require authentication. Unauthenticated requests receive 401 Unauthorized.
- **Actor / Scope:** All endpoints.
- **Acceptance Criteria:** Requests without a valid Sanctum token return 401.
- **Business Rule Reference:** `BR-AI-019`.

### AI-REQ-022 — Organization ID From Auth Context

- **Requirement Statement:** The organization_id for AI queries is derived from the authenticated user's context, not from request input. Clients must not supply organization_id.
- **Actor / Scope:** All authenticated actors.
- **Acceptance Criteria:** The StoreAIRequest does not include organization_id as an input field. The controller injects organization_id from auth()->user()->organization_id.
- **Business Rule Reference:** `BR-AI-019`.

### AI-REQ-023 — Prompt Truncation in List

- **Requirement Statement:** When listing AI queries, prompt text is truncated to 200 characters to optimize response size and protect sensitive content in list views.
- **Actor / Scope:** All authenticated actors; list endpoint.
- **Acceptance Criteria:** The prompt field in list responses is truncated with "..." suffix if longer than 200 chars. Full prompt is available in show endpoint (truncated at 500 chars for display).
- **Business Rule Reference:** `BR-AI-003`, `BR-AI-007`.

### AI-REQ-024 — Response Truncation in Display

- **Requirement Statement:** When showing AI query detail, response text is truncated to 500 characters for display purposes.
- **Actor / Scope:** All authenticated actors; show endpoint.
- **Acceptance Criteria:** The response field in show endpoint is truncated with "..." suffix if longer than 500 chars.
- **Business Rule Reference:** `BR-AI-007`.

### AI-REQ-025 — OpenAPI Documentation

- **Requirement Statement:** All AI Engine endpoints are documented in OpenAPI 3.1 format.
- **Actor / Scope:** API consumers.
- **Acceptance Criteria:** Five endpoints documented: list, show, create, retry, cancel. Each with request/response schemas, error codes, and examples.
- **Business Rule Reference:** N/A — documentation standard.