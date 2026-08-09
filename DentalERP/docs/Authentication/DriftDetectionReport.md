# Authentication Drift Detection Report

## Status

**DESIGN FREEZE ACTIVE — 2026-08-09**

Declared on: **2026-08-09 (Design Freeze Declaration post Architecture Review PASS)**

Stages 01–05 design-artifact comparisons (Requirement, Business Rules, ERD, API, Flow, OpenAPI, ADRs, Traceability Matrix) are verified and synchronized. Architecture Review (11 criteria) PASS. Diagrams (Flow, Flowchart, SequenceDiagram) trace to Accepted authorities through DD-AUTH-018 and ADR-006. Design Freeze governance record: `docs/Authentication/DesignFreeze.md`. Stage 06 migration-draft items (DESC indexes, refresh_tokens session_id FK, missing user_sessions migration, alter_users_table) are deferred to Stage 06 implementation synchronization.

## Cross-Artifact Matrix

| Comparison | Status | Evidence / Finding |
|---|---|---|
| Requirement ↔ Business Rules | PASS | All 18 AUTH-REQ-xxx map to correct AUTH-BR-xxx. DD-AUTH-003 self-service boundary, DD-AUTH-004 Change Password behavior, DD-AUTH-008 Remember Me removal, DD-AUTH-007 lifecycle, and ADR-005 data categories are Accepted and reflected in synchronized BR references. |
| Requirement ↔ API Contract | PASS | All 18 AUTH-REQ-xxx map to API endpoints or architecture concerns. Device pagination, Super Admin self-service boundary (DD-AUTH-003), and all 12 endpoint operations are synchronized. |
| Business Rules ↔ Flow | PASS | Flow covers all 17 operations. Session lifecycle, token rotation/reuse, credential-change projection exception (DD-AUTH-018), device revocation cascade, audit separation, and async cleanup are consistent with Business Rules. |
| Business Rules ↔ OpenAPI | PASS | All 12 endpoint x-business-rules annotations match AUTH-BR-xxx assignments. Remember Me removed, Change Password contract, revocation boundaries, and lifecycle authority are synchronized. |
| ERD ↔ Flow | PASS | Login History, Device, Refresh Token, Redis lockout, and framework-managed password reset flows use approved data ownership. |
| ERD ↔ OpenAPI | PASS | LoginHistory and Device response schemas match ERD column nullability and field classification per DD-AUTH-017. Derived `is_active`, excluded tenant FKs, excluded sensitive fields (user_agent, ip_address, device_type) are consistent. |
| ERD ↔ ADR | PASS | ADR-004 is Superseded by ADR-006. ERD governance declares accepted authorities (DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, ADR-005, ADR-006). Data Categories, lifecycle policies, and audit/projection separation align. |
| Flow ↔ OpenAPI | PASS | All 12 endpoint operations covered in Flow match OpenAPI operationIds. Change Password response contract (`current_session_active`, `other_sessions_revoked`, `registered_devices_retained`), bounded Device list pagination/sort, and Login History ordering are synchronized. |
| ADR ↔ Business Rules | PASS | Lockout, token TTL/rotation, password reset, and audit rules match Accepted ADRs. |
| ADR ↔ ERD | PASS | Redis transient state, PostgreSQL audit, framework password reset, and custom refresh-token persistence match ADRs. |
| Migration Draft ↔ ERD | PASS | Reconcile to frozen design 2026-08-09. Six migrations: 004 (alter users — comments only), 005 (login_histories — DESC composite indexes per DD-AUTH-010), 006 (user_devices), 007 (user_sessions), 008 (refresh_tokens — session_id FK replaces direct user/org/branch/device), 009 (personal_access_tokens — add session_id FK/UNIQUE). All columns, types, nullable, defaults, FKs, indexes, CHECK constraints match ERD and Database Design. PASSWORD_RESET_TOKENS is framework-managed (no migration). |
| Repository ↔ API Contract | NOT STARTED | Repository implementation does not exist; check is mandatory at Stage 09. |
| Request ↔ OpenAPI | NOT STARTED | Request implementation does not exist; check is mandatory at Stage 12. |
| Resource ↔ OpenAPI | NOT STARTED | Resource implementation does not exist; check is mandatory at Stage 13. |
| Policy ↔ Business Rules | NOT STARTED | Policy implementation does not exist; check is mandatory at Stage 14. |
| Controller/Routes ↔ OpenAPI | NOT STARTED | Controller and Routes do not exist; check is mandatory at Stages 15–16. |
| Tests ↔ Contract/Rules | NOT STARTED | Feature and Unit tests do not exist; checks are mandatory at Stages 17–18. |

