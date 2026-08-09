# ADR-005

## Title

Platform Lifecycle and Audit Policy

## ADR Metadata

| Item | Value |
|---|---|
| Identifier | ADR-005 |
| Scope | Platform-wide |
| Originating Decision | `DD-AUTH-007` |
| Decision Type | Architecture-significant cross-domain policy |
| Supersedes | None |
| Implementation Authority | Accepted platform policy; implementation remains gated by downstream synchronization, Full Drift Detection, Architecture Review, and Design Freeze |

## Status

Accepted

This ADR is the Accepted platform lifecycle and audit authority. Acceptance does not by itself authorize implementation or Design Freeze; downstream synchronization, Full Drift Detection, and final Architecture Review remain separate gates.

## Context

Dental ERP needs one platform policy for distinguishing durable audit evidence, query-oriented history, mutable operational state, security data, and ordinary business records. These objects do not share one safe mutation, deletion, retention, archive, or cleanup model.

`docs/Architecture/Standards/` remains the canonical source for field classification, exposure, lifecycle, ownership, audit, traceability, drift detection, and architecture review vocabulary. This ADR applies that vocabulary as a cross-domain policy. It does not define schemas, APIs, implementation classes, or business-specific retention durations.

`DD-AUTH-007` is the source domain decision for Authentication. It remains the Authentication-specific implementation decision and is not superseded by this ADR.

## Problem Statement

- Treating audit evidence as an ordinary Business Record weakens immutability and forensic integrity.
- Treating operational projections as canonical audit evidence obscures their query purpose and allowed mutations.
- Applying `Soft Deletable` to all security data retains secret material beyond its approved purpose.
- Allowing domains to invent lifecycle terminology creates inconsistent retention, archive, Legal Hold, and cleanup behavior.
- Mixing data category with lifecycle semantics creates overlapping classifications and ambiguous authority.

The platform requires a mutually exclusive primary data category model, canonical lifecycle semantics, and common governance while preserving domain ownership of concrete behavior.

## Decision Drivers

- Audit integrity and reliable forensic evidence.
- Separation of evidence, projections, operational state, security data, and business records.
- Secret minimization and deterministic disposal.
- Consistent tenant-aware governance.
- Adaptability to domain, contractual, and jurisdictional obligations.
- Scalable archive and cleanup for 10-100 branches.
- Traceability from platform policy to domain design and planned tests.
- Preservation of Accepted Decision and ADR history.

## Options Considered

### Option A - Universal Soft Delete

Apply `Soft Deletable` to every persistent object. This is uniform and recoverable for ordinary business workflows, but it misclassifies immutable evidence and security data, retains secrets unnecessarily, and cannot express revocation, expiry, or append-only evidence correctly.

### Option B - Lifecycle Policy by Primary Data Category

Assign every persistent object exactly one primary data category, then document applicable lifecycle semantics using only `LifecycleSemantics.md`. This separates architectural purpose from behavior and supports category-appropriate governance, but requires explicit domain classification and review.

### Option C - Mandatory Event Sourcing

Require events as the sole source of truth and rebuild all projections. This offers strong temporal evidence but imposes disproportionate migration, query, implementation, and operational complexity.

## Decision

Select Option B: lifecycle policy by primary data category.

1. Every persistent object has exactly one primary data category from this ADR.
2. Primary data categories are mutually exclusive.
3. Data category identifies architectural purpose. Lifecycle semantics identify allowed behavior. Field and exposure classifications remain separate dimensions.
4. An object may use multiple applicable canonical lifecycle semantics when explicitly documented without acquiring multiple primary data categories.
5. Every domain maps its concrete persistent objects and documents allowed mutations before Design Freeze.
6. Domain policy may strengthen this policy but may not weaken it without a superseding platform Decision or ADR.
7. This ADR operates through synchronized repository governance and does not directly supersede `AGENTS.md` or an unrelated Accepted ADR.

## Reasons

- One primary data category gives every persistent object an unambiguous architectural purpose.
- Separate lifecycle semantics allow an object to support applicable behavior without overlapping its primary category.
- Immutable Audit Events remain authoritative evidence instead of becoming ordinary operational records.
- Category-aware policy preserves `Soft Deletable` for Business Records while allowing appropriate security-data termination.
- Platform governance remains reusable while concrete objects, transitions, durations, schemas, APIs, and tests remain domain-owned.

## Consequences

### Positive

- Audit evidence remains distinct from operational history and mutable state.
- Business Records retain a recoverable default without imposing it on every category.
- Security data can terminate through approved `Revocable`, `Expiring`, and `Hard Deletable` semantics.
- Domains share one vocabulary and governance model.

### Costs and Risks

