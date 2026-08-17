# Phase 16 — Treatment Flow

**Date:** 2026-08-17 | **Phase:** 16 — Treatment | **Status:** STEP_16_03_DRAFT

## Flows

### 1. Create Treatment

```mermaid
sequenceDiagram
    actor D as Doctor
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    D->>API: POST /api/v1/treatments
    API->>Controller: store(StoreTreatmentRequest)
    Controller->>Service: create(CreateTreatmentDTO)
    Service->>Service: Validate patient exists
    Service->>Service: Validate treatment type
    Service->>Service: Set status = 'planned'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO treatments
    DB-->>Repository: Treatment
    Repository-->>Service: Treatment
    Service-->>Controller: Treatment
    Controller-->>API: TreatmentResource (201)
    API-->>D: JSON response
```

### 2. List Treatments

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/treatments?patient_id=&status=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (patient, status, type, search)
    Repository->>Repository: Whitelist sort
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: TreatmentResource collection (200)
    API-->>U: JSON response
```

### 3. Update Treatment

```mermaid
sequenceDiagram
    actor D as Doctor
    participant API
    participant Controller
    participant Service
    participant Repository
    D->>API: PUT /api/v1/treatments/{id}
    API->>Controller: update(id, UpdateTreatmentRequest)
    Controller->>Service: update(id, UpdateTreatmentDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Treatment
    Service->>Service: Validate status transition
    Service->>Service: Validate cost >= 0
    Service->>Repository: update(treatment, data)
    Repository->>DB: UPDATE treatments
    DB-->>Repository: Updated Treatment
    Repository-->>Service: Treatment
    Service-->>Controller: Treatment
    Controller-->>API: TreatmentResource (200)
    API-->>D: JSON response
```

### 4. Delete Treatment

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/treatments/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Treatment
    Service->>Repository: delete(treatment)
    Repository->>DB: UPDATE treatments SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### 5. Status Lifecycle Flow

```mermaid
stateDiagram-v2
    [*] --> planned: Create
    planned --> in_progress: Start treatment
    planned --> cancelled: Cancel
    in_progress --> completed: Complete treatment
    in_progress --> cancelled: Cancel (OPEN)
    completed --> [*]
    cancelled --> [*]
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | TREAT-REQ-001, TREAT-REQ-002, TREAT-REQ-003, TREAT-REQ-006, TREAT-REQ-015 | BR-TREAT-001, BR-TREAT-002, BR-TREAT-006 |
| List | TREAT-REQ-011, TREAT-REQ-012, TREAT-REQ-013, TREAT-REQ-014 | BR-TREAT-014, BR-TREAT-015 |
| Update | TREAT-REQ-016, TREAT-REQ-018 | BR-TREAT-008, BR-TREAT-009, BR-TREAT-010, BR-TREAT-011, BR-TREAT-012 |
| Delete | TREAT-REQ-017, TREAT-REQ-024 | BR-TREAT-016 |
| Status Lifecycle | TREAT-REQ-007, TREAT-REQ-018 | BR-TREAT-007, BR-TREAT-008, BR-TREAT-009, BR-TREAT-010, BR-TREAT-011 |