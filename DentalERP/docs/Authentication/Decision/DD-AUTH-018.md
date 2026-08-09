# DD-AUTH-018

## Title

Credential-Change Revocation and Login History Projection Policy

## Status

Accepted — Architecture, Security, Data, API Contract, and Audit/Compliance Reviews PASS; Final Quality Gate and Governance Acceptance PASS

## Problem

Accepted Authentication Decisions conflict on one projection behavior: when Password Change revokes every other authenticated Session, must `login_histories.logout_at` for those revoked Sessions remain unchanged or receive its approved `Controlled One-Time Mutation`?

This Decision resolves that precedence conflict only. It does not redesign Authentication, Session revocation, token lifecycle, Device behavior, schema, or API shape.

## Current State

### Accepted DD-AUTH-004

- Every other active Session owned by the User is revoked after Password Change.
- Login History records are not modified.
- Login History remains unchanged; immutable evidence belongs to Audit Platform records.

### Accepted DD-AUTH-007

- Credential Change is an approved Session-revocation trigger.
- Session revocation is governed by the Authentication lifecycle authority.
- Canonical Audit Events remain separate from the Login History Operational History Projection.

### Accepted DD-AUTH-017

- Login History is an Operational History Projection.
- `logout_at` is nullable and uses `Controlled One-Time Mutation`.
- The documented generic trigger is explicit Logout or approved Session revocation.

### Current Downstream Interpretation

- `BusinessRule.md`, `API.md`, and OpenAPI follow DD-AUTH-004 and state that Password Change does not modify Login History.
- DD-AUTH-018 resolved the authority conflict; Batch `STEP_05_19_5_BATCH_8B_API_OPENAPI_ALIGNMENT` subsequently synchronized the accepted precedence rule.

## Options

### Option A — Credential Change Is an Explicit Projection Exception

Effects within this Decision's narrow scope:

- Other Sessions are revoked.
- Descendant Access Tokens are revoked.
- Descendant Refresh Token families are revoked.
- `login_histories.logout_at` remains unchanged.
- Login History Operational History Projections are not mutated by Password Change.
- The `PASSWORD_CHANGED` Audit Event provides immutable evidence.
- The applicable `SESSION_REVOKED` Audit Events provide immutable evidence.

Advantages:

- Preserves the specific semantics of DD-AUTH-004.
- Keeps Password Change behavior deterministic.
- Avoids expanding `logout_at` semantics to every Session-termination reason.
- Preserves the current API contract and Business Rule interpretation.
- Requires no schema or Database Design change.

Disadvantages:

- Introduces one explicit exception to the generic approved Session-revocation trigger for `logout_at`.

### Option B — Credential Change Uses the Generic Projection Trigger

Effects within this Decision's narrow scope:

- Other Sessions are revoked.
- Linked `login_histories.logout_at` values are updated exactly once where still `NULL`.
- Canonical Audit Events remain separate from the projection.

Advantages:

- Applies one uniform projection rule to approved Session revocations.

Disadvantages:

- Changes the current DD-AUTH-004 downstream interpretation.
- Changes current Business Rule and API expectations.
- Alters Login History projection behavior.
- Requires downstream contract and diagram synchronization.

## Decision

Select Option A — Credential Change Is an Explicit Projection Exception.

Rationale for the Accepted Decision:

- A specific Password Change Decision should take precedence over a generic Session lifecycle trigger within its narrow scope.
- DD-AUTH-004 is the Accepted Password Change authority.
- DD-AUTH-007 owns generic Authentication lifecycle behavior.
- DD-AUTH-017 owns generic field classification, exposure, nullability, and field governance.
- Option A resolves precedence without modifying the intent of any Accepted Decision, schema, or API contract.

Normative statement:

> Credential-change revocation is an explicit exception to the generic approved Session-revocation trigger for `login_histories.logout_at`.

During Password Change:

- Current Session remains active.
- Current Access Token remains active.
- Current Refresh Token family remains active.
- Every other Session is revoked.
- Descendant Access Tokens are revoked.
- Descendant Refresh Token families are revoked.
- Login History Operational History Projections SHALL NOT be mutated.
- `login_histories.logout_at` SHALL remain unchanged.
- The `PASSWORD_CHANGED` Audit Event SHALL provide immutable evidence.
- The applicable `SESSION_REVOKED` Audit Events SHALL provide immutable evidence.

The quoted statement is binding within the narrow credential-change projection scope of this Accepted Decision.

## Field Classification