- Every domain must classify concrete objects and document allowed mutations.
- Incorrect classification can cause premature deletion, over-retention, or loss of audit integrity.
- Domain and deployment reviews remain necessary for legal, contractual, performance, and operational requirements.

### Reviewable Consequences

- Security review validates secret handling, revocation, expiry, and cryptographic destruction.
- Performance review validates archive, Legal Hold, and cleanup at expected scale.
- Existing persistent objects require classification and drift review before lifecycle or schema changes.
- Operations must be bounded, resumable, observable, tenant-aware, and auditable.
- Planned tests cover category exclusivity, allowed mutations, audit immutability, retention eligibility, archive integrity, Legal Hold blocking, and cleanup authorization.

## Data Category Model

Data Category is an object-level architecture concept defined by this ADR. It is separate from the field-level classifications in `docs/Architecture/Standards/FieldClassification.md`.

Every persistent object must declare exactly one primary Data Category from this model:

| Primary Data Category | Architectural Purpose | Exclusive Boundary |
|---|---|---|
| Immutable Audit Event | Canonical compliance and forensic evidence of a material action or outcome | Not an Operational History Projection, current operational state, security datum, or Business Record |
| Operational History Projection | Query-oriented historical representation derived from or associated with operational and audit evidence | Not the canonical Audit Event source, current mutable state, security datum, or Business Record |
| Mutable Operational State | Current persistent state used to execute an operational workflow | Not canonical audit evidence, a history-only projection, security datum, or Business Record |
| Revocable Security Data | Persistent security data whose primary invalidation mechanism is an explicit revocation action | Not Expiring Security Data, even when a defensive expiry boundary also exists |
| Expiring Security Data | Persistent security data whose primary invalidation mechanism is time-based expiry or approved single-use completion | Not Revocable Security Data, even when exceptional early revocation is supported |
| Business Record | Persistent domain record supporting ordinary business workflows | Not canonical audit evidence, a history-only projection, current platform operational state, or security datum |

Data Category rules:

1. One persistent object has exactly one primary Data Category; overlapping primary assignments are prohibited.
2. Category assignment follows the object's primary architectural purpose, not every behavior or field it contains.
3. Fields independently use the canonical classifications and optional secondary sensitivity labels from `FieldClassification.md`; field classifications do not create additional object Data Categories.
4. Exposure independently uses `ExposureClassification.md`; exposure does not change the object's Data Category.
5. Lifecycle semantics independently define permitted behavior and do not change the object's Data Category.
6. Revocable Security Data and Expiring Security Data are distinguished by their primary invalidation mechanism. An optional secondary lifecycle semantic does not create category overlap.
7. Ambiguous category assignment blocks the affected domain's Design Freeze until an approved Decision resolves it.
8. Technical logs and transient state remain governed by `AuditPolicy.md`. If either becomes persistent, the adopting domain must assign one primary Data Category through an approved Decision.

### Security Data Boundary

Data Category, Field Classification, and Lifecycle Semantics are independent dimensions and must not be merged:

| Dimension | Canonical Concept | Meaning | Normative Boundary |
|---|---|---|---|
| Object-level Data Category | Business Record | A persistent object whose primary architectural purpose is an ordinary domain business workflow | This is the object category represented by the phrase "Business Data" in lifecycle discussions; it does not classify individual fields and does not imply that every field is public or non-sensitive |
| Field Classification | `Secret` | A field containing credential, token, key, or equivalent material that must never be exposed, logged, audited, or archived | `Secret` never becomes an object-level Data Category and does not by itself authorize deletion |
| Lifecycle Semantic | `Soft Deletable` | The default deletion behavior for a Business Record | This semantic applies to the object lifecycle and does not weaken handling rules for fields classified `Sensitive` or `Secret` |
| Lifecycle Semantic | `Hard Deletable` | Physical deletion permitted only by an Accepted lifecycle and retention decision | This semantic applies to an object or approved field lifecycle and does not create a Data Category or field classification |

Normative relationship:

1. **Soft Deletable Business Data** means a Business Record Data Category whose approved lifecycle uses `Soft Deletable`; it is not a separate Data Category.
2. **Hard Deletable Secret Data** means data containing a field classified `Secret` whose approved lifecycle uses `Hard Deletable` after its security purpose ends; it is not a separate Data Category.
3. A persistent object containing a `Secret` field still has exactly one primary Data Category, selected from the Data Category Model by the object's primary architectural purpose.
4. A `Secret` field follows the strict Secret exposure rules regardless of the containing object's Data Category or lifecycle.
5. `Hard Deletable` authority requires the approved lifecycle, retention eligibility, Legal Hold evaluation, detached redacted evidence where required, and authorized cleanup. Field classification alone never grants that authority.
6. `Soft Deletable` is never used to retain Secret material beyond its approved security purpose. When a Soft Deletable Business Record contains Secret material, the Secret field follows its separately approved destruction lifecycle while the Business Record remains Soft Deletable.

