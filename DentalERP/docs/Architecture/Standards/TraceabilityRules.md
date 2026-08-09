# Traceability Rules Standard

## Purpose

Ensure no Requirement, Business Rule, Decision, data entity, API operation, implementation, or test becomes orphaned.

## Required Chain

```text
Requirement
  -> Business Rule
  -> Decision Record / ADR
  -> Database Design
  -> ERD
  -> API.md
  -> OpenAPI
  -> Implementation
  -> Feature / Unit Tests
```

## Traceability Matrix Requirements

Every Requirement row must include:

- Business Rule IDs.
- ADR references.
- Decision Record IDs and actual status.
- Database Design mapping.
- ERD mapping.
- API.md endpoint/behavior.
- OpenAPI operationId/schema.
- Implementation artifact or `PLANNED`.
- Test artifact or `PLANNED`.

## Rules

1. Every API operation appears exactly once in endpoint traceability.
2. Every Business Rule appears in coverage mapping.
3. Every Accepted/TBD/Proposed Decision appears with its actual status.
4. Missing implementation/test is marked `PLANNED`, never inferred complete.
5. Internal/excluded fields are mapped with an explicit reason.
6. Derived fields include their formula.
7. A superseding Decision updates mappings while preserving historical references.
8. Any upstream change invalidates affected rows until Drift Detection passes.
