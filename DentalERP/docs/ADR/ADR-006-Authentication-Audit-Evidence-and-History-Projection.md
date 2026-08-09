# ADR-006

## Title

Authentication Audit Evidence and Login History Projection Authority

## Status

Accepted

This ADR is the Accepted authority distinguishing canonical Authentication Audit Events from the Login History Operational History Projection. ADR-004 is superseded to the extent defined by this ADR and remains immutable historical evidence.

## Context

Accepted ADR-004 established durable Authentication auditability and separated persistent Authentication history from transient state. Its consequence describes `login_histories` as immutable without an explicit field-level exception.

Subsequent Accepted authorities refined the platform and Authentication lifecycle model:

- ADR-005 distinguishes canonical Immutable Audit Events from Operational History Projections.
- DD-AUTH-007 classifies Login History as an Operational History Projection and permits an explicitly documented controlled mutation.
- DD-AUTH-017 classifies `login_histories.logout_at` as nullable lifecycle-generated data using `Controlled One-Time Mutation`.
- DD-AUTH-018 establishes that credential-change revocation is a specific exception and does not invoke the `logout_at` mutation.

The original ADR-004 statement and the later Accepted lifecycle model cannot remain unconditional authorities for the same projection behavior. A superseding ADR is required because ADR-004 is Accepted and immutable in intent.

## Problem Statement

The platform needs one explicit ADR-level authority that distinguishes canonical Authentication audit evidence from the Login History operational projection while preserving the valid intent of ADR-004.

The decision must answer:

1. Which records are canonical audit evidence?
2. Which record is an Operational History Projection?
3. Whether the projection may contain a controlled mutable field without weakening immutable audit evidence.
4. To what extent ADR-004 must be superseded.

## Decision Drivers

- Preserve immutable and append-only canonical audit evidence.
- Preserve Accepted DD-AUTH-007, DD-AUTH-017, DD-AUTH-018, and ADR-005 intent.
- Keep Login History query-friendly without treating it as canonical evidence.
- Make the allowed `logout_at` mutation explicit and deterministic.
- Preserve Accepted ADR history through formal supersession rather than direct edits.
- Avoid schema, API, or Authentication workflow redesign.

## Options Considered

### Option A — Keep ADR-004 Unchanged as the Unconditional Authority

Treat all Login History fields as immutable and reject the later controlled `logout_at` mutation.

Consequences:

- Conflicts with Accepted DD-AUTH-007 and DD-AUTH-017.
- Reopens Accepted field and lifecycle decisions.
- Prevents a coherent authority chain.

### Option B — Distinguish Canonical Audit Evidence from Operational History Projection

Preserve immutable canonical Audit Events while recognizing Login History as an Operational History Projection with immutable default fields and one explicitly governed `logout_at` mutation.

Consequences:

- Preserves ADR-004's durable-audit intent.
- Aligns ADR-level authority with ADR-005 and Accepted Authentication Decisions.
- Supersedes ADR-004 only where it treats Login History itself as unconditionally immutable canonical evidence.
- Requires governance and downstream reference synchronization after acceptance.

### Option C — Treat Login History as the Canonical Audit Event Store

Use Login History as canonical audit evidence and retain its controlled mutation.

Consequences:

- Violates ADR-005 audit/projection separation.
- Weakens canonical evidence semantics.
- Conflicts with DD-AUTH-007 and DD-AUTH-017.

## Decision

Select Option B — Distinguish Canonical Audit Evidence from Operational History Projection.

Normative policy:

1. Canonical Authentication Audit Events are the sole authoritative audit evidence for material Authentication actions and outcomes.
2. Canonical Audit Events are `Append Only` and `Immutable`.
3. Login History is an `Operational History Projection`, not canonical audit evidence.
4. Login History fields are `Immutable` by default after creation.
5. `login_histories.logout_at` may perform exactly one `Controlled One-Time Mutation` from `NULL` to the approved timestamp under Accepted Authentication lifecycle authority.
6. The `logout_at` mutation never mutates, rewrites, replaces, or weakens canonical Audit Events.
7. Accepted DD-AUTH-018 remains the specific precedence authority: credential-change revocation does not invoke the `logout_at` mutation.
8. Audit evidence remains available through separate immutable canonical Audit Events regardless of Login History projection state.

The policy is binding within the audit-evidence and Login History projection scope of this Accepted ADR.

## Reasons

- Option B preserves the valid durable-audit objective of ADR-004.
- It aligns the ADR layer with the Accepted platform Data Category model.
- It prevents an operational projection from being mistaken for canonical evidence.
- It permits only the mutation already governed by Accepted domain decisions.
- It resolves authority drift without changing schema or API behavior.

