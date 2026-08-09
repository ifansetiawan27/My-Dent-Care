# Drift Detection Standard

## Purpose

Detect inconsistency across design, implementation, tests, and documentation before it becomes production risk.

## Mandatory Comparisons

- Requirement ↔ Business Rules.
- Requirement ↔ API Contract.
- Business Rules ↔ Flow and OpenAPI.
- Decision/ADR ↔ Business Rules, ERD, and API.
- Database Design ↔ ERD.
- ERD ↔ API/OpenAPI exposure and nullability.
- API.md ↔ OpenAPI paths, schemas, examples, and HTTP behavior.
- Traceability ↔ every upstream artifact.
- Migration ↔ Database Design/ERD.
- Model ↔ Migration.
- Repository ↔ Repository Interface, ERD, and query contracts.
- Service ↔ Service Interface and Business Rules.
- Request/Resource/Policy/Controller/Routes ↔ OpenAPI and authorization rules.
- Tests ↔ Requirements, Business Rules, Decisions, and API Contract.

## Field-Level Checks

- Name and type.
- Nullability and presence.
- Field classification.
- Exposure classification.
- Ownership resolution state.
- Lifecycle semantics and allowed mutations.
- Derived formula.
- Index/FK/constraint consistency.
- Sensitive/secret redaction.

## Gate Result

Every run returns only:

```text
STEP_xxx_PASS
```

or:

```text
STEP_xxx_FAIL
```

A single unresolved drift blocks Architecture Review, Design Freeze, implementation transition, test sign-off, and commit.