### Exposure Classification

This policy adopts every canonical exposure level from `docs/Architecture/Standards/ExposureClassification.md`. Exposure is a field/contract dimension and never changes an object's primary Data Category or lifecycle semantics.

| Exposure Level | Platform Principle |
|---|---|
| `Public API` | Exposure is permitted only when explicitly documented by an approved public contract and synchronized with all dependent contract and validation artifacts. |
| `Derived Public` | Exposure is permitted only as a deterministic value computed from named canonical sources; it is never independently authoritative or persistently mutated as public truth. |
| `Persistence Only` | Data may be persisted for approved domain purposes but is excluded from public contracts. |
| `Audit Only` | Data is available only through authorized audit/compliance capabilities with purpose, ownership/tenant scope, and access evidence. |
| `Sensitive` | Exposure requires an explicitly approved authorized contract, least privilege, purpose limitation, and redaction or minimization where applicable. |
| `Secret` | Data is never exposed, returned, logged, audited, archived, or included in examples; accepted input handling follows the canonical write-only rule. |
| `Excluded` | Data is intentionally omitted from a specific contract and the exclusion reason must be documented. |

Exposure rules:

1. Every field governed by a contract receives exactly one applicable exposure level for that contract.
2. Exposure changes require review and invalidate affected downstream contract gates according to `ExposureClassification.md`.
3. `Sensitive` and `Secret` exposure levels do not create object-level Data Categories.
4. No platform exposure principle creates an endpoint, request, response, route, method, or schema. Domain contracts define those details under these boundaries.

### Stable Contract Principles

1. **Stable response shape.** A field documented by an approved contract remains present for the lifetime of that contract version. Its value may be `null` only when the contract explicitly defines it as nullable; lifecycle transitions do not silently remove documented fields.
2. **Backward-compatible contract evolution.** Contract evolution follows repository governance and preserves existing client behavior within the active contract version. A removal, rename, type change, nullability tightening, semantic reinterpretation, or exposure reduction that can break clients requires the approved breaking-change and versioning process.
3. **Deterministic lifecycle visibility.** Any lifecycle state exposed by a domain contract is derived from canonical lifecycle sources and has one documented meaning across equivalent operations. Visibility must not contradict the object's Data Category, lifecycle semantics, ownership resolution, or exposure classification.
4. `Derived Public` lifecycle visibility identifies its canonical source fields and deterministic formula. It is never an independently mutable source of truth.
5. `Persistence Only`, `Audit Only`, `Sensitive`, `Secret`, and `Excluded` data remain outside ordinary public contract visibility according to their canonical exposure boundaries.
6. Lifecycle or retention changes do not alter an approved public contract implicitly. Any contract impact requires explicit review, traceability, and synchronization through the applicable governance gates.
7. These principles define platform contract invariants only. Concrete fields, schemas, requests, responses, methods, routes, status codes, and examples remain domain-contract responsibilities.

### Audit and Lifecycle Visibility

| Visibility Concept | Platform Meaning | Contract Boundary |
|---|---|---|
| Canonical persisted lifecycle state | The authoritative stored source used to evaluate the object's approved lifecycle semantics and transitions | Persistence does not imply public exposure; authority remains with the canonical source identified by the domain Decision |
| `Derived Public` lifecycle visibility | A non-authoritative public representation computed deterministically from named canonical persisted lifecycle sources | It may appear only in an approved contract with an explicit formula and nullability; it cannot be mutated independently or used to rewrite canonical state |
| `Persistence Only` lifecycle state | Internal persisted state required for lifecycle evaluation, ownership, retention, or cleanup | It does not automatically become part of a public contract and may be exposed only after an approved exposure-classification change |
| `Audit Only` lifecycle evidence | Immutable evidence of lifecycle actions, actors, scope, reasons, and outcomes | It is not ordinary public lifecycle state and is available only through authorized audit/compliance capabilities |

Visibility rules:

1. Canonical persisted lifecycle state is the storage source of truth; public visibility never becomes an alternative authority.
2. `Derived Public` values always identify their canonical source fields and deterministic formula.
3. `Persistence Only` and `Audit Only` state remain non-public by default and are never inferred into a public contract merely because they exist.
4. Immutable Audit Events preserve evidence but do not replace the canonical operational lifecycle source unless an Accepted domain Decision explicitly establishes an event as that source.
5. A lifecycle state classified `Sensitive`, `Secret`, or `Excluded` follows its stricter exposure boundary regardless of derivability.
6. Domain contracts select lifecycle visibility under these platform rules without changing the underlying Data Category or lifecycle semantics.

