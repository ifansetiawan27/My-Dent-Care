# Exposure Classification Standard

## Purpose

Define how fields may be exposed beyond persistence and prevent ambiguous terms such as `Internal`.

## Exposure Levels

| Exposure | Definition |
|---|---|
| Public API | Explicitly documented and returned by a public API contract. |
| Derived Public | Public field computed from canonical persistence fields. |
| Persistence Only | Stored in the database but never exposed through public APIs. |
| Audit Only | Available only to immutable audit/compliance systems under strict authorization. |
| Sensitive | May be exposed only through an explicitly approved, authorized contract with redaction/minimization. |
| Secret | Never exposed, logged, audited, or returned. |
| Excluded | Intentionally omitted from a specific contract; exclusion reason is mandatory. |

## Rules

1. Use `Persistence Only`, not `Internal`, for database-only fields.
2. Every excluded field must state why it is excluded.
3. Public API fields must exist in API.md, OpenAPI, Resource, and tests.
4. Derived Public fields must include a deterministic formula.
5. Sensitive fields require Policy/permission, purpose limitation, and examples that do not leak real data.
6. Secret fields must be `writeOnly` where accepted as input and absent from response schemas.
7. Exposure changes invalidate downstream API, Resource, Test, Documentation, and Traceability gates.
