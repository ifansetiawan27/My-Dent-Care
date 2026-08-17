# Phase 20 — Laboratory Flow

**Date:** 2026-08-17 | **Phase:** 20 — Laboratory | **Status:** STEP_20_03_DRAFT

## Flows

### 1. Create Lab Order

```mermaid
sequenceDiagram
    actor D as Doctor
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    D->>API: POST /api/v1/lab-orders
    API->>Controller: store(StoreLaboratoryRequest)
    Controller->>Service: create(CreateLaboratoryDTO)
    Service->>Service: Set status = 'pending'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO lab_orders
    DB-->>Repository: Laboratory
    Repository-->>Service: Laboratory
    Service-->>Controller: Laboratory
    Controller-->>API: LaboratoryResource (201)
    API-->>D: JSON response
```

### 2. List Lab Orders

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/lab-orders?patient_id=&status=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (patient, doctor, category, status, search)
    Repository->>Repository: Whitelist sort
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: LaboratoryResource collection (200)
    API-->>U: JSON response
```

### 3. Update Lab Order

```mermaid
sequenceDiagram
    actor D as Doctor
    participant API
    participant Controller
    participant Service
    participant Repository
    D->>API: PUT /api/v1/lab-orders/{id}
    API->>Controller: update(id, UpdateLaboratoryRequest)
    Controller->>Service: update(id, UpdateLaboratoryDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Laboratory
    Service->>Service: Validate status transition
    Service->>Repository: update(laboratory, data)
    Repository->>DB: UPDATE lab_orders
    DB-->>Repository: Updated Laboratory
    Repository-->>Service: Laboratory
    Service-->>Controller: Laboratory
    Controller-->>API: LaboratoryResource (200)
    API-->>D: JSON response
```

### 4. Delete Lab Order

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/lab-orders/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Laboratory
    Service->>Repository: delete(laboratory)
    Repository->>DB: UPDATE lab_orders SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### 5. Status Lifecycle Flow

```mermaid
stateDiagram-v2
    [*] --> pending: Create
    pending --> in_progress: Start lab work
    pending --> cancelled: Cancel
    in_progress --> completed: Complete lab work
    in_progress --> cancelled: Cancel (OPEN)
    completed --> [*]
    cancelled --> [*]
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | LAB-REQ-001, LAB-REQ-002, LAB-REQ-003, LAB-REQ-005, LAB-REQ-017 | BR-LAB-001, BR-LAB-002, BR-LAB-005 |
| List | LAB-REQ-013, LAB-REQ-014, LAB-REQ-015, LAB-REQ-016 | BR-LAB-015, BR-LAB-016 |
| Update | LAB-REQ-018, LAB-REQ-020 | BR-LAB-008, BR-LAB-009, BR-LAB-010, BR-LAB-011 |
| Delete | LAB-REQ-019 | BR-LAB-017 |
| Status Lifecycle | LAB-REQ-007, LAB-REQ-020 | BR-LAB-007, BR-LAB-008, BR-LAB-009, BR-LAB-010, BR-LAB-011 |