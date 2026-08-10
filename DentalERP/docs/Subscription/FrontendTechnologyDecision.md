# DECISION-013 — DentalERP Frontend SPA Technology

**Date:** 2026-08-11
**Category:** Frontend Architecture
**Status:** **RECOMMENDED** — Awaiting Approval

---

## Problem

DentalERP requires a separate frontend SPA to consume its REST APIs. The backend (Laravel 12, Sanctum, PostgreSQL, Redis) is complete. No frontend framework has been selected.

## Options

| Criteria | Vue 3 + TypeScript | React + TypeScript |
|---|---|---|
| Laravel ecosystem pairing | ✅ Traditional default | ✅ Fully compatible via REST |
| TypeScript inference | ✅ Good | ✅ Excellent (JSX generics) |
| Interactive dental UI (odontogram, tooth charts) | ✅ Composition API | ✅ Superior canvas/ref patterns |
| Large enterprise forms | ✅ | ✅ |
| Component ecosystem | ✅ Vuetify, PrimeVue | ✅ MUI, Ant Design, Radix |
| State management | ✅ Pinia | ✅ Zustand / TanStack Query |
| DICOM/PACS integration | ✅ | ✅ Superior viewer ecosystem (OHIF, Cornerstone) |
| Code splitting | ✅ | ✅ |
| Testing (Vitest / Jest) | ✅ | ✅ |
| Laravel Sanctum compatibility | ✅ | ✅ |
| Developer pool (Indonesia) | ✅ Large | ✅ Largest |
| Learning curve | ✅ Lower | ⚠️ Higher initially |
| SFC (Single File Components) | ✅ `.vue` files | ✅ JSX/TSX |
| Accessibility | ✅ | ✅ Radix/Headless UI |

## Recommendation

**React + TypeScript**

### Rationale

1. **Clinical UI requirements**: DentalERP's odontogram, tooth charts, intraoral image viewers, and future DICOM/PACS integration require canvas-based interactive components. React's ref pattern and canvas manipulation ecosystem (Fabric.js, Konva, Cornerstone.js/DICOM) are stronger and more established.

2. **DICOM/PACS ecosystem**: OHIF Viewer (Open Health Imaging Foundation) is React-based. If DentalERP integrates DICOM imaging, a React frontend eliminates framework bridging complexity.

3. **Enterprise form complexity**: React Hook Form + Zod provides the most mature TypeScript-first form validation stack for the large clinical forms DentalERP requires.

4. **State management**: TanStack Query provides superior server-state caching and invalidation for REST APIs — critical for subscription/payment state that must not go stale.

5. **Developer ecosystem**: React has the largest TypeScript component ecosystem in Indonesia, making hiring and onboarding faster.

6. **Long-term scalability**: React's functional component model scales well from simple CRUD to complex interactive clinical UIs without paradigm shifts.

## Consequences

| Positive | Negative |
|---|---|
| Superior DICOM/PACS integration path | Higher initial learning curve vs Vue |
| Largest TypeScript component ecosystem | No built-in Laravel-first integration (but REST API resolves this) |
| TanStack Query for server-state | Requires separate SPA build pipeline |
| OHIF compatibility for dental imaging | More tooling configuration initially |

## Recommended Stack

| Layer | Technology |
|---|---|
| Framework | React 19 + TypeScript 5 |
| Build | Vite |
| Routing | React Router |
| State (Server) | TanStack Query |
| State (Client) | Zustand |
| Forms | React Hook Form + Zod |
| UI Components | Radix UI (headless) + Tailwind CSS |
| Tables | TanStack Table |
| Charts | Recharts |
| HTTP Client | Axios |
| Testing | Vitest + React Testing Library + Playwright |
| Auth | Sanctum token-based (backend) |
| Icons | Lucide React |

## Architecture

```
frontend/                         (separate repository)
├── src/
│   ├── modules/
│   │   ├── auth/                 Login, register
│   │   ├── subscription/         Plans, status, trial, billing
│   │   ├── payment/              History, checkout
│   │   ├── settings/             Clinic, branding, invoice
│   │   ├── patient/              Patient CRUD
│   │   ├── appointment/          Scheduling
│   │   ├── emr/                  Clinical records
│   │   ├── odontogram/           Tooth charts
│   │   ├── treatment/            Procedures
│   │   ├── billing/              Invoicing
│   │   ├── inventory/            Stock
│   │   ├── pharmacy/             Drugs
│   │   ├── laboratory/           Lab orders
│   │   └── reporting/            Analytics
│   ├── shared/                   Components, hooks, utils
│   └── core/                     API client, auth, guards
├── package.json
└── vite.config.ts
```

## Rejected Alternative

**Vue 3 + TypeScript** was rejected primarily due to:
- Smaller DICOM/PACS ecosystem (OHIF is React-only)
- Less mature canvas/interactive graphics component patterns for clinical UIs
- Smaller enterprise component library ecosystem compared to React

Vue 3 remains an excellent framework and would be fully capable for most DentalERP features. The decision is based on future clinical imaging requirements, not current capability gaps.

---

**Files Modified:** 0 — Decision record only.
**Frozen Artifacts:** 0 modifications.
**Implementation:** Not started — governance decision only.

STEP_28_42_SAAS_FRONTEND_SPA_TECHNOLOGY_DECISION — **DECISION REQUIRED** (awaiting Product Owner approval for DECISION-013)
