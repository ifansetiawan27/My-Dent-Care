# Phase 24 — CRM Flow

**Date:** 2026-08-17 | **Phase:** 24 — CRM | **Status:** STEP_24_03_DRAFT

## Flows

### 1. Create CRM Contact

```mermaid
sequenceDiagram
    actor C as CRM Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    C->>API: POST /api/v1/crm-contacts
    API->>Controller: store(StoreCRMRequest)
    Controller->>Service: create(CreateCRMDTO)
    Service->>Service: Set status = 'new'
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO crm_contacts
    DB-->>Repository: CRMContact
    Repository-->>Service: CRMContact
    Service-->>Controller: CRMContact
    Controller-->>API: CRMResource (201)
    API-->>C: JSON response
```

### 2. List CRM Contacts

```mermaid
sequenceDiagram
    actor U as User
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/crm-contacts?status=&contact_type=&search=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query
    Repository->>Repository: Apply filters (status, contact_type, search)
    Repository->>Repository: Whitelist sort (follow_up_date, created_at, status)
    Repository->>DB: SELECT with pagination
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: CRMResource collection (200)
    API-->>U: JSON response
```

### 3. Update CRM Contact

```mermaid
sequenceDiagram
    actor C as CRM Staff
    participant API
    participant Controller
    participant Service
    participant Repository
    C->>API: PUT /api/v1/crm-contacts/{id}
    API->>Controller: update(id, UpdateCRMRequest)
    Controller->>Service: update(id, UpdateCRMDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: CRMContact
    Service->>Service: Validate status transition (if changed)
    Service->>Repository: update(contact, data)
    Repository->>DB: UPDATE crm_contacts
    DB-->>Repository: Updated CRMContact
    Repository-->>Service: CRMContact
    Service-->>Controller: CRMContact
    Controller-->>API: CRMResource (200)
    API-->>C: JSON response
```

### 4. Delete CRM Contact

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: DELETE /api/v1/crm-contacts/{id}
    API->>Controller: destroy(id)
    Controller->>Service: delete(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: CRMContact
    Service->>Repository: delete(contact)
    Repository->>DB: UPDATE crm_contacts SET deleted_at = now()
    DB-->>Repository: true
    Repository-->>Service: true
    Service-->>Controller: true
    Controller-->>API: {"success": true} (200)
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | CRM-REQ-001, CRM-REQ-002, CRM-REQ-004, CRM-REQ-015 | BR-CRM-001, BR-CRM-003, BR-CRM-004 |
| List | CRM-REQ-011, CRM-REQ-012, CRM-REQ-013, CRM-REQ-014 | BR-CRM-005, BR-CRM-006 |
| Update | CRM-REQ-016 | BR-CRM-012 |
| Delete | CRM-REQ-017 | BR-CRM-007 |