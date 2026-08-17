# Phase 27 — Integration Hub Flow

**Date:** 2026-08-17 | **Phase:** 27 — Integration Hub | **Status:** STEP_27_03_DRAFT

## Flows

### 1. Create Integration Config

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    participant DB
    A->>API: POST /api/v1/integration-configs
    API->>Controller: store(StoreIntegrationRequest)
    Controller->>Service: create(CreateIntegrationDTO)
    Service->>Service: Validate provider uniqueness per org
    Service->>Service: Set is_active = false
    Service->>Repository: create(data)
    Repository->>DB: INSERT INTO integration_configs (credentials encrypted)
    DB-->>Repository: IntegrationHub
    Repository-->>Service: IntegrationHub
    Service-->>Controller: IntegrationHub
    Controller-->>API: IntegrationHubResource (201) [credentials excluded]
    API-->>A: JSON response
```

### 2. List Integration Configs

```mermaid
sequenceDiagram
    actor U as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    U->>API: GET /api/v1/integration-configs?provider=&is_active=&search=&sort_by=
    API->>Controller: index()
    Controller->>Service: paginate(filters)
    Service->>Repository: paginate(filters)
    Repository->>Repository: Tenant-scoped query (organization_id)
    Repository->>Repository: Apply filters (provider, is_active)
    Repository->>Repository: Apply search (name ILIKE)
    Repository->>Repository: Whitelist sort (name, provider, created_at)
    Repository->>DB: SELECT with pagination (credentials excluded from select)
    DB-->>Repository: Paginated results
    Repository-->>Service: LengthAwarePaginator
    Service-->>Controller: LengthAwarePaginator
    Controller-->>API: IntegrationHubResource collection (200) [credentials excluded]
    API-->>U: JSON response
```

### 3. Update Integration Config

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: PUT /api/v1/integration-configs/{id}
    API->>Controller: update(id, UpdateIntegrationRequest)
    Controller->>Service: update(id, UpdateIntegrationDTO, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: IntegrationHub
    Service->>Service: Validate provider uniqueness (if changed)
    Service->>Repository: update(integration, data)
    Repository->>DB: UPDATE integration_configs (credentials encrypted if provided)
    DB-->>Repository: Updated IntegrationHub
    Repository-->>Service: IntegrationHub
    Service-->>Controller: IntegrationHub
    Controller-->>API: IntegrationHubResource (200) [credentials excluded]
    API-->>A: JSON response
```

### 4. Toggle Active

```mermaid
sequenceDiagram
    actor A as Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: POST /api/v1/integration-configs/{id}/toggle-active
    API->>Controller: toggleActive(id)
    Controller->>Service: toggleActive(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: IntegrationHub
    Service->>Service: Flip is_active boolean
    Service->>Repository: update(integration, {is_active: !current})
    Repository->>DB: UPDATE integration_configs SET is_active = ...
    DB-->>Repository: Updated IntegrationHub
    Repository-->>Service: IntegrationHub
    Service-->>Controller: IntegrationHub
    Controller-->>API: IntegrationHubResource (200) [credentials excluded]
    API-->>A: JSON response
```

### 5. Sync Trigger (updates last_sync_at)

```mermaid
sequenceDiagram
    actor A as System/Admin
    participant API
    participant Controller
    participant Service
    participant Repository
    A->>API: POST /api/v1/integration-configs/{id}/sync (conceptual)
    API->>Controller: syncTrigger(id)
    Controller->>Service: markSynced(id, orgId)
    Service->>Repository: findById(id, orgId)
    Repository-->>Service: IntegrationHub
    Service->>Repository: update(integration, {last_sync_at: now()})
    Repository->>DB: UPDATE integration_configs SET last_sync_at = now()
    DB-->>Repository: Updated IntegrationHub
    Repository-->>Service: IntegrationHub
    Service-->>Controller: IntegrationHub
    Controller-->>API: IntegrationHubResource (200) [credentials excluded]
    API-->>A: JSON response
```

### Traceability

| Flow | Requirement | Business Rule |
|---|---|---|
| Create | INT-REQ-001, INT-REQ-002, INT-REQ-003, INT-REQ-004, INT-REQ-005, INT-REQ-006, INT-REQ-007, INT-REQ-016 | BR-INT-001, BR-INT-002, BR-INT-003, BR-INT-004, BR-INT-005, BR-INT-007, BR-INT-016 |
| List | INT-REQ-012, INT-REQ-013, INT-REQ-014, INT-REQ-015 | BR-INT-009, BR-INT-010 |
| Update | INT-REQ-017 | BR-INT-004, BR-INT-017 |
| Toggle Active | INT-REQ-019 | BR-INT-008 |
| Sync Trigger | INT-REQ-025 | BR-INT-019 |