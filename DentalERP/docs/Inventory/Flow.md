# Phase 18 — Inventory Flow

**Date:** 2026-08-17 | **Phase:** 18 — Inventory | **Status:** STEP_18_03_DRAFT

## Flows

### 1. Create Inventory Item

```mermaid
sequenceDiagram
    actor I as Inventory Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    I->>API: POST /api/v1/inventory-items
    API->>Controller: store(StoreInventoryRequest)
    Controller->>Service: create(CreateInventoryDTO)
    Service->>Service: Validate item_code uniqueness
    Service->>Service: Set is_active = true
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO inventory_items
    DB-->>Repository: InventoryItem
    Repository-->>Service: InventoryItem
    Service-->>Controller: InventoryItem
    Controller-->>API: InventoryResource (201)
    API-->>I: JSON response
```

### 2. List Inventory Items

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/inventory-items?branch_id=&category_id=&is_active=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (branch_id, category_id, is_active, search)
    Repository->>Repository: Whitelist sort (name, created_at, quantity)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: InventoryResource collection (200)
    API-->>U: JSON response
```

### 3. Update Inventory Item

```mermaid
sequenceDiagram
    actor I as Inventory Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    I->>API: PUT /api/v1/inventory-items/{id}
    API->>Controller: update(id, UpdateInventoryRequest)
    Controller->>Service: update(id, UpdateInventoryDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: InventoryItem
    Service->>Service: Validate item_code uniqueness (if changed)
    Service->>Service: Validate quantity & min_quantity >= 0
    Service->>Repository: update(item, data)
    Repository->>DB: UPDATE inventory_items
    DB-->>Repository: Updated InventoryItem
    Repository-->>Service: InventoryItem
    Service-->>Controller: InventoryItem
    Controller-->>API: InventoryResource (200)
    API-->>I: JSON response
```

### 4. Delete Inventory Item

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/inventory-items/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: InventoryItem
    Service->>Repository: delete(item)
    Repository->>DB: UPDATE inventory_items SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### 5. Toggle Active Status

```mermaid
sequenceDiagram
    actor I as Inventory Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    I->>API: PATCH /api/v1/inventory-items/{id}/toggle-active
    API->>Controller: toggleActive(id)
    Controller->>Service: toggleActive(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: InventoryItem
    Service->>Service: is_active = !is_active
    Service->>Repository: update(item, data)
    Repository->>DB: UPDATE inventory_items SET is_active = ...
    DB-->>Repository: Updated InventoryItem
    Repository-->>Service: InventoryItem
    Service-->>Controller: InventoryItem
    Controller-->>API: InventoryResource (200)
    API-->>I: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | INV-REQ-001, INV-REQ-002, INV-REQ-005, INV-REQ-006, INV-REQ-008, INV-REQ-017 | BR-INV-001, BR-INV-004, BR-INV-005, BR-INV-006, BR-INV-010, BR-INV-019 |
| List | INV-REQ-013, INV-REQ-014, INV-REQ-015, INV-REQ-016 | BR-INV-012, BR-INV-013 |
| Update | INV-REQ-018 | BR-INV-004, BR-INV-007, BR-INV-008, BR-INV-009, BR-INV-020 |
| Delete | INV-REQ-019 | BR-INV-014 |
| Toggle Active | INV-REQ-020 | BR-INV-011 |