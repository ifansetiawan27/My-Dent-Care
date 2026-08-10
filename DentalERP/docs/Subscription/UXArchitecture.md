# STEP_28_40 — SaaS Frontend UX Architecture Design

**Date:** 2026-08-11
**Status:** DESIGN — NOT YET IMPLEMENTED
**Frontend Framework:** Not yet selected (API-backend only)

---

## 1. Architecture Overview

```
┌─────────────────────────────────────────────┐
│           Frontend Application               │
│  (Vue/React/Next — TBD)                     │
├─────────────────────────────────────────────┤
│  API Client Layer                            │
│  /api/v1/subscription|settings|webhooks     │
├─────────────────────────────────────────────┤
│  DentalERP Backend (Laravel 12)              │
│  Sanctum Auth | Policies | Entitlements      │
└─────────────────────────────────────────────┘
```

**Principle:** Backend is authoritative. Frontend is presentation only.

---

## 2. Navigation Architecture

```
Dashboard
├── Clinical
│   ├── Patients
│   ├── Appointments
│   ├── EMR
│   ├── Odontogram
│   └── Treatment
├── Operations
│   ├── Billing
│   ├── Inventory
│   ├── Pharmacy
│   ├── Laboratory
│   └── ...
├── Analytics
│   └── Reporting
├── Administration
│   ├── Subscription     ← SaaS module
│   ├── Billing          ← SaaS module
│   ├── Payment History  ← SaaS module
│   └── Settings         ← Clinic + Billing settings
└── User Menu
    ├── Profile
    └── Logout
```

---

## 3. API → Screen Mapping

| Screen | API Endpoint | Method |
|---|---|---|
| Subscription Overview | `GET /v1/subscription` | Shows plan, status, trial, billing, storage |
| Plans Comparison | `GET /v1/subscription/plans` | Shows Starter/Professional/Enterprise |
| Cancel Subscription | `POST /v1/subscription/cancel` | Cancels subscription |
| Payment History | `GET /v1/payment-transactions` (future) | Lists past payments |
| Settings Overview | `GET /v1/settings` | Clinic + Invoice + Billing + Subscription |
| Update Settings | `PUT /v1/settings` | Update clinic profile, invoice, billing |
| Webhook (backend) | `POST /webhooks/midtrans` | Payment confirmation |

---

## 4. Screen Designs

### 4.1 Subscription Overview

```
┌─────────────────────────────────────────────────┐
│  Subscription Overview                           │
├─────────────────────────────────────────────────┤
│  Current Plan: Professional    Rp 399.000/month  │
│  Status: ACTIVE ●                               │
│  Billing Period: 10 Aug — 10 Sep 2026            │
│  Next Billing: 10 Sep 2026                       │
│                                                   │
│  Storage: 12 GB / 50 GB  ████░░░░░░  24%         │
│  Users: Unlimited                                │
│  Clinical Records: Unlimited                     │
│                                                   │
│  [Upgrade Plan]  [Cancel Subscription]           │
└─────────────────────────────────────────────────┘
```

### 4.2 Trial (with days remaining)

```
┌─────────────────────────────────────────────────┐
│  ⚡ Trial Active — 22 days remaining              │
│  Plan: Starter                                   │
│  Trial ends: 2 Sep 2026                          │
│  Full access during trial                        │
│                                                   │
│  [Choose Plan]  [Start Payment]                  │
└─────────────────────────────────────────────────┘
```

### 4.3 Past Due / Grace

```
┌─────────────────────────────────────────────────┐
│  ⚠ Payment Past Due                              │
│  Amount: Rp 399.000                              │
│  Due: 10 Aug 2026                                │
│  Grace period: 4 days remaining                  │
│  Full access maintained during grace             │
│                                                   │
│  [Pay Now]  [Update Payment Method]              │
└─────────────────────────────────────────────────┘
```

### 4.4 Expired

```
┌─────────────────────────────────────────────────┐
│  ❌ Subscription Expired                         │
│  Plan: Professional                              │
│  Expired: 17 Aug 2026                            │
│                                                   │
│  Clinical modules are restricted.                │
│  Your data is preserved and will be restored     │
│  upon reactivation.                              │
│                                                   │
│  [Reactivate — Rp 399.000/month]                 │
│  [View Payment History]                           │
│  [Export Data]                                   │
└─────────────────────────────────────────────────┘
```

