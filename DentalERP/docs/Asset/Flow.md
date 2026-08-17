# Phase 22 — Asset Flow

**Date:** 2026-08-17 | **Phase:** 22 — Asset | **Status:** STEP_22_03_DRAFT

## Flows

### 1. Create Asset

```mermaid
sequenceDiagram
    actor A as Asset Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    A->>API: POST /api/v1/assets
    API->>Controller: store(StoreAssetRequest)
    Controller->>Service: create(CreateAssetDTO)
    Service->>Service: Validate asset_code uniqueness
    Service->>Service: Set status = 'active'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO assets
    DB-->>Repository: Asset
    Repository-->>Service: Asset
    Service-->>Controller: Asset
    Controller-->>API: AssetResource (201)
    API-->>A: JSON response
```

### 2. List Assets

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/assets?status=&search=&sort_by=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (status, category_id, search)
    Repository->>Repository: Whitelist sort (name, created_at, purchase_date)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: AssetResource collection (200)
    API-->>U: JSON response
```

### 3. Update Asset

```mermaid
sequenceDiagram
    actor A as Asset Manager
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: PUT /api/v1/assets/{id}
    API->>Controller: update(id, UpdateAssetRequest)
    Controller->>Service: update(id, UpdateAssetDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Asset
    Service->>Service: Validate asset_code uniqueness (if changed)
    Service->>Service: Validate status transition (if changed)
    Service->>Repository: update(asset, data)
    Repository->>DB: UPDATE assets
    DB-->>Repository: Updated Asset
    Repository-->>Service: Asset
    Service-->>Controller: Asset
    Controller-->>API: AssetResource (200)
    API-->>A: JSON response
```

### 4. Delete Asset

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/assets/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: Asset
    Service->>Repository: delete(asset)
    Repository->>DB: UPDATE assets SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | ASST-REQ-001, ASST-REQ-002, ASST-REQ-005, ASST-REQ-006, ASST-REQ-017 | BR-ASST-001, BR-ASST-004, BR-ASST-005, BR-ASST-006, BR-ASST-015 |
| List | ASST-REQ-013, ASST-REQ-014, ASST-REQ-015, ASST-REQ-016 | BR-ASST-008, BR-ASST-009 |
| Update | ASST-REQ-018 | BR-ASST-004, BR-ASST-016, BR-ASST-017 |
| Delete | ASST-REQ-019 | BR-ASST-010 |