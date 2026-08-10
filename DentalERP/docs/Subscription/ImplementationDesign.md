# STEP_28_28 — Subscription Lifecycle Implementation Design

**Date:** 2026-08-10
**Phase:** SaaS / Subscription
**SDLC Stage:** Design
**Status:** `DESIGN — NOT YET IMPLEMENTED`

**Governance Baseline:** DECISION-001 through DECISION-012 (all ACCEPTED)

---

## 1. Architecture Overview

```
┌────────────────────────────────────────────────┐
│               HTTP / API Layer                  │
│  SubscriptionController, WebhookController     │
├────────────────────────────────────────────────┤
│              Domain Services                    │
│  SubscriptionTransitionService                 │
│  SubscriptionService                            │
│  IdempotencyService                             │
├────────────────────────────────────────────────┤
│              Payment Gateway                    │
│  PaymentGatewayInterface (Phase 07)            │
│  → MidtransDriver (future)                     │
├────────────────────────────────────────────────┤
│              Scheduled Jobs                     │
│  ProcessTrialExpiration                         │
│  ProcessSubscriptionRenewals                    │
│  RetryFailedPayment                             │
│  ProcessGraceExpiration                         │
├────────────────────────────────────────────────┤
│              Persistence                        │
│  subscriptions                                  │
│  subscription_transitions (audit)              │
│  payment_transactions                           │
│  idempotency_records                            │
└────────────────────────────────────────────────┘
```

## 2. Domain Boundary — `app/Domains/Subscription/`

```
app/Domains/Subscription/
├── Models/
│   ├── Subscription.php
│   └── SubscriptionTransition.php
├── Enums/
│   ├── SubscriptionStatus.php
│   └── SubscriptionTrigger.php
├── Services/
│   ├── SubscriptionService.php
│   └── SubscriptionTransitionService.php
├── Repositories/
│   ├── SubscriptionRepository.php
│   └── SubscriptionTransitionRepository.php
├── Jobs/
│   ├── ProcessTrialExpiration.php
│   ├── ProcessSubscriptionRenewals.php
│   ├── RetryFailedPayment.php
│   └── ProcessGraceExpiration.php
├── Events/
│   ├── SubscriptionActivated.php
│   ├── SubscriptionPaymentFailed.php
│   ├── SubscriptionEnteredGrace.php
│   ├── SubscriptionExpired.php
│   └── SubscriptionReactivated.php
├── Policies/
│   └── SubscriptionPolicy.php
├── Exceptions/
│   ├── InvalidTransitionException.php
│   └── DuplicateEventException.php
├── Migrations/
│   ├── create_subscriptions_table.php
│   ├── create_subscription_transitions_table.php
│   ├── create_payment_transactions_table.php
│   └── create_idempotency_records_table.php
├── Providers/
│   └── SubscriptionServiceProvider.php
├── Routes/
│   └── api.php
└── Http/
    └── Controllers/
        ├── SubscriptionController.php
        └── PaymentWebhookController.php
```

## 3. Database Design

### `subscriptions`

| Column | Type | Nullable | Description |
|---|---|---|---|
| `id` | `uuid` PK | NOT NULL | Ordered UUID |
| `organization_id` | `uuid` FK | NOT NULL | → organizations.id RESTRICT |
| `plan_code` | `varchar(30)` | NOT NULL | `starter`/`professional`/`enterprise` |
| `status` | `varchar(20)` | NOT NULL | `SubscriptionStatus` enum |
| `trial_starts_at` | `timestamptz` | NULL | Trial start |
| `trial_ends_at` | `timestamptz` | NULL | Trial end |
| `current_period_starts_at` | `timestamptz` | NULL | Billing period start |
| `current_period_ends_at` | `timestamptz` | NULL | Billing period end |
| `grace_starts_at` | `timestamptz` | NULL | Grace start |
| `grace_ends_at` | `timestamptz` | NULL | Grace end |
| `cancelled_at` | `timestamptz` | NULL | Cancellation timestamp |
| `reactivated_at` | `timestamptz` | NULL | Last reactivation |
| `created_at` | `timestamptz` | NOT NULL | |
| `updated_at` | `timestamptz` | NOT NULL | |
| `deleted_at` | `timestamptz` | NULL | Soft delete |

**Indexes:** `(organization_id)` UNIQUE (one active subscription per org), `(status)`, `(trial_ends_at)` for expiration job, `(current_period_ends_at)` for renewal job.

### `subscription_transitions`