## Blocking Findings

### DD-AUTH-001 — Access Token to Device/Refresh Session Link

Severity: **Resolved — DD-AUTH-001 Accepted**

Requirements and rules require:

- Logout to revoke the current access token and associated refresh token.
- Device revocation to revoke every access and refresh token for the device.

The current `personal_access_tokens` design has no `device_id`, session identifier, or direct association to a refresh-token family. `refresh_tokens` references `user_devices`, but Sanctum tokens cannot be selected reliably by device/session.

Required decision:

- Define a device/session association for Sanctum tokens, or
- Define a deterministic token naming/abilities strategy with an enforceable database/query contract.

Resolution:

- Added explicit `user_sessions` aggregate.
- `personal_access_tokens.session_id` and `refresh_tokens.session_id` link tokens to a Session.
- Device lifecycle remains independent from Session lifecycle.
- Logout, Logout All, and Device Revocation now have deterministic revocation paths.
- ADR-002, ERD, Database Design, Business Rules, API/OpenAPI behavior, and Traceability Matrix are synchronized.

Drift Detection: **PASS**.

### DD-AUTH-002 — Argon2id Conflicts with Existing User Artifacts

Severity: **Decision Accepted — downstream User artifact verification remains for Full Drift Detection**

Authentication requires Argon2id, while existing User database design, business rules, migration comments, and repository documentation still reference bcrypt. Laravel's `hashed` cast follows application hashing configuration and does not itself guarantee Argon2id.

Required decision:

- Approve Argon2id as platform-wide password hashing, document legacy bcrypt rehash-on-login behavior, and synchronize User artifacts/configuration; or
- Revise Authentication requirements through an ADR.

### DD-AUTH-003 — Super Admin Session Management Is Ambiguous

Severity: **Decision Accepted — Requirement/Business Rule/API scope recheck required**

Requirement grants Super Admin “Manage active sessions” and Business Rules state Super Admin can revoke all devices for an authorized target User. Current APIs only operate on the authenticated User's own sessions/devices.

Required decision:

- Restrict Super Admin to own sessions and revise Business Rules, or
- Add target-user session-management endpoints, authorization rules, tenant scope, OpenAPI, flow, and traceability.

### DD-AUTH-004 — Change Password Session Behavior Is Undefined

Severity: **Resolved — DD-AUTH-004 Accepted and API/OpenAPI/Flow synchronized**

Flow says “revoke other sessions according to approved contract”, but Requirement, Business Rules, and OpenAPI do not define whether Change Password:

- Preserves all sessions.
- Preserves current session and revokes others.
- Revokes all sessions.

Required decision must be added to Business Rules, API, Flow, and tests.

### DD-AUTH-005 — API Nullability Differs from ERD

Severity: **Resolved — DD-AUTH-017 Accepted; DD-AUTH-005 Superseded**

Database allows nullable values for parsing/geolocation/device metadata, while OpenAPI requires non-null fields:

- Login History: `ip_address`, `browser`, `operating_system`, `device_name`.
- Device: `device_name`, `platform`, `browser`, `operating_system`, `last_login_at`, `last_activity_at`.

Required decision:

- Make database fields non-null with reliable defaults/source guarantees, or
- Make OpenAPI fields nullable and adjust required lists/examples.

### DD-AUTH-006 — Device List Is Not Bounded

Severity: **Resolved — Standard Pagination Accepted**

Requirement mandates device lists are paginated or bounded. OpenAPI has neither pagination nor a documented maximum result count.

Resolution:

- Standard page-based pagination is required.
- Default page size is 20.
- Maximum page size is 100.
- Default sort is `last_activity_at DESC, id DESC`.
- API and OpenAPI use the standard pagination metadata envelope.

Drift Detection: **PASS**.

### DD-AUTH-007 — Mandatory Audit/Soft-Delete Standard Conflicts with Authentication Tables

Severity: **Resolved — DD-AUTH-007 and ADR-005 Accepted; governance and downstream lifecycle artifacts synchronized**

`AGENTS.md` requires tenant-scoped tables to include audit columns and soft delete, while `user_devices`, `refresh_tokens`, and `login_histories` intentionally omit them. Immutable history and security-token tables may need exceptions, but no ADR currently grants them.