Not Applicable — this Decision does not add, remove, or reclassify fields. Accepted DD-AUTH-017 remains the field-policy authority.

## Exposure Classification

Not Applicable — this Decision does not change field exposure or API response shape. Accepted DD-AUTH-017 and the approved API contract remain authoritative.

## Lifecycle Semantics

This Decision introduces no new lifecycle semantic or transition.

- Session revocation remains `Revocable` under Accepted DD-AUTH-007.
- `login_histories.logout_at` remains a `Controlled One-Time Mutation` under Accepted DD-AUTH-017 and the lifecycle authority of DD-AUTH-007.
- Option A defines only a narrow trigger-precedence exception: credential-change revocation does not invoke that projection mutation.
- Canonical Audit Events remain `Append Only` and `Immutable` under ADR-006, DD-AUTH-007, and ADR-005. ADR-004 remains superseded historical evidence.

## Ownership Exceptions

Not Applicable — this Decision does not change User, Organization, Branch, Device, Session, or Login History ownership resolution.

## Consequences

### Accepted Option A

- Existing Password Change Business Rules and API contract remain unchanged.
- Login History projection behavior remains unchanged for Password Change.
- Canonical `PASSWORD_CHANGED` and `SESSION_REVOKED` Audit Events remain the evidence of credential-change revocation.
- Database Design and ERD remain unchanged.
- Decision index, dependency graph, traceability, Business Rules, API/OpenAPI authority references, Flow, Flowchart, and Sequence Diagram require post-acceptance synchronization only.

### If Option B Is Accepted

- Business Rules, API/OpenAPI descriptions, Flow, Flowchart, Sequence Diagram, and traceability require synchronization.
- Database schema remains unchanged, but projection behavior changes.

### Unchanged Accepted Behavior

The following behaviors are dependencies, not subjects of redesign:

- Current Session remains active.
- Current Access Token remains active.
- Current Refresh Token family remains active.
- Every other Session is revoked.
- Descendant Access Tokens are revoked.
- Descendant Refresh Token families are revoked.
- Devices remain registered.
- Canonical Audit Events remain `Append Only` and `Immutable`.
- Distributed Lockout, Refresh Rotation, token lifecycle, and Login History schema remain unchanged.

## Affected Documents

Post-acceptance synchronization only:

- `docs/Authentication/Decision/index.md`
- `docs/Authentication/Decision/DependencyGraph.md`
- `docs/Authentication/TraceabilityMatrix.md`
- `docs/Authentication/BusinessRule.md`
- `docs/Authentication/API.md`
- `docs/api/openapi.yaml`
- `docs/Authentication/Flow.md`
- `docs/Authentication/Flowchart.md`
- `docs/Authentication/SequenceDiagram.md`

Database Design and ERD remain unchanged if Option A is Accepted.

## Review Status

Architecture Review: PASS (`STEP_DD_AUTH_018_ARCHITECTURE_REVIEW_PASS`).

Security Review: PASS (`STEP_DD_AUTH_018_SECURITY_REVIEW_PASS`).

Data Review: PASS (`STEP_DD_AUTH_018_DATA_REVIEW_PASS`).

API Contract Review: PASS (`STEP_DD_AUTH_018_API_CONTRACT_REVIEW_PASS`).

Audit/Compliance Review: PASS (`STEP_DD_AUTH_018_AUDIT_COMPLIANCE_REVIEW_PASS`).

Final Quality Gate: PASS (`DD_AUTH_018_FINAL_QUALITY_GATE_PASS`).

Governance Acceptance: PASS (`DD_AUTH_018_ACCEPTED_PASS`).

Final Review Status: Accepted.

Implementation Status: Not started.

## Traceability

- Requirements: `AUTH-REQ-007`, `AUTH-REQ-014`.
- Business Rules: `AUTH-BR-012`, `AUTH-BR-016`.
- Accepted authority conflict: DD-AUTH-004, DD-AUTH-007, and DD-AUTH-017.
- Platform authority: Accepted ADR-005.
- Data entities: User Session and Login History Operational History Projection.
- API operation: `auth.changePassword`.
- Planned tests: `PLANNED` — current Session continuity, other-Session revocation, descendant token revocation, unchanged Login History projection under Option A, and immutable Audit Event evidence.

## Supersedes

None. DD-AUTH-018 does not supersede DD-AUTH-004, DD-AUTH-007, or DD-AUTH-017; it is the specific precedence authority for credential-change revocation and Login History projection behavior.

## Superseded By

None.