## Lifecycle Model

Only this vocabulary from `LifecycleSemantics.md` is authoritative:

- `Immutable`
- `Append Only`
- `Controlled One-Time Mutation`
- `Mutable Operational State`
- `Revocable`
- `Expiring`
- `Soft Deletable`
- `Hard Deletable`

Every concrete object must define:

| Field | Initial State | Allowed Mutation | Trigger | Final State | Repeatable | Audit Event |
|---|---|---|---|---|---:|---|

`Immutable` cannot coexist with undocumented updates. `Append Only` permits new records but no mutation of existing records. `Controlled One-Time Mutation` requires one explicit transition. `Mutable Operational State` permits only approved transitions. `Revocable` and `Expiring` are independent. `Soft Deletable` is the default deletion semantic only for Business Records. `Hard Deletable` requires an Accepted lifecycle and retention decision, retention eligibility, Legal Hold clearance, and audit evidence. Unlisted fields inherit the documented object default.

### Deterministic Security Lifecycle

The transition names below describe lifecycle operations, not additional lifecycle semantics. Applicable objects continue to use only `Controlled One-Time Mutation`, `Mutable Operational State`, `Revocable`, `Expiring`, and `Hard Deletable` as defined by `LifecycleSemantics.md`.

Canonical sequence where applicable:

```text
Issuance
    -> Rotation (optional)
    -> Revocation or Expiration
    -> Destruction (when applicable)
```

| Operation | Trigger | Authority | Resulting State | Forbidden Transition |
|---|---|---|---|---|
| Issuance | Approved security purpose begins after identity, ownership, tenant scope, and policy requirements are resolved | Authorized Platform security capability or explicitly delegated domain security authority | One valid security object with immutable identity, bounded ownership, configured expiry where applicable, and Secret material protected under `FieldClassification.md` | Issue without resolved authority or required ownership; persist an unprotected Secret; create duplicate valid authority where the approved invariant permits only one |
| Rotation (optional) | Approved replacement policy is invoked while the current security object remains valid and eligible | Authorized Platform security capability or explicitly delegated domain security authority | Replacement security object becomes valid; predecessor becomes `Revocable` through an atomic `Controlled One-Time Mutation`; lineage and detached Audit Event evidence are preserved | Leave predecessor and replacement simultaneously valid when prohibited; rotate an invalid, revoked, expired, or destroyed object; change owner or tenant scope; reuse predecessor authority after rotation |
| Revocation | Authorized actor action, security policy, ownership termination, compromise signal, or dependent-authority revocation | Authorized Platform security capability, validated owner within scope, or explicitly delegated domain security authority | Object becomes `Revocable` and cannot grant further authority; dependent revocation follows the approved boundary | Reactivate a revoked object; revoke outside validated tenant/owner scope; preserve dependent authority when policy requires cascade revocation |
| Expiration | Approved validity boundary is reached using the authoritative time source or approved single-use completion occurs | Authorized Platform lifecycle capability or explicitly delegated domain capability enforcing approved policy | Object becomes `Expiring` and cannot grant further authority | Extend validity in place without an approved new lifecycle; treat an expired object as valid; conflate expiration with an explicit revocation event |
| Destruction (when applicable) | Security purpose has ended, the object is revoked or expired as required, retention eligibility is satisfied, applicable Legal Hold evaluation is complete, and detached redacted evidence exists where required | Restricted Platform security or data-governance authority, or explicitly delegated cleanup authority under approved policy | Approved Secret material and security record become irreversibly `Hard Deletable`; detached Immutable Audit Event evidence remains | Destroy before revocation/expiration or retention eligibility; destroy evidence required by policy; archive Secret material; reconstruct, restore, or reactivate destroyed authority |

Security lifecycle rules:

1. Rotation is optional and applies only when an approved domain policy defines replacement semantics.
2. Revocation and expiration are independent terminal validity conditions; neither transition restores or extends authority.
3. Destruction is not issuance, rotation, revocation, or expiration. It occurs only after the applicable terminal validity condition and governance checks.
4. Every material transition emits an Immutable Audit Event without Secret material.
5. Domain Decisions define concrete security objects and transition boundaries without weakening this platform sequence.

## Audit Policy

1. Immutable Audit Events are `Append Only` and `Immutable` canonical evidence.
2. Operational History Projections, technical logs, transient state, and Business Records are not substitutes for canonical Audit Events.
3. Every material lifecycle transition requiring evidence emits an Immutable Audit Event.
4. Audit context includes event type, actor when resolved, target when applicable, Organization and Branch when resolved, timestamp, correlation identifier, reason for administrative or high-risk action, and outcome.
5. Cross-tenant administrative actions preserve both actor and target tenant context.
6. Passwords, token values, password hashes, token hashes, authorization headers, cookies, and other Secret data never enter Audit Event payloads.
7. Audit access requires explicit authorization, tenant and target scope, purpose, and audit evidence for high-risk access.

