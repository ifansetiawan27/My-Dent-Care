# Phase 17 — Billing Flow

**Date:** 2026-08-17 | **Phase:** 17 — Billing | **Status:** STEP_17_03_DRAFT

## Flows

### 1. Create Invoice

```mermaid
sequenceDiagram
    actor F as Finance
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    F->>API: POST /api/v1/invoices
    API->>Controller: store(StoreBillingRequest)
    Controller->>Service: create(CreateBillingDTO)
    Service->>Service: Generate invoice_number (INV-YYYYMMDD-XXXXX)
    Service->>Service: Validate paid_amount ≤ total_amount
    Service->>Service: Set status = 'draft'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO invoices
    DB-->>Repository: Invoice
    Repository-->>Service: Invoice
    Service-->>Controller: Invoice
    Controller-->>API: BillingResource (201)
    API-->>F: JSON response
```

### 2. List Invoices

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/invoices?status=&patient_id=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (status, patient_id, search)
    Repository->>Repository: Whitelist sort (created_at, due_date)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: BillingResource collection (200)
    API-->>U: JSON response
```

### 3. Update Invoice & Status Transition

```mermaid
sequenceDiagram
    actor F as Finance
    participant API
    participant Controller
    participant Service
    participant Repository
    F->>API: PUT /api/v1/invoices/{id}
    API->>Controller: update(id, UpdateBillingRequest)
    Controller->>Service: update(id, UpdateBillingDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Invoice
    Service->>Service: Validate status transition
    Service->>Service: Validate paid_amount ≤ total_amount
    Service->>Service: Auto-set status='paid' if paid_amount = total_amount
    Service->>Repository: update(invoice, data)
    Repository->>DB: UPDATE invoices
    DB-->>Repository: Updated Invoice
    Repository-->>Service: Invoice
    Service-->>Controller: Invoice
    Controller-->>API: BillingResource (200)
    API-->>F: JSON response
```

### 4. Delete Invoice

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/invoices/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Invoice
    Service->>Service: Validate not paid (paid invoices cannot be deleted)
    Service->>Repository: delete(invoice)
    Repository->>DB: UPDATE invoices SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### 5. Status Lifecycle Flow

```mermaid
stateDiagram-v2
    [*] --> draft: Create
    draft --> sent: Send invoice
    draft --> cancelled: Cancel
    sent --> paid: Payment received
    sent --> overdue: Past due date
    overdue --> paid: Payment received
    overdue --> cancelled: Cancel
    paid --> [*]
    cancelled --> [*]
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | BILL-REQ-001, BILL-REQ-002, BILL-REQ-004, BILL-REQ-005, BILL-REQ-015 | BR-BILL-001, BR-BILL-004, BR-BILL-005 |
| List | BILL-REQ-011, BILL-REQ-012, BILL-REQ-013, BILL-REQ-014 | BR-BILL-015, BR-BILL-016 |
| Update | BILL-REQ-016, BILL-REQ-018 | BR-BILL-008 through BR-BILL-013 |
| Delete | BILL-REQ-017, BILL-REQ-024 | BR-BILL-017 |
| Status Lifecycle | BILL-REQ-007, BILL-REQ-018 | BR-BILL-008 through BR-BILL-013 |