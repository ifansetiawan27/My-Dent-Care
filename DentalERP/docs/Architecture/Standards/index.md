# Global Architecture Standards

These documents are canonical vocabulary and governance for every Dental ERP domain and Platform service.

| Standard | Purpose |
|---|---|
| [FieldClassification.md](FieldClassification.md) | Classify every persistent, transient, sensitive, and derived field. |
| [ExposureClassification.md](ExposureClassification.md) | Define Public API, Persistence Only, Sensitive, Secret, Audit Only, Derived Public, and Excluded exposure. |
| [LifecycleSemantics.md](LifecycleSemantics.md) | Define allowed mutations, revocation, expiry, deletion, and retention. |
| [OwnershipResolution.md](OwnershipResolution.md) | Define Resolved, Partially Resolved, and Unresolved ownership states. |
| [AuditPolicy.md](AuditPolicy.md) | Separate immutable audit evidence from operational history/logs/transient state. |
| [DecisionLifecycle.md](DecisionLifecycle.md) | Define unique IDs, statuses, immutability, and superseding decisions. |
| [TraceabilityRules.md](TraceabilityRules.md) | Prevent orphan requirements, fields, endpoints, implementation, and tests. |
| [DriftDetection.md](DriftDetection.md) | Define mandatory cross-artifact consistency checks. |
| [ArchitectureReviewChecklist.md](ArchitectureReviewChecklist.md) | Define the formal Architecture Review and Design Freeze gate. |

## Governance Gate

- [ ] Every standard file exists and links resolve.
- [ ] AGENTS.md references this standards index.
- [ ] Decision Records use the fixed structure and canonical classification terms.
- [ ] New decisions use unique sequential IDs, never version suffixes.
- [ ] Every domain's Traceability Matrix follows TraceabilityRules.md.
- [ ] Design Freeze requires DriftDetection.md PASS and formal Architecture Review PASS.
