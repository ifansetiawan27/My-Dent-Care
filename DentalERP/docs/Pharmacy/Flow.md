# Phase 19 — Pharmacy Flow

**Date:** 2026-08-17 | **Phase:** 19 — Pharmacy | **Status:** STEP_19_03_DRAFT

## Flows

### 1. Create Pharmacy Item

```mermaid
sequenceDiagram
    actor P as Pharmacy Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    P->>API: POST /api/v1/pharmacy-items
    API->>Controller: store(StorePharmacyRequest)
    Controller->>Service: create(CreatePharmacyDTO)
    Service->>Service: Validate drug_code uniqueness
    Service->>Service: Set is_active = true
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO pharmacy_items
    DB-->>Repository: PharmacyItem
    Repository-->>Service: PharmacyItem
    Service-->>Controller: PharmacyItem
    Controller-->>API: PharmacyResource (201)
    API-->>P: JSON response
```

### 2. List Pharmacy Items

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/pharmacy-items?branch_id=&category=&is_active=&search=&expiry_date=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (branch_id, category, is_active, expiry_date, search)
    Repository->>Repository: Whitelist sort (name, drug_code, expiry_date)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: PharmacyResource collection (200)
    API-->>U: JSON response
```

### 3. Update Pharmacy Item

```mermaid
sequenceDiagram
    actor P as Pharmacy Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    P->>API: PUT /api/v1/pharmacy-items/{id}
    API->>Controller: update(id, UpdatePharmacyRequest)
    Controller->>Service: update(id, UpdatePharmacyDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: PharmacyItem
    Service->>Service: Validate drug_code uniqueness (if changed)
    Service->>Repository: update(item, data)
    Repository->>DB: UPDATE pharmacy_items
    DB-->>Repository: Updated PharmacyItem
    Repository-->>Service: PharmacyItem
    Service-->>Controller: PharmacyItem
    Controller-->>API: PharmacyResource (200)
    API-->>P: JSON response
```

### 4. Delete Pharmacy Item

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/pharmacy-items/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: PharmacyItem
    Service->>Repository: delete(item)
    Repository->>DB: UPDATE pharmacy_items SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### 5. Toggle Active Status

```mermaid
sequenceDiagram
    actor P as Pharmacy Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    P->>API: PATCH /api/v1/pharmacy-items/{id}/toggle-active
    API->>Controller: toggleActive(id)
    Controller->>Service: toggleActive(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: PharmacyItem
    Service->>Service: is_active = !is_active
    Service->>Repository: update(item, data)
    Repository->>DB: UPDATE pharmacy_items SET is_active = ...
    DB-->>Repository: Updated PharmacyItem
    Repository-->>Service: PharmacyItem
    Service-->>Controller: PharmacyItem
    Controller-->>API: PharmacyResource (200)
    API-->>P: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | PHARM-REQ-001, PHARM-REQ-002, PHARM-REQ-004, PHARM-REQ-005, PHARM-REQ-017 | BR-PHARM-001, BR-PHARM-003, BR-PHARM-004, BR-PHARM-010, BR-PHARM-019 |
| List | PHARM-REQ-013, PHARM-REQ-014, PHARM-REQ-015, PHARM-REQ-016 | BR-PHARM-012, BR-PHARM-013 |
| Update | PHARM-REQ-018 | BR-PHARM-003, BR-PHARM-006, BR-PHARM-008, BR-PHARM-009, BR-PHARM-020 |
| Delete | PHARM-REQ-019 | BR-PHARM-014 |
| Toggle Active | PHARM-REQ-020 | BR-PHARM-011 |