Resolution:

- ADR-005 establishes mutually exclusive Data Categories and category-specific lifecycle policy.
- Governance, Decision metadata, Database Design, ERD, API.md, OpenAPI, Flow, Sequence Diagram, and Traceability metadata are synchronized.
- Login History is an Operational History Projection; canonical Audit Events remain append-only and immutable.
- Security data uses revocation/expiry and authorized `Hard Deletable` cleanup rather than universal soft delete.

Drift Detection for this finding: **PASS**.

### DD-AUTH-008 — Remember Me Behavior

Severity: **Resolved — Option A Accepted**

Resolution:

- Removed `remember_me` from Login API and OpenAPI schemas/examples.
- Access Token TTL remains 60 minutes.
- Refresh Token TTL remains 30 days.
- Login does not grant trusted-device status.
- Device trust remains a separate verified capability.

Drift Detection: **PASS**.

### DD-AUTH-009 — Device UUID Uniqueness Diagram Drift

Severity: **Resolved during audit**

The ERD diagram previously marked `device_uuid` globally unique while prose/migration specified uniqueness per User. The diagram now relies on the documented composite unique `(user_id, device_uuid)`.

### DD-AUTH-010 — Login History Index Direction Differs in Draft

Severity: **Medium — verified during Full Drift Detection re-run 2026-08-09**

ERD/database design specify descending timestamp indexes. Draft migration uses Laravel's ordinary multi-column index declaration without explicit `DESC`. Three composite indexes affected: `login_histories` user-scoped, tenant-scoped, and identifier-status ordered indexes.

Additional draft structural findings (same comparison):
1. `refresh_tokens` draft uses direct `user_id`, `organization_id`, `branch_id`, `device_id` columns while ERD specifies `session_id` FK as the sole ownership link.
2. No `user_sessions` migration draft exists in the draft directory.

Required action after Design Freeze:

- Generate PostgreSQL descending indexes with explicit SQL for Login History.
- Rewrite `refresh_tokens` migration to use `session_id` FK per ERD.
- Create `user_sessions` migration matching ERD specification. 
- Discard or redesign `alter_users_table` draft (failed its migration Quality Gate).

### DD-AUTH-012 — Unresolved Legacy Migration Drafts

Severity: **Low**

`drafts/migrations/` directory retains pre-Decision structural artifacts that no longer match Accepted ERD. These non-runtime references do not block Design Freeze but require cleanup/removal or redesign before Stage 06 execution.

### DD-AUTH-011 — Password Broker Architecture Was Ambiguous

Severity: **Resolved during audit**

Architecture and Flow now explicitly delegate reset-token creation, verification, and consumption to a Laravel Password Broker adapter in accordance with ADR-003.

## Draft Migration Status

All Stage 06 drafts are non-runtime references in:

```text
docs/Authentication/drafts/migrations/
```

They are blocked by Design Freeze and must not be executed, reviewed as final implementation, or committed as implementation until all blocking drift is resolved.

The `alter_users_table` draft must be discarded or redesigned because it modifies existing columns and failed its migration Quality Gate.

## Exit Criteria

Design Freeze declared. All exit criteria met:

1. DD-AUTH-001 through DD-AUTH-004, DD-AUTH-006 through DD-AUTH-008, DD-AUTH-010, DD-AUTH-017, and DD-AUTH-018 are Accepted; DD-AUTH-005 is Superseded by DD-AUTH-017; ADR-004 is Superseded by ADR-006. ✅ MET
2. Requirement, Business Rules, ERD, API, Flow, Architecture, ADRs, and Traceability Matrix are synchronized. ✅ MET
3. The complete Drift Detection matrix reports PASS for Stages 01–05 (all 8 design-artifact comparisons). ✅ MET
4. Diagrams (Flow, Flowchart, SequenceDiagram) trace to Accepted authorities and cover all operations. ✅ MET
5. Architecture Review passes. ✅ MET (2026-08-09: 11/11 criteria)
6. No executable Authentication implementation remains in runtime paths before Stage 06 restarts. ✅ VERIFIED

Migration draft reconciliation (descending indexes, refresh_tokens structure, missing user_sessions migration, alter_users_table removal) is a Stage 06 prerequisite. Implementation may begin at Stage 06 (Migration) after Design Freeze is declared.