## Consequences

### Positive

- Canonical audit authority becomes unambiguous.
- Login History lifecycle becomes consistent across ADR and Decision layers.
- Existing Accepted Authentication behavior remains unchanged.
- No schema, ERD, request, response, or endpoint change is introduced.

### Governance

- ADR-004 is superseded by ADR-006 to the extent that it treats Login History as unconditionally immutable canonical audit evidence.
- ADR-004 metadata must reference ADR-006 through the separate post-acceptance synchronization task.
- ADR-004 remains immutable historical evidence after supersession.
- ADR-005 remains the platform lifecycle and audit policy.
- DD-AUTH-007 remains the Authentication lifecycle authority.
- DD-AUTH-017 remains the Authentication field-policy authority.
- DD-AUTH-018 remains the credential-change projection-precedence authority.

### Operations and Tests

- Audit-integrity tests remain `PLANNED` until implementation stages.
- Planned tests must distinguish immutable canonical Audit Events from Login History projection behavior.
- Planned tests must verify `logout_at` idempotency and the DD-AUTH-018 credential-change exception.

## Dependencies

- `docs/ADR/ADR-004-Authentication-Audit.md` — Superseded historical authority within the scope defined by ADR-006; metadata synchronization remains separate.
- `docs/ADR/ADR-005-Platform-Lifecycle-Audit-Policy.md` — Accepted platform lifecycle and audit authority.
- `docs/Authentication/Decision/DD-AUTH-007.md` — Accepted Authentication lifecycle authority.
- `docs/Authentication/Decision/DD-AUTH-017.md` — Accepted Authentication field-policy authority.
- `docs/Authentication/Decision/DD-AUTH-018.md` — Accepted credential-change projection-precedence authority.

Dependency direction is one-way. ADR-006 reconciles the ADR layer with Accepted authorities; none of those Accepted Decisions depends on ADR-006 for its historical acceptance. There is no circular dependency.

## Related ADRs and Decisions

- ADR-004: Superseded by ADR-006 and retained as immutable historical evidence.
- ADR-005: remains Accepted and is not superseded.
- DD-AUTH-007: remains Accepted and is not superseded.
- DD-AUTH-017: remains Accepted and is not superseded.
- DD-AUTH-018: remains Accepted and is not superseded.

## Affected Documents

Post-acceptance synchronization only:

- `docs/ADR/ADR-004-Authentication-Audit.md` — metadata only: mark Superseded and reference ADR-006.
- `docs/ADR/index.md`
- `docs/Authentication/Decision/DependencyGraph.md`
- `docs/Authentication/TraceabilityMatrix.md`
- Any downstream documentation that still treats Login History as canonical immutable Audit Evidence.

Downstream synchronization is a separate post-acceptance SDLC task and does not occur through Governance Acceptance itself.

## Traceability

- Requirements: `AUTH-REQ-010`, `AUTH-REQ-014`.
- Business Rules: `AUTH-BR-005`, `AUTH-BR-008`, `AUTH-BR-009`, `AUTH-BR-016`.
- Data entity: Login History Operational History Projection.
- Canonical evidence: Authentication Audit Events in the Audit Platform.
- API operations: `auth.logout`, `auth.logoutAll`, `auth.changePassword`, `auth.loginHistory.index`.
- Planned tests: `PLANNED` — audit immutability, projection separation, `logout_at` controlled mutation, and credential-change exception.

## Review Status

- Architecture Review: PASS (`STEP_ADR_006_ARCHITECTURE_REVIEW_PASS`).
- Security Review: PASS (`STEP_ADR_006_SECURITY_REVIEW_PASS`).
- Data Review: PASS (`STEP_ADR_006_DATA_REVIEW_PASS`).
- API Contract Review: PASS (`STEP_ADR_006_API_CONTRACT_REVIEW_PASS`).
- Audit/Compliance Review: PASS (`STEP_ADR_006_AUDIT_COMPLIANCE_REVIEW_PASS`).
- Platform Review: PASS (`STEP_ADR_006_PLATFORM_REVIEW_PASS`).
- Final Quality Gate: PASS (`ADR_006_FINAL_QUALITY_GATE_PASS`).
- Governance Acceptance: PASS (`ADR_006_ACCEPTED_PASS`).
- Final Review Status: Accepted.
- Implementation Status: Not started.

## Supersedes

ADR-004, to the extent that it treats Login History as unconditionally immutable canonical audit evidence. ADR-004 remains immutable historical evidence; its metadata synchronization is a separate task.

## Superseded By

None.
