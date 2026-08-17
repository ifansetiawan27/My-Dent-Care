# AI Engine API Specification

## Base URL

```
/api/v1/ai-queries
```

## Authentication

All endpoints require `auth:sanctum` middleware. Requests without a valid token return `401 Unauthorized`.

---

## Endpoints

### 1. List AI Queries

```
GET /api/v1/ai-queries
```

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `query_type` | `string` | No | — | Filter by query type |
| `status` | `string` | No | — | Filter by status (pending, processing, completed, failed) |
| `date_from` | `string` (ISO 8601) | No | — | Filter by created_at >= date_from |
| `date_to` | `string` (ISO 8601) | No | — | Filter by created_at <= date_to |
| `per_page` | `integer` | No | `20` | Items per page (max 100) |
| `page` | `integer` | No | `1` | Page number |

**Response `200 OK`:**

```json
{
  "data": [
    {
      "id": "018d...",
      "organization_id": "018c...",
      "user_id": "018c...",
      "query_type": "diagnosis_suggestion",
      "prompt": "Given the following symptoms... (truncated)",
      "model": "gpt-4",
      "tokens_used": 150,
      "status": "completed",
      "status_label": "Completed",
      "error_message": null,
      "created_by": "018c...",
      "updated_by": "018c...",
      "created_at": "2026-08-17T10:00:00+07:00",
      "updated_at": "2026-08-17T10:00:05+07:00"
    }
  ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

**Errors:**

| Code | Description |
|---|---|
| `401` | Unauthenticated |
| `422` | Invalid filter values |

---

### 2. Show AI Query

```
GET /api/v1/ai-queries/{id}
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | `string` (UUID) | Yes | AI query ID |

**Response `200 OK`:**

```json
{
  "data": {
    "id": "018d...",
    "organization_id": "018c...",
    "user_id": "018c...",
    "query_type": "diagnosis_suggestion",
    "prompt": "Given the following symptoms: tooth pain, sensitivity to cold, swelling in gum area...",
    "response": "Based on the symptoms described, this appears to be...",
    "model": "gpt-4",
    "tokens_used": 350,
    "status": "completed",
    "status_label": "Completed",
    "error_message": null,
    "created_by": "018c...",
    "updated_by": "018c...",
    "created_at": "2026-08-17T10:00:00+07:00",
    "updated_at": "2026-08-17T10:00:05+07:00"
  }
}
```

**Errors:**

| Code | Description |
|---|---|
| `401` | Unauthenticated |
| `404` | AI query not found or not in user's organization |

---

### 3. Create AI Query

```
POST /api/v1/ai-queries
```

**Request Body:**

```json
{
  "query_type": "diagnosis_suggestion",
  "prompt": "Given the following symptoms: tooth pain, sensitivity to cold...",
  "model": "gpt-4"
}
```

| Field | Type | Required | Max Length | Description |
|---|---|---|---|---|
| `query_type` | `string` | Yes | 50 | Classification of the query |
| `prompt` | `string` | Yes | — | The AI prompt text |
| `model` | `string` | No | 50 | AI model identifier |

**Note:** `organization_id` is NOT accepted in the request body. It is derived from the authenticated user's context.

**Response `201 Created`:**

```json
{
  "data": {
    "id": "018d...",
    "organization_id": "018c...",
    "user_id": "018c...",
    "query_type": "diagnosis_suggestion",
    "prompt": "Given the following symptoms: tooth pain, sensitivity to cold...",
    "model": "gpt-4",
    "tokens_used": null,
    "status": "pending",
    "status_label": "Pending",
    "error_message": null,
    "created_by": "018c...",
    "updated_by": null,
    "created_at": "2026-08-17T10:00:00+07:00",
    "updated_at": "2026-08-17T10:00:00+07:00"
  }
}
```

**Errors:**

| Code | Description |
|---|---|
| `401` | Unauthenticated |
| `422` | Validation error (missing query_type or prompt) |

---

### 4. Retry AI Query

```
POST /api/v1/ai-queries/{id}/retry
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | `string` (UUID) | Yes | AI query ID |

**Response `200 OK`:**

```json
{
  "data": {
    "id": "018d...",
    "status": "pending",
    "status_label": "Pending",
    "error_message": null,
    "updated_at": "2026-08-17T10:05:00+07:00"
  }
}
```

**Errors:**

| Code | Description |
|---|---|
| `401` | Unauthenticated |
| `404` | AI query not found or not in user's organization |
| `422` | Business rule violation (query is not in `failed` status) |

---

### 5. Cancel AI Query

```
POST /api/v1/ai-queries/{id}/cancel
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | `string` (UUID) | Yes | AI query ID |

**Response `200 OK`:**

```json
{
  "data": {
    "id": "018d...",
    "status": "failed",
    "status_label": "Failed",
    "error_message": "Cancelled by user",
    "updated_at": "2026-08-17T10:05:00+07:00"
  }
}
```

**Errors:**

| Code | Description |
|---|---|
| `401` | Unauthenticated |
| `404` | AI query not found or not in user's organization |
| `422` | Business rule violation (query is not in `pending` or `processing` status) |

---

## Status Lifecycle

| Status | Description |
|---|---|
| `pending` | Query created, awaiting processing |
| `processing` | Query picked up by AI processor |
| `completed` | AI inference succeeded, response available |
| `failed` | AI inference failed or query was cancelled |

## Common Response Envelope

All responses follow the standard API envelope:

```json
{
  "success": true,
  "message": "...",
  "data": { ... },
  "errors": [],
  "meta": { ... }
}
```