## Retention Policy

This ADR defines policy only and assigns no business-specific duration.

### Retention Authority

| Authority Type | Responsibility | Boundary |
|---|---|---|
| Policy Authority | Establishes, owns, versions, and changes platform retention categories, policy rules, start triggers, eligibility conditions, and the permitted range for domain refinement | Requires an Accepted platform Decision or ADR; cannot execute archive, purge, or destruction merely by defining policy |
| Execution Authority | Evaluates approved retention eligibility and performs authorized archive, cleanup, purge, or cryptographic destruction | Executes only an approved policy within validated ownership and tenant scope; cannot create, shorten, extend, or reinterpret retention policy |
| Review Authority | Reviews proposed retention-policy changes for lifecycle consistency, ownership, referential preservation, auditability, operational impact, and cross-domain compatibility | Review does not itself approve regulatory applicability or authorize execution |
| Compliance Authority | Approves or rejects retention-policy changes that affect legal, regulatory, contractual, privacy, erasure, or Legal Hold obligations | Approval is required before a regulatory-impacting change can become binding; approval does not bypass platform governance or execution controls |

Authority rules:

1. Policy Authority, Execution Authority, Review Authority, and Compliance Authority are distinct responsibilities even when an approved governance model assigns more than one responsibility to the same capability.
2. No single authority can unilaterally define policy, approve its regulated impact, and execute destructive action.
3. A retention-policy change becomes binding only after Policy Authority acceptance, applicable Review Authority PASS, applicable Compliance Authority approval, and synchronization through repository governance.
4. Domain policy may refine an Accepted platform policy only within its delegated boundary and cannot weaken platform minimums or bypass Legal Hold.
5. Execution Authority always evaluates the policy version effective for the object and records that version in immutable execution evidence.

1. Retention is defined per primary data category and refined by domain purpose, legal obligation, contract, security purpose, and risk.
2. Every rule defines a deterministic start trigger and eligibility condition.
3. Retention eligibility is independent from validity, activity, revocation, or expiry.
4. Archive, purge, and cryptographic destruction require retention eligibility and an atomic Legal Hold check.
5. Domain or deployment policy may extend retention for non-secret evidence when lawful and justified.
6. Secret values and hashes are not retained indefinitely for audit or Legal Hold; required evidence is detached and redacted.
7. Concrete durations require applicable domain, Security, Data, and Audit/Compliance review before implementation.
8. While a duration is unresolved, destructive action against the affected non-secret record remains blocked.

## Archive Policy

### Eligibility

- Operational purpose has ended and the approved archive trigger is satisfied.
- Category, retention policy, ownership scope, integrity requirements, and Legal Hold have been evaluated.
- Secret values and hashes are not archive-eligible.

### Authority

- Only an authorized Platform retention capability or explicitly delegated domain retention capability may archive.
- Authority enforces tenant scope, least privilege, purpose, required approval, and Immutable Audit Event evidence.

### Restoration Policy

- Archive is a storage transition, not deletion.
- Restoration never rewrites archived evidence.
- Restoration may re-enable authorized access or create a controlled operational copy under approved domain policy.
- Archives preserve identifiers, ownership context, integrity metadata, retention status, and Legal Hold state.
- Canonical Audit Evidence remains the sole source of truth for the action or outcome it records.
- Archive Evidence remains canonical in its approved storage medium; an archive transition or authorized access does not change its evidentiary status or immutability.
- A controlled operational copy created from an archive is explicitly non-canonical and may be used only for an authorized operational purpose.
- A controlled operational copy cannot replace canonical Audit Evidence, cannot become canonical merely through use or persistence, and cannot be used as the basis for changing canonical Audit Evidence.
- Creation, use, correction, or deletion of a controlled operational copy cannot alter the immutable status, content, integrity metadata, retention status, or Legal Hold state of canonical Audit Evidence or Archive Evidence.
- Any decision derived from a controlled operational copy must remain traceable to the unchanged canonical evidence without treating the copy as authoritative evidence.

## Legal Hold Policy

### Creation

Authorized Legal/Compliance authority creates a hold with scope, reason, source authority, start time, optional expiry, approver, ownership context, and correlation identifier. Creation emits an Immutable Audit Event.

### Release

Authorized Legal/Compliance authority releases a hold with an Immutable Audit Event. Release does not restart retention; eligibility is recalculated from the original trigger and preserved policy.

### Ownership

