# Architecture Review Checklist Standard

## Purpose

Provide a mandatory, reusable review gate before every Design Freeze across all Dental ERP domains and Platform services.

## Review Metadata

Every review records:

```text
Canonical Source:
- Requirement / Decision / ADR IDs

Reviewed Against:
- Business Rules
- Database Design
- ERD
- API.md
- OpenAPI
- Traceability Matrix
- Global Architecture Standards

Result:
STEP_xxx_PASS or STEP_xxx_FAIL
```

## Requirement Review

- [ ] Problem, actors, scope, exclusions, NFRs, and testable acceptance criteria exist.
- [ ] Requirement IDs are unique and appear in Traceability Matrix.
- [ ] Multi-tenant, security, audit, performance, retention, and availability needs are explicit.
- [ ] No implementation detail substitutes for a requirement.

## Business Rule Review

- [ ] Rule IDs are unique and every Requirement maps to rules.
- [ ] Invariants, permissions, lifecycle transitions, delete/revocation guards, and edge cases are explicit.
- [ ] Rules use canonical global terminology.
- [ ] No unresolved rule conflicts with an Accepted Decision or ADR.

## Decision Record Review

- [ ] Fixed Decision Record structure is complete and ordered.
- [ ] Field, exposure, lifecycle, and ownership classifications use global standards.
- [ ] Derived fields include formulas.
- [ ] Allowed mutations are explicit.
- [ ] Consequences cover security, performance, migration/compatibility, operations, and tests.
- [ ] Affected Documents and Traceability are complete.
- [ ] Accepted records are immutable; material changes use a new sequential ID and `Supersedes`.
- [ ] Status in the Decision register matches the record.

## ADR Review

- [ ] ADR is used only for architecture-significant cross-domain/platform decisions.
- [ ] Context, Decision, Reasons, Consequences, Status, and supersession links are complete.
- [ ] Accepted ADR is immutable.
- [ ] All affected domains and standards are identified.

## Database Design Review

- [ ] Every entity, field, type, nullability, classification, and ownership state is documented.
- [ ] FK, unique/check constraints, indexes, cardinality, and delete behavior are explicit.
- [ ] Lifecycle, retention, legal hold, archive, and cleanup behavior are documented.
- [ ] Derived fields are not duplicated as authoritative columns without a decision.
- [ ] Query patterns and scale for 10–100 branches are considered.

## ERD Review

- [ ] ERD entities and fields match Database Design exactly.
- [ ] Nullability annotations, cardinality, FK, indexes, and constraints are visible or referenced.
- [ ] No obsolete relationship remains.
- [ ] Every ownership and lifecycle relationship matches Accepted decisions.

## API.md Review

- [ ] Endpoint inventory is stable and versioned.
- [ ] Request/response behavior maps to Business Rules and Decisions.
- [ ] Public, derived, sensitive, and excluded fields follow Exposure Classification.
- [ ] Nullable fields and stable response presence follow the canonical Decision.
- [ ] Error/status behavior and examples are complete.

## OpenAPI Review

- [ ] OpenAPI version, paths, operationId, tags, security, request, response, examples, and schemas are valid.
- [ ] API.md and OpenAPI endpoints/behavior are identical.
- [ ] Internal references resolve.
- [ ] Business Rule and Decision extensions are present where required.
- [ ] No Persistence Only, Secret, or excluded field leaks into public schemas.

## Traceability Review

- [ ] Every Requirement maps through Rules, Decision/ADR, Database Design, ERD, API.md, OpenAPI, implementation, and tests.
- [ ] Every endpoint and Business Rule appears exactly once in coverage mappings.
- [ ] Real decision status is used: TBD, Proposed, Accepted, Superseded, or Rejected.
- [ ] Missing implementation/test is marked `PLANNED`.
- [ ] No orphan artifact exists.

## Drift Detection Review

- [ ] Full mandatory comparisons from `DriftDetection.md` pass.
- [ ] Upstream changes invalidated and re-reviewed affected downstream artifacts.
- [ ] No High/Medium unresolved drift remains.
- [ ] Evidence and exact file references are recorded.

## Design Freeze Gate

Design Freeze returns PASS only when:

- [ ] Requirement Review PASS.
- [ ] Business Rule Review PASS.
- [ ] All relevant Decisions/ADRs Accepted.
- [ ] Database Design and ERD Review PASS.
- [ ] API.md and OpenAPI Review PASS.
- [ ] Traceability Review PASS.
- [ ] Full Drift Detection PASS.
- [ ] Architecture, Security, Data, API, Performance, Audit/Compliance reviews PASS as applicable.
- [ ] No runtime implementation artifact was created before freeze.

Final output must be exactly:

```text
ARCHITECTURE_REVIEW_PASS
```

or:

```text
ARCHITECTURE_REVIEW_FAIL
```
