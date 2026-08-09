# Authentication Design Freeze

## Status

**DESIGN FREEZE — ACTIVE**

Declared: **2026-08-09**

The Stage 01–05 Authentication Design is frozen. This design is the authoritative baseline for Stage 06 implementation.

## Scope

| Stage | Status |
|---|---|
| Stage 01 — Requirement | Frozen |
| Stage 02 — Business Rules | Frozen |
| Stage 03 — Database Design (ERD) | Frozen |
| Stage 04 — API Contract (OpenAPI) | Frozen |
| Stage 05 — Architecture & Flow | Frozen |

## Pre-Freeze Gates

| Gate | Result | Date |
|---|---|---|
| Full Drift Detection (Stages 01–05) | PASS (8/8 design comparisons) | 2026-08-09 |
| Architecture Review (11 criteria) | PASS | 2026-08-09 |
| All Decisions Accepted | Confirmed | 2026-08-09 |
| No unresolved Stage 05 blocker | Confirmed | 2026-08-09 |

## Stage 06 — Migration Status

| Gate | Result | Date |
|---|---|---|
| Migration Draft Reconciliation | PASS | 2026-08-09 |
| Migration Quality Gate | PASS | 2026-08-09 |
| Implementation Preflight | PASS | 2026-08-09 |
| Implementation Readiness | READY | 2026-08-09 |

## Authority Baseline

Active (Accepted) governance authorities in effect at freeze time:

| Authority | Status | Scope |
|---|---|---|
| DD-AUTH-001 | Accepted | Session/Device/Token linkage and revocation |
| DD-AUTH-002 | Accepted | Argon2id password hashing |
| DD-AUTH-003 | Accepted | Self-service Session/Device boundary |
| DD-AUTH-004 | Accepted | Change Password Session behavior |
| DD-AUTH-006 | Accepted | Device list pagination, sort, filter |
| DD-AUTH-007 | Accepted | Authentication lifecycle and audit strategy |
| DD-AUTH-008 | Accepted | Remember Me removal; trusted Device separated |
| DD-AUTH-010 | Accepted | Login History descending index strategy |
| DD-AUTH-017 | Accepted | Field classification, exposure, nullability |
| DD-AUTH-018 | Accepted | Credential-change projection exception |
| ADR-001 | Accepted | Authentication lockout |
| ADR-002 | Accepted | Authentication token strategy |
| ADR-003 | Accepted | Password reset |
| ADR-005 | Accepted | Platform lifecycle and audit policy |
| ADR-006 | Accepted | Audit Evidence vs Login History projection |

Superseded (historical evidence only):

| Authority | Status | Superseded By |
|---|---|---|
| DD-AUTH-005 | Superseded | DD-AUTH-017 |
| ADR-004 | Superseded | ADR-006 |

## Frozen Artifacts

| Artifact | Path |
|---|---|
| Requirement | `docs/Authentication/Requirement.md` |
| Business Rules | `docs/Authentication/BusinessRule.md` |
| ERD | `docs/Authentication/ERD.md` |
| Database Design | `database_design/007_Authentication.md` |
| API Contract | `docs/Authentication/API.md` |
| OpenAPI Specification | `docs/api/openapi.yaml` |
| Flow | `docs/Authentication/Flow.md` |
| Flowchart | `docs/Authentication/Flowchart.md` |
| Sequence Diagram | `docs/Authentication/SequenceDiagram.md` |
| Traceability Matrix | `docs/Authentication/TraceabilityMatrix.md` |
| Architecture Checklist | `docs/Authentication/ArchitectureChecklist.md` |
| Drift Detection Report | `docs/Authentication/DriftDetectionReport.md` |

## Stage 06 Deferred Items

All deferred items resolved during Migration Draft Reconciliation (2026-08-09). Migration drafts now conform to the frozen design.

| Item | Resolution |
|---|---|
| DESC indexes | Login History composite indexes now use explicit `DESC` via raw SQL per DD-AUTH-010 |
| session_id FK | `refresh_tokens` now uses `session_id` FK per ERD; direct user/org/branch/device columns removed |
| user_sessions migration | Created (migration 007) matching complete ERD specification |
| alter_users_table | Reduced to metadata-only comments; structural column changes deferred to Users domain |
| personal_access_tokens session_id | Alter migration (009) adds `session_id` column, UNIQUE constraint, and CASCADE FK per ERD |

## Governance Rule

This Design Freeze is the authoritative baseline. Stage 06 implementation must conform to the frozen design artifacts listed above.

If Stage 06 discovers a genuine design contradiction, implementation must stop. The issue must be raised through the governance process (new Decision Record or superseding ADR) rather than silently changing the frozen design.

## Review Evidence

- Architecture Review: `docs/Authentication/ArchitectureChecklist.md`
- Full Drift Detection: `docs/Authentication/DriftDetectionReport.md`
- Traceability: `docs/Authentication/TraceabilityMatrix.md`