Legal Hold metadata is Platform-owned. Ordinary tenant and domain users cannot create, alter, release, or bypass it. Domain services may report eligible records but cannot override Platform authority.

### Interaction with Retention

- Legal Hold suspends purge and cryptographic destruction of covered non-secret evidence.
- Archive is permitted only when hold and integrity requirements remain preserved.
- Legal Hold does not require plaintext secrets or secret hashes beyond their approved security purpose.
- Cleanup checks hold state atomically immediately before destructive action.

### Legal Hold Precedence

The following platform precedence is authoritative when Secret destruction, retention, and Legal Hold overlap:

1. Approved destruction of fields classified `Secret` follows their security lifecycle and is irreversible.
2. Legal Hold preserves detached Immutable Audit Event evidence and other covered non-secret evidence that still exists when the hold applies.
3. Legal Hold cannot restore, reconstruct, regenerate, or otherwise recover Secret material that has already been destroyed.
4. Detached immutable evidence remains available according to the approved Legal Hold and retention policy without containing the destroyed Secret material.
5. Legal Hold applies only to evidence that still exists. It does not create a recovery obligation or recovery path for previously destroyed material.
6. When Secret-retention and evidence-preservation policies conflict, approved Secret destruction takes precedence for the Secret material while detached Immutable Audit Event evidence remains preserved.
7. Hold creation, release, expiry, and every destruction decision remain independently auditable without recording Secret material.

## Cleanup Authority

| Action | Authority | Mandatory Conditions |
|---|---|---|
| Revocation | Authorized Platform security capability or explicitly delegated domain authority | Approved `Revocable` lifecycle, authenticated actor or authorized system trigger, tenant scope, atomic state transition, Immutable Audit Event evidence |
| Expiration | Authorized Platform lifecycle capability or domain capability enforcing approved policy | Approved `Expiring` lifecycle, deterministic time source, idempotent transition, observable outcome |
| Archive | Authorized Platform retention capability or delegated domain retention capability | Category, tenant scope, eligibility, integrity evidence, Legal Hold evaluation |
| Purge | Authorized retention process under approved Platform and domain policy | `Hard Deletable`, retention eligibility, Legal Hold clearance, referential safety, required approval, Immutable Audit Event evidence |
| Cryptographic destruction | Restricted Platform security or data-governance authority | Retention eligibility, Legal Hold clearance for covered non-secret evidence, dual control where required, irreversible-action validation, Immutable Audit Event evidence |

Cleanup is authorized, idempotent, resumable, bounded, tenant-aware, observable, and auditable. It never cascade-deletes Immutable Audit Events. Secret values and hashes never enter archives, Audit Event payloads, or destruction evidence. Failure leaves the object in a safe state and cannot record false success.

### Background Processing

The following lifecycle operations execute as asynchronous background processes where applicable:

- Archive.
- Retention enforcement.
- Cleanup.
- Purge.
- Cryptographic destruction.
- Legal Hold evaluation for lifecycle eligibility and destructive action.

These operations are operational lifecycle work and are not part of a normal business transaction. A normal business transaction may record the lifecycle transition, policy version, eligibility marker, and immutable evidence needed for later processing, but it does not perform or wait for archive movement, retention enforcement, cleanup, purge, cryptographic destruction, or full Legal Hold evaluation to complete.

Background processing must preserve the result and atomicity of the originating business transaction. A delayed, retried, failed, or incomplete background operation cannot reverse a committed business outcome, grant authority, bypass Legal Hold, or record destructive action as successful before completion.

Every background lifecycle operation must be:

- **Idempotent:** repeating the same authorized operation does not duplicate effects or corrupt lifecycle state.
- **Retry-safe:** retrying after interruption or failure preserves authorization, ownership, Legal Hold, retention, and audit invariants.
- **Resumable:** incomplete work can continue from a verified safe state without restarting completed destructive effects.
- **Bounded:** each execution has an explicit finite scope and cannot process an unbounded object set.

These are platform behavior requirements only. This ADR does not prescribe a retry algorithm, timing policy, transport, orchestration mechanism, or infrastructure implementation.

### Transaction Boundary

1. A long-running lifecycle operation must never execute inline with or block a normal business transaction.
2. A normal business transaction performs only the minimum synchronous changes required to preserve its business invariant, lifecycle intent, ownership scope, policy version, and immutable evidence.
3. Archive movement, retention enforcement, cleanup, purge, cryptographic destruction, and complete lifecycle eligibility processing continue through governed background processing after the business transaction commits.
4. The business transaction does not wait for physical lifecycle processing to complete and does not report that processing as complete before verified completion evidence exists.
5. Background completion or failure cannot reopen the committed business transaction or make an invalid, revoked, expired, archived, purged, or destroyed object authoritative again.
6. Any lifecycle operation that requires an atomic validity or authorization transition performs only that minimum transition within the business boundary; long-running follow-up work remains outside it.