### 4.5 Settings — Clinic Profile

```
┌─────────────────────────────────────────────────┐
│  Settings  >  Clinic Profile                      │
├─────────────────────────────────────────────────┤
│  Clinic Name: [Klinik Sehat___________________]   │
│  Legal Name:  [PT Klinik Sehat Indonesia______]   │
│  Phone:       [021-12345678___________________]   │
│  Email:       [admin@kliniksehat.id___________]   │
│  Website:     [https://kliniksehat.id_________]   │
│                                                   │
│  Address:     [Jl. Sudirman No. 10___________]   │
│  City:        [Jakarta Selatan_______________]   │
│  Province:    [DKI Jakarta___________________]   │
│  Postal Code: [12190_________________________]   │
│                                                   │
│  [Save Changes]                                   │
└─────────────────────────────────────────────────┘
```

### 4.6 Settings — Branding

```
┌─────────────────────────────────────────────────┐
│  Settings  >  Branding                            │
├─────────────────────────────────────────────────┤
│  Clinic Logo:  [🖼 preview]  [Upload] [Remove]    │
│  Max size: 2 MB. Format: PNG, JPG, SVG           │
│                                                   │
│  Invoice Logo: [🖼 preview]  [Upload] [Remove]    │
│  Max size: 2 MB. Format: PNG, JPG                │
│                                                   │
│  [Save]                                           │
└─────────────────────────────────────────────────┘
```

### 4.7 Settings — Invoice

```
┌─────────────────────────────────────────────────┐
│  Settings  >  Invoice                             │
├─────────────────────────────────────────────────┤
│  Invoice Prefix:  [INV/________________________]  │
│  Invoice Footer:  [Terima kasih_______________]   │
│                                                   │
│  Billing Name:    [PT Klinik Sehat Indonesia__]   │
│  Billing Email:   [billing@kliniksehat.id_____]   │
│  Billing Phone:   [021-12345678_______________]   │
│  Billing Address: [Jl. Sudirman No. 10_______]   │
│                                                   │
│  [Save]                                           │
└─────────────────────────────────────────────────┘
```

---

## 5. Access Control Matrix (UX)

| State | Clinical Nav | Billing Nav | Settings Nav | Subscription Nav |
|---|---|---|---|---|
| TRIAL | Visible | Visible | Visible | Visible |
| ACTIVE | Visible | Visible | Visible | Visible |
| PAST_DUE | Visible | Visible | Visible | Visible |
| GRACE | Visible | Visible | Visible | Visible |
| EXPIRED | Disabled* | Visible | Visible | Visible |
| CANCELLED | Disabled* | Visible | Visible | Visible |

*Disabled = grayed out with tooltip: "Subscription required"

---

## 6. Global Subscription Banner

| State | Banner |
|---|---|
| TRIAL | "Trial ends in X days — [Choose Plan]" |
| PAST_DUE | "Payment failed — [Pay Now]" |
| GRACE | "Payment overdue — X days remaining — [Pay Now]" |
| EXPIRED | "Subscription expired — [Reactivate]" |

---

## 7. State Management

```
API Response → Normalize → Store → Components consume

After payment/webhook:
  1. Refresh subscription API
  2. Update store
  3. Re-render affected components
```

No local state overriding server state.

---

## 8. Security Rules

| Rule | Enforcement |
|---|---|
| Never store Midtrans credentials in frontend | Code review |
| Never trust client-side subscription status | Backend entitlement enforcement |
| Never calculate prices client-side | API provides all pricing |
| Never expose server keys in API responses | Backend field exclusion |

---

## 9. Implementation Readiness

**Not yet implemented.** Backend APIs (STEP_28_35–38) are ready. Frontend screens are designed. Awaiting frontend framework selection and implementation authorization.

**Files modified:** 0 — Design document only.

STEP_28_40_SAAS_FRONTEND_UX_ARCHITECTURE_DESIGN_PASS