| Column | Type | Nullable | Description |
|---|---|---|---|
| `id` | `uuid` PK | NOT NULL | |
| `subscription_id` | `uuid` FK | NOT NULL | → subscriptions.id |
| `organization_id` | `uuid` | NOT NULL | Denormalized for tenant queries |
| `previous_state` | `varchar(20)` | NOT NULL | |
| `new_state` | `varchar(20)` | NOT NULL | |
| `trigger` | `varchar(50)` | NOT NULL | `SubscriptionTrigger` |
| `actor_type` | `varchar(10)` | NOT NULL | `user` or `system` |
| `actor_id` | `uuid` | NULL | User UUID (null when system) |
| `metadata` | `jsonb` | NULL | Payment ref, reason, etc. |
| `idempotency_key` | `varchar(100)` | NULL | For duplicate prevention |
| `created_at` | `timestamptz` | NOT NULL | |

**Indexes:** `(subscription_id, created_at)`, `(organization_id, created_at)`, `(idempotency_key)` UNIQUE WHERE NOT NULL.

### `payment_transactions`

| Column | Type | Nullable | Description |
|---|---|---|---|
| `id` | `uuid` PK | NOT NULL | |
| `organization_id` | `uuid` FK | NOT NULL | |
| `subscription_id` | `uuid` FK | NOT NULL | |
| `provider` | `varchar(20)` | NOT NULL | `midtrans` |
| `provider_transaction_id` | `varchar(100)` | NULL | External reference |
| `amount` | `bigint` | NOT NULL | Smallest currency unit (IDR) |
| `currency` | `varchar(3)` | NOT NULL | `IDR` |
| `status` | `varchar(20)` | NOT NULL | `PaymentTransactionStatus` |
| `payment_method` | `varchar(30)` | NULL | qris/va/card/ewallet |
| `gateway_fee` | `bigint` | NULL | Internal cost |
| `raw_response` | `jsonb` | NULL | Provider response |
| `created_at` | `timestamptz` | NOT NULL | |

### `idempotency_records`

| Column | Type | Nullable | Description |
|---|---|---|---|
| `id` | `uuid` PK | NOT NULL | |
| `key` | `varchar(100)` | NOT NULL | UNIQUE — webhook event ID or job fingerprint |
| `entity_type` | `varchar(50)` | NOT NULL | `payment_transaction`, `subscription_transition` |
| `entity_id` | `uuid` | NOT NULL | |
| `processed_at` | `timestamptz` | NOT NULL | |
| `response` | `jsonb` | NULL | Cached response |

## 4. State Machine — Transition Matrix

```php
// Allowed transitions
SubscriptionStatus::Trial → [Active, Expired]
SubscriptionStatus::Active → [Active, PastDue, Cancelled]
SubscriptionStatus::PastDue → [Active, Grace]
SubscriptionStatus::Grace → [Active, Expired]
SubscriptionStatus::Expired → [Active]
SubscriptionStatus::Cancelled → [Active]

// Forbidden (throws InvalidTransitionException)
Trial → PastDue, Trial → Grace, Expired → Grace, Expired → Trial, Cancelled → Grace
```

## 5. Scheduled Jobs

| Job | Schedule | Query | Action |
|---|---|---|---|
| `ProcessTrialExpiration` | Every hour | `WHERE status='trial' AND trial_ends_at <= now()` | `TransitionService::transition(id, TRIAL_EXPIRED, SYSTEM)` |
| `ProcessSubscriptionRenewals` | Daily at 00:00 | `WHERE status='active' AND current_period_ends_at <= now()` | Initiate payment → success/failure |
| `RetryFailedPayment` | Daily at 00:00 | `WHERE status='past_due' AND age >= 3 days` | Retry payment → success → grace |
| `ProcessGraceExpiration` | Every hour | `WHERE status='grace' AND grace_ends_at <= now()` | `TransitionService::transition(id, GRACE_EXPIRED, SYSTEM)` |

## 6. Webhook Flow

```
Midtrans → POST /api/v1/webhooks/payment/midtrans
  → MidtransWebhookController (Phase 07 contracts)
  → verifySignature(payload)
  → IdempotencyService::check(order_id)
    ├─ Already processed → return 200 (safe)
    └─ New → PaymentTransaction::create()
           → SubscriptionTransitionService::transition()
              ├─ Success → 200
              └─ Invalid → log, return 200 (don't retry bad webhooks)
```

## 7. Implementation Readiness

**READY.** All 12 governance decisions accepted. Design complete. Phase 07 PaymentGateway contracts exist for reuse. No blocking conflicts with existing infrastructure.

---

**Files Modified:** 0 — Design document only.

STEP_28_28_SUBSCRIPTION_LIFECYCLE_IMPLEMENTATION_DESIGN — **DESIGN COMPLETE** (awaiting approval to proceed to implementation)