## Ownership Resolution

This policy adopts the canonical ownership states from `docs/Architecture/Standards/OwnershipResolution.md`:

| Ownership State | Platform Behavior | Prohibited Behavior |
|---|---|---|
| `Resolved` | Normal lifecycle, retention, archive, Legal Hold, and cleanup operations may proceed within validated ownership, tenant scope, authorization, and policy | Acting outside the resolved owner or tenant boundary |
| `Partially Resolved` | Only operations explicitly permitted by an approved domain policy may proceed; known ownership dimensions and the reason for unresolved dimensions remain preserved and auditable | Inferring missing ownership, widening scope, or treating partial context as fully authorized |
| `Unresolved` | The decision or operation is rejected or deferred according to approved domain policy until sufficient ownership context exists | Fabricating tenant identifiers, fabricating owner identifiers, deriving authorization implicitly, or executing tenant-scoped destructive action |

Ownership rules:

1. Ownership resolution is independent from Data Category, Field Classification, Exposure Classification, and Lifecycle Semantics.
2. Every tenant-scoped persistent object normally requires `Resolved` ownership unless an approved Decision documents a legitimate pre-resolution, inbound-event, import, security-event, or equivalent exception.
3. `Partially Resolved` and `Unresolved` states preserve all known evidence without inventing missing identifiers.
4. No `Partially Resolved` or `Unresolved` state grants authorization or cross-tenant access.
5. A transition toward `Resolved` ownership must be deterministic, authorized, and evidenced by an Immutable Audit Event when materially significant.
6. Archive, purge, and cryptographic destruction require the ownership scope needed by the approved policy; unresolved scope cannot be bypassed by Execution Authority.
7. Domains define their allowed ownership exceptions and operational responses without weakening these platform boundaries.

## Cross-domain Applicability

| Domain | Platform Policy Application | Domain-owned Detail |
|---|---|---|
| Authentication | Evidence, operational history, security state, and credential lifecycle | Concrete objects, transitions, durations, API behavior, tests |
| Patient | Identity, demographics, consent, access history, and change evidence | Privacy basis, lifecycle, durations, erasure behavior |
| Appointment | Scheduling state, cancellation/no-show history, and evidence | Transitions, durations, API behavior |
| EMR | Clinical records, amendments, access evidence, archive, and Legal Hold | Medical policy, amendment rules, jurisdictional retention |
| Finance | Invoice/payment state, ledgers, reversals, and evidence | Financial finality, durations, regulatory controls |
| Inventory | Stock movement evidence, balance projections, and corrections | Ledger, correction, duration, and reconciliation rules |
| HR | Employment state, attendance/payroll history, and access evidence | Employment policy, durations, privacy controls |
| CRM | Interaction state, consent history, and privacy lifecycle | Consent, campaign retention, erasure assessment |
| AI | Inputs, outputs, provenance, safety decisions, and evidence | Model retention, redaction, explainability, safety controls |
| Integration Hub | Delivery state, retries, payload lifecycle, and provider evidence | Contract payload retention, replay, provider obligations |

Platform policy owns categories, lifecycle vocabulary, audit boundaries, retention principles, archive governance, Legal Hold, and cleanup authority. Domain artifacts own concrete objects, business transitions, durations, schemas, APIs, and tests.

## Exceptions

1. Business Records use `Soft Deletable` by default.
2. A Business Record may use stricter semantics when required by an Accepted domain Decision, Accepted ADR, or legal obligation.
3. Platform Security Data may use `Revocable`, `Expiring`, or `Hard Deletable` after approved retention and Legal Hold requirements are satisfied.
4. Immutable Audit Events are not ordinary Business Records and do not inherit `Soft Deletable`.
5. An exception requires rationale, authority, category assignment, lifecycle mapping, retention policy, audit evidence, and traceability.
6. No exception may expose, audit, or archive Secret data.
7. Exceptions operate only through synchronized repository governance and do not directly override `AGENTS.md`.

## Dependencies

- `AGENTS.md` remains repository governance authority.
- `docs/Architecture/Standards/index.md` and its registered standards remain canonical vocabulary and review authority.
- `docs/Authentication/Decision/DD-AUTH-007.md` is the Accepted source domain decision; the originating dependency is satisfied.
- ADR-001 through ADR-003 remain Accepted and authoritative within their scopes. ADR-004 is Superseded by ADR-006 and remains historical evidence.
- ADR acceptance is complete. Implementation and Design Freeze remain blocked until affected artifacts pass synchronization, Full Drift Detection, and final Architecture Review.

### Repository Governance Synchronization Boundary

