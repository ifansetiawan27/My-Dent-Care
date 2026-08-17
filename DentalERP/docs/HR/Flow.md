# Phase 23 — HR Flow

**Date:** 2026-08-17 | **Phase:** 23 — HR | **Status:** STEP_23_03_DRAFT

## Flows

### 1. Create HR Record

```mermaid
sequenceDiagram
    actor H as HR Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    H->>API: POST /api/v1/hr-records
    API->>Controller: store(StoreHRRequest)
    Controller->>Service: create(CreateHRDTO)
    Service->>Service: Set status = 'active'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO hr_records
    DB-->>Repository: HRRecord
    Repository-->>Service: HRRecord
    Service-->>Controller: HRRecord
    Controller-->>API: HRResource (201)
    API-->>H: JSON response
```

### 2. List HR Records

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/hr-records?record_type=&status=&employee_id=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (record_type, status, employee_id, search)
    Repository->>Repository: Whitelist sort (effective_date, created_at, status)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: HRResource collection (200)
    API-->>U: JSON response
```

### 3. Update HR Record

```mermaid
sequenceDiagram
    actor H as HR Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    H->>API: PUT /api/v1/hr-records/{id}
    API->>Controller: update(id, UpdateHRRequest)
    Controller->>Service: update(id, UpdateHRDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: HRRecord
    Service->>Service: Validate status transition (if changed)
    Service->>Repository: update(record, data)
    Repository->>DB: UPDATE hr_records
    DB-->>Repository: Updated HRRecord
    Repository-->>Service: HRRecord
    Service-->>Controller: HRRecord
    Controller-->>API: HRResource (200)
    API-->>H: JSON response
```

### 4. Delete HR Record

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/hr-records/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: HRRecord
    Service->>Repository: delete(record)
    Repository->>DB: UPDATE hr_records SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | HR-REQ-001, HR-REQ-002, HR-REQ-004, HR-REQ-006, HR-REQ-014 | BR-HR-001, BR-HR-003, BR-HR-004, BR-HR-005 |
| List | HR-REQ-010, HR-REQ-011, HR-REQ-012, HR-REQ-013 | BR-HR-007, BR-HR-008 |
| Update | HR-REQ-015 | BR-HR-014 |
| Delete | HR-REQ-016 | BR-HR-009 |