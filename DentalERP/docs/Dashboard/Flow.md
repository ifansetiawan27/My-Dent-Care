# Phase 26 — Dashboard Flow

**Date:** 2026-08-17 | **Phase:** 26 — Dashboard | **Status:** STEP_26_03_DRAFT

## Flows

### 1. Create Dashboard

```mermaid
sequenceDiagram
    actor D as Dashboard Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    D->>API: POST /api/v1/dashboards
    API->>Controller: store(StoreDashboardRequest)
    Controller->>Service: create(CreateDashboardDTO)
    Service->>Service: Set is_default = false
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO dashboards
    DB-->>Repository: Dashboard
    Repository-->>Service: Dashboard
    Service-->>Controller: Dashboard
    Controller-->>API: DashboardResource (201)
    API-->>D: JSON response
```

### 2. List Dashboards

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/dashboards?user_id=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (user_id, search)
    Repository->>Repository: Whitelist sort (name, created_at)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: DashboardResource collection (200)
    API-->>U: JSON response
```

### 3. Update Dashboard

```mermaid
sequenceDiagram
    actor D as Dashboard Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    D->>API: PUT /api/v1/dashboards/{id}
    API->>Controller: update(id, UpdateDashboardRequest)
    Controller->>Service: update(id, UpdateDashboardDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Dashboard
    Service->>Repository: update(dashboard, data)
    Repository->>DB: UPDATE dashboards
    DB-->>Repository: Updated Dashboard
    Repository-->>Service: Dashboard
    Service-->>Controller: Dashboard
    Controller-->>API: DashboardResource (200)
    API-->>D: JSON response
```

### 4. Delete Dashboard

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/dashboards/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Dashboard
    Service->>Repository: delete(dashboard)
    Repository->>DB: UPDATE dashboards SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | DASH-REQ-001, DASH-REQ-002, DASH-REQ-004, DASH-REQ-012 | BR-DASH-001, BR-DASH-003, BR-DASH-004 |
| List | DASH-REQ-008, DASH-REQ-009, DASH-REQ-010, DASH-REQ-011 | BR-DASH-005, BR-DASH-006 |
| Update | DASH-REQ-013 | — |
| Delete | DASH-REQ-014 | BR-DASH-007 |