- Acceptance of ADR-005 MUST NOT directly modify `AGENTS.md`.
- `AGENTS.md` remains the repository governance authority; the separate governance synchronization activity for ADR-005 is complete.
- Repository governance synchronization was a distinct post-acceptance SDLC activity with its own explicitly approved scope, governance review, and gate result.
- Acceptance of ADR-005 does not imply or perform synchronization of `AGENTS.md`, Global Architecture Standards, or downstream artifacts.
- Future changes to repository-governance artifacts require a separate authorized synchronization activity.

```text
Global Architecture Standards
        -> DD-AUTH-007 Accepted
        -> ADR-005 Accepted
        -> Separately approved repository governance synchronization
        -> Domain synchronization and domain Decisions
        -> Full Drift Detection
        -> Domain Design Freeze
```

There is no dependency from `DD-AUTH-007` back to ADR-005. ADR-005 does not supersede or modify `DD-AUTH-007`.

## Related ADRs and Decisions

- `docs/Authentication/Decision/DD-AUTH-007.md`: Authentication-specific implementation decision; referenced, not superseded.
- `docs/ADR/ADR-001-Authentication-Lockout.md`: transient lockout authority remains unchanged.
- `docs/ADR/ADR-002-Authentication-Token.md`: token and Session authority remains unchanged.
- `docs/ADR/ADR-003-Password-Reset.md`: password-reset authority remains unchanged.
- `docs/ADR/ADR-004-Authentication-Audit.md`: Superseded historical Authentication audit strategy.
- `docs/ADR/ADR-006-Authentication-Audit-Evidence-and-History-Projection.md`: Accepted active authority distinguishing canonical Audit Events from the Login History Operational History Projection.

ADR-005 supersedes no ADR or Decision. A material future change requires a new sequential ADR that explicitly supersedes ADR-005.

## Traceability

- Source domain decision: `docs/Authentication/Decision/DD-AUTH-007.md` (`Accepted`; originating dependency satisfied).
- Global standards: `docs/Architecture/Standards/index.md` and all standards registered there.
- Related active ADRs: ADR-001, ADR-002, ADR-003, ADR-005, and ADR-006. ADR-004 is retained as Superseded historical evidence.
- Affected domains: Authentication, Patient, Appointment, EMR, Finance, Inventory, HR, CRM, AI, and Integration Hub.
- Planned downstream mappings: domain Requirements, Business Rules, Decisions/ADRs, Database Designs, ERDs, APIs/OpenAPI where applicable, implementation, and tests.
- Planned tests: category exclusivity, lifecycle mutation restrictions, audit immutability, retention eligibility, archive integrity/restoration, Legal Hold authorization/blocking, cleanup authorization, tenant isolation, and secret exclusion.
- Downstream design synchronization is governed by separate SDLC gates. Implementation and tests remain `PLANNED` and are not inferred complete or authorized by ADR acceptance alone.

## Review Status

- Architecture Review: PASS (`STEP_05_18_2_PLATFORM_ADR_ARCHITECTURE_REVIEW_PASS`).
- Security Review: PASS (`STEP_05_18_3_PLATFORM_ADR_SECURITY_REVIEW_PASS`).
- Data Review: PASS (`STEP_05_18_4_PLATFORM_ADR_DATA_REVIEW_PASS`).
- API Review: PASS (`STEP_05_18_5_PLATFORM_ADR_API_REVIEW_PASS`).
- Performance Review: PASS (`STEP_05_18_6_PLATFORM_ADR_PERFORMANCE_REVIEW_PASS`).
- Compliance Review: PASS (`STEP_05_18_7_PLATFORM_ADR_COMPLIANCE_REVIEW_PASS`).
- Platform Review: PASS (`STEP_05_18_8_PLATFORM_ADR_PLATFORM_REVIEW_PASS`).
- Final Quality Gate: PASS (`PLATFORM_ADR_FINAL_QUALITY_GATE_PASS`).
- Final Review Status: Accepted.
- Implementation Status: Not started.

## Post-Acceptance Governance Note

- ADR-005 Status: Accepted.
- DD-AUTH-007 Status: Accepted; originating Authentication lifecycle authority.
- DD-AUTH-017 Status: Accepted; active Authentication field-policy authority.
- DD-AUTH-005 Status: Superseded by DD-AUTH-017 and retained only as historical evidence.
- Platform Lifecycle Authority: ADR-005.
- Repository governance synchronization for ADR-005: completed as a separate SDLC activity.
- Implementation remains gated by Full Drift Detection, final Architecture Review, and Design Freeze.
- ADR-004 Status: Superseded by ADR-006.
- ADR-006 Status: Accepted; active Authentication audit-evidence and Login History projection authority.
