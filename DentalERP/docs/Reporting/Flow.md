# Phase 25 — Reporting Flow

**Date:** 2026-08-17 | **Phase:** 25 — Reporting | **Status:** STEP_25_03_DRAFT

## Flows

### 1. Create Report

```mermaid
sequenceDiagram
    actor R as Report Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    R->>API: POST /api/v1/reports
    API->>Controller: store(StoreReportingRequest)
    Controller->>Service: create(CreateReportingDTO)
    Service->>Service: Set status = 'generated'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO reports
    DB-->>Repository: Report
    Repository-->>Service: Report
    Service-->>Controller: Report
    Controller-->>API: ReportingResource (201)
    API-->>R: JSON response
```

### 2. List Reports

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/reports?report_type=&status=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (report_type, status, search)
    Repository->>Repository: Whitelist sort (report_date, created_at)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: ReportingResource collection (200)
    API-->>U: JSON response
```

### 3. Update Report

```mermaid
sequenceDiagram
    actor R as Report Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    R->>API: PUT /api/v1/reports/{id}
    API->>Controller: update(id, UpdateReportingRequest)
    Controller->>Service: update(id, UpdateReportingDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Report
    Service->>Repository: update(report, data)
    Repository->>DB: UPDATE reports
    DB-->>Repository: Updated Report
    Repository-->>Service: Report
    Service-->>Controller: Report
    Controller-->>API: ReportingResource (200)
    API-->>R: JSON response
```

### 4. Delete Report

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/reports/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Report
    Service->>Repository: delete(report)
    Repository->>DB: UPDATE reports SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | RPT-REQ-001, RPT-REQ-002, RPT-REQ-003, RPT-REQ-004, RPT-REQ-008, RPT-REQ-013 | BR-RPT-001, BR-RPT-002, BR-RPT-003, BR-RPT-004, BR-RPT-005 |
| List | RPT-REQ-009, RPT-REQ-010, RPT-REQ-011, RPT-REQ-012 | BR-RPT-006, BR-RPT-007 |
| Update | RPT-REQ-014 | BR-RPT-013 |
| Delete | RPT-REQ-015 | BR-RPT-008 |