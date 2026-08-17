# Phase 21 — Procurement Flow

**Date:** 2026-08-17 | **Phase:** 21 — Procurement | **Status:** STEP_21_03_DRAFT

## Flows

### 1. Create Procurement Order

```mermaid
sequenceDiagram
    actor P as Procurement Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    P->>API: POST /api/v1/procurement-orders
    API->>Controller: store(StoreProcurementRequest)
    Controller->>Service: create(CreateProcurementDTO)
    Service->>Service: Validate order_number uniqueness
    Service->>Service: Set status = 'pending'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO procurement_orders
    DB-->>Repository: ProcurementOrder
    Repository-->>Service: ProcurementOrder
    Service-->>Controller: ProcurementOrder
    Controller-->>API: ProcurementResource (201)
    API-->>P: JSON response
```

### 2. List Procurement Orders

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/procurement-orders?status=&search=&sort_by=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (status, search)
    Repository->>Repository: Whitelist sort (order_date, created_at, status)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: ProcurementResource collection (200)
    API-->>U: JSON response
```

### 3. Update Procurement Order

```mermaid
sequenceDiagram
    actor P as Procurement Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    P->>API: PUT /api/v1/procurement-orders/{id}
    API->>Controller: update(id, UpdateProcurementRequest)
    Controller->>Service: update(id, UpdateProcurementDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: ProcurementOrder
    Service->>Service: Validate order_number uniqueness (if changed)
    Service->>Service: Validate status transition (if changed)
    Service->>Repository: update(order, data)
    Repository->>DB: UPDATE procurement_orders
    DB-->>Repository: Updated ProcurementOrder
    Repository-->>Service: ProcurementOrder
    Service-->>Controller: ProcurementOrder
    Controller-->>API: ProcurementResource (200)
    API-->>P: JSON response
```

### 4. Delete Procurement Order

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/procurement-orders/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: ProcurementOrder
    Service->>Repository: delete(order)
    Repository->>DB: UPDATE procurement_orders SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | PROC-REQ-001, PROC-REQ-002, PROC-REQ-005, PROC-REQ-007, PROC-REQ-016 | BR-PROC-001, BR-PROC-004, BR-PROC-005, BR-PROC-015 |
| List | PROC-REQ-012, PROC-REQ-013, PROC-REQ-014, PROC-REQ-015 | BR-PROC-008, BR-PROC-009 |
| Update | PROC-REQ-017 | BR-PROC-004, BR-PROC-016, BR-PROC-017 |
| Delete | PROC-REQ-018 | BR-PROC-010 |