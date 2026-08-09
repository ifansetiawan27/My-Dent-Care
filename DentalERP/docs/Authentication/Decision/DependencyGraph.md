# Authentication Design Decision Dependency Graph

## Critical Path to Design Freeze

```text
DD-AUTH-001 (ACCEPTED)
Access Token <-> Device Linkage
        |
        v
DD-AUTH-004 (ACCEPTED)
Change Password Session Behavior
        |
        v
DD-AUTH-018 (ACCEPTED)
Credential-Change Projection Precedence
        |
        v
Sequence Diagram
        |
        v
Flowchart
        |
        v
Architecture Review
        |
        v
Drift Detection PASS
        |
        v
Design Freeze
```

## Dependency Rules

### DD-AUTH-001 -> DD-AUTH-004

DD-AUTH-004 requires a deterministic way to identify:

- The current Access Token.
- The current Device/session.
- Every Access Token owned by other Devices.
- Every Refresh Token owned by current and other Devices.

Therefore, DD-AUTH-004 cannot be Accepted until DD-AUTH-001 is Accepted and propagated to ERD/token architecture.

### DD-AUTH-008 Independent Track

DD-AUTH-008 selected removal of `remember_me`. It no longer affects Refresh Token TTL, Device trust, or post-password-change Session continuity and is independent from DD-AUTH-004.

```text
DD-AUTH-008 (ACCEPTED — INDEPENDENT)
Remember Me Behavior
```

### DD-AUTH-004 / DD-AUTH-007 / DD-AUTH-017 -> DD-AUTH-018

- DD-AUTH-004 owns Password Change Session behavior.
- DD-AUTH-007 owns generic Authentication lifecycle behavior.
- DD-AUTH-017 owns field governance for `login_histories.logout_at`.
- DD-AUTH-018 resolves their trigger-precedence conflict without superseding them.
- DD-AUTH-018 is the specific Accepted authority: credential-change revocation does not mutate Login History or `logout_at`.

### Accepted Decisions -> Sequence Diagram and Flowchart

The final Login, Refresh, Change Password, Logout, and Device lifecycle diagrams depend on:

- Token-to-Device linkage.
- Current/other session revocation behavior.
- Trusted Device and `remember_me` behavior.

Sequence Diagram and Flowchart follow DD-AUTH-001, DD-AUTH-004, DD-AUTH-008, and DD-AUTH-018 after acceptance.

## Parallel Decision Tracks

The critical path does not replace the other Design Freeze blockers. These decisions may be reviewed in parallel but must also PASS before Design Freeze:

```text
DD-AUTH-002  Password Hashing Strategy
DD-AUTH-003  Super Admin Session Scope
DD-AUTH-017  Field Classification and Nullability (ACCEPTED)
DD-AUTH-006  Device Pagination
DD-AUTH-007  Audit and Operational Data Lifecycle Strategy (ACCEPTED)
DD-AUTH-010  Login History Index Strategy
ADR-005      Platform Lifecycle and Audit Policy (ACCEPTED)
ADR-006      Authentication Audit Evidence and Login History Projection Authority (ACCEPTED)
DD-AUTH-018  Credential-Change Projection Precedence (ACCEPTED)
```

## DD-AUTH-007 / ADR-005 -> DD-AUTH-017

- DD-AUTH-007 is the Accepted Authentication-specific lifecycle and audit source decision.
- ADR-005 is the Accepted platform lifecycle and audit authority.
- Repository governance synchronization for ADR-005 is complete.
- DD-AUTH-017 depends one-way on DD-AUTH-007 and ADR-005; neither Accepted record depends on DD-AUTH-017.
- DD-AUTH-017 is Accepted and is the active field-policy authority.
- DD-AUTH-005 is Superseded by DD-AUTH-017 and remains immutable historical decision evidence.
- ADR-004 is Superseded by ADR-006; ADR-006 is the active audit-evidence and Login History projection authority.
- There is no circular dependency.

## Design Freeze Gate

Design Freeze requires:

1. Critical path DD-AUTH-001 -> DD-AUTH-004 -> DD-AUTH-018 Accepted; DD-AUTH-008 Accepted independently.
2. DD-AUTH-002, 003, 006, 007, 010, 017, and 018 Accepted; ADR-005 and ADR-006 Accepted; DD-AUTH-005 Superseded by DD-AUTH-017; ADR-004 Superseded by ADR-006.
3. Field-policy downstream artifacts synchronized to DD-AUTH-017.
4. All affected documents synchronized.
5. Sequence Diagram completed.
6. Flowchart completed.
7. Architecture Review PASS.
8. Full Drift Detection PASS.
9. No runtime implementation artifacts created before Stage 06 restarts.
