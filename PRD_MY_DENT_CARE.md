# Product Requirements Document (PRD)
# My Dent Care - Dental Clinic ERP Platform

**Version:** 1.0  
**Date:** 23 August 2026  
**Status:** Production Deployment Phase  
**Author:** Development Team

---

## Executive Summary

My Dent Care adalah platform Enterprise Resource Planning (ERP) yang dirancang khusus untuk manajemen klinik gigi multi-cabang. Platform ini dibangun dengan arsitektur enterprise-grade menggunakan Domain Driven Design (DDD) dan prinsip SOLID untuk memastikan skalabilitas, maintainability, dan extensibility.

### Key Highlights
- **Target:** Klinik gigi single-location hingga multi-branch (1-100+ cabang)
- **Architecture:** Domain Driven Design (DDD) + SOLID Principles
- **Tech Stack:** Laravel 12 (PHP 8.4), PostgreSQL, Redis, Docker
- **Deployment:** AWS EC2 (Backend) + Vercel (Frontend)
- **API Standard:** RESTful API dengan OpenAPI 3.1 specification
- **Security:** Laravel Sanctum Authentication, Row Level Security (RLS)

---

## 1. Product Vision & Goals

### 1.1 Vision Statement
Menjadi platform ERP dental clinic terbaik di Indonesia yang dapat digunakan oleh klinik single-location maupun jaringan klinik multi-cabang dengan fitur integrasi SATUSEHAT dan BPJS.

### 1.2 Product Goals
1. **Skalabilitas:** Mendukung pertumbuhan dari 1 klinik hingga 100+ cabang tanpa perubahan arsitektur
2. **Maintainability:** Junior developer dapat memahami dan maintain codebase dengan mudah
3. **Extensibility:** Domain baru dapat ditambahkan tanpa mengubah kode existing
4. **Integration Ready:** API dapat dikonsumsi oleh mobile apps, third-party, SATUSEHAT, dan BPJS
5. **Quality Assurance:** Setiap bug tertangkap oleh automated tests sebelum production

### 1.3 Success Metrics
- **Technical:**
  - 95%+ uptime
  - <200ms API response time (p95)
  - 80%+ test coverage
  - Zero critical security vulnerabilities
  
- **Business:**
  - Support 50+ dental clinics dalam 12 bulan pertama
  - 90%+ customer satisfaction score
  - <5% monthly churn rate

---

## 2. Technology Stack & Architecture

### 2.1 Backend Technology

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| Framework | Laravel | 12.x | Backend application framework |
| Language | PHP | 8.4 | Programming language |
| Database | PostgreSQL | 16 | Primary data store |
| Cache/Queue | Redis | 7.x | Caching & job queue |
| Container | Docker | Latest | Containerization |
| Web Server | PHP Built-in / Nginx | - | HTTP server |

### 2.2 Frontend Technology

| Component | Technology | Purpose |
|-----------|-----------|---------|
| Framework | React 18 | UI library |
| Build Tool | Vite | Frontend build tool |
| Hosting | Vercel | Frontend deployment |
| State Management | TBD | Global state management |

### 2.3 Infrastructure

| Component | Technology | Configuration |
|-----------|-----------|---------------|
| Backend Server | AWS EC2 | Ubuntu 24.04 LTS |
| IP Address | Public | 108.136.48.83 |
| Port | 8080 | Backend API |
| Database | PostgreSQL (Local) | 172.31.20.84:5432 |
| Cache | Redis (Local) | 172.31.20.84:6379 |
| Frontend | Vercel | HTTPS with CDN |

### 2.4 Architectural Patterns

#### Domain Driven Design (DDD)
```
app/
├── Core/                 # Shared infrastructure
│   ├── Enums/           # Cross-domain enums
│   ├── Traits/          # Reusable traits
│   └── Exceptions/      # Base exceptions
├── Domains/             # Business domains
│   ├── Organization/    # Organization & Branch management
│   ├── Patient/         # Patient management
│   ├── Appointment/     # Appointment scheduling
│   ├── Treatment/       # Treatment records
│   ├── Inventory/       # Inventory management
│   ├── Finance/         # Financial transactions
│   ├── HumanResource/   # HR management
│   └── Authentication/  # User auth & permissions
└── Platform/            # Platform infrastructure
```

#### SOLID Principles Implementation
- **Single Responsibility:** Each class has one reason to change
- **Open/Closed:** New features added via new classes, not modifications
- **Liskov Substitution:** Implementations replaceable via interfaces
- **Interface Segregation:** Domain-specific, focused interfaces
- **Dependency Inversion:** Dependencies on interfaces, not concrete classes

---

## 3. Domain Models & Business Logic

### 3.1 Core Domains

#### 3.1.1 Organization Domain
**Purpose:** Multi-tenant organization and branch management

**Entities:**
- **Organization:** Represents a dental clinic organization (tenant)
  - Attributes: name, email, phone, address, logo, subscription_tier
  - Relations: hasMany(branches), hasMany(users)
  
- **Branch:** Physical location of a clinic
  - Attributes: name, code, address, phone, operating_hours
  - Relations: belongsTo(organization), hasMany(appointments)

**Business Rules:**
- One organization can have multiple branches
- Each user belongs to one organization but can access multiple branches (based on permissions)
- Branch code must be unique within an organization
- Super admin can manage all organizations

#### 3.1.2 Authentication Domain
**Purpose:** User authentication, authorization, and session management

**Entities:**
- **User:** System users (doctors, staff, admin)
  - Attributes: name, email, password, role, status
  - Relations: belongsTo(organization), hasMany(appointments)
  
- **Role & Permission:** Spatie permission system
  - Roles: super_admin, admin, doctor, receptionist, cashier
  - Permissions: granular access control

**Features:**
- Multi-device session management
- Device tracking and management
- Login history & audit logs
- Refresh token mechanism
- Password reset flow

#### 3.1.3 Patient Domain
**Purpose:** Patient registration and medical records

**Entities:**
- **Patient:** Dental clinic patients
  - Attributes: medical_record_number, name, dob, gender, contact, address
  - Relations: hasMany(appointments), hasMany(treatments), belongsTo(branch)

**Business Rules:**
- Medical record number auto-generated per branch
- Patient data includes medical history
- GDPR/Privacy compliant data handling
- Support for patient photo and documents

#### 3.1.4 Appointment Domain
**Purpose:** Appointment scheduling and management

**Entities:**
- **Appointment:** Scheduled patient visits
  - Attributes: appointment_date, time_slot, doctor_id, patient_id, status
  - Relations: belongsTo(patient), belongsTo(doctor), belongsTo(branch)

**Business Rules:**
- Doctor availability checking
- Prevent double booking
- Appointment status workflow: pending → confirmed → completed → cancelled
- SMS/Email notification integration ready

#### 3.1.5 Treatment Domain
**Purpose:** Dental treatment records and procedures

**Entities:**
- **Treatment:** Dental procedures performed
  - Attributes: treatment_date, diagnosis, procedures, notes, cost
  - Relations: belongsTo(appointment), belongsTo(patient), belongsTo(doctor)

**Business Rules:**
- Treatment always linked to an appointment
- Support for multiple procedures per treatment
- Treatment cost calculation with discounts
- Odontogram support for tooth charting

#### 3.1.6 Inventory Domain
**Purpose:** Dental supplies and equipment management

**Entities:**
- **InventoryItem:** Dental supplies and equipment
  - Attributes: name, code, category, unit, quantity, min_stock, cost_price
  - Relations: belongsTo(branch), hasMany(stockMovements)

**Business Rules:**
- Stock level tracking per branch
- Automatic reorder point alerts
- Batch/expiry date tracking for medicines
- Stock transfer between branches

#### 3.1.7 Finance Domain
**Purpose:** Financial transactions and billing

**Entities:**
- **Invoice:** Patient billing
  - Attributes: invoice_number, date, patient_id, total_amount, paid_amount, status
  - Relations: belongsTo(patient), hasMany(invoiceItems)

- **Payment:** Payment records
  - Attributes: payment_date, amount, method, reference_number
  - Relations: belongsTo(invoice)

**Business Rules:**
- Multiple payment methods (cash, transfer, card, insurance)
- Partial payment support
- Tax calculation (PPN)
- Integration ready for payment gateway (Midtrans)

#### 3.1.8 Master Data Domain
**Purpose:** System-wide reference data

**Reference Tables:**
- Countries, Provinces, Cities, Districts, Villages
- Currencies, Timezones, Languages
- Genders, Religions, Blood Types, Marital Statuses
- Patient Types, Doctor Specialties, Treatment Categories
- Payment Methods, Insurance Companies, Tax Rates

---

## 4. API Architecture

### 4.1 API Design Principles

- **RESTful:** Standard REST conventions (GET, POST, PUT, DELETE)
- **OpenAPI 3.1:** Full API specification documentation
- **Versioning:** `/api/v1/` prefix for version management
- **Consistent Response:** Standard JSON response format
- **Error Handling:** Consistent error response structure
- **Authentication:** Bearer token (Laravel Sanctum)

### 4.2 API Endpoints Structure

**Current Endpoints (Deployed):**
```
BASE URL: http://108.136.48.83:8080/api/api/v1

Authentication:
POST   /auth/login                    # User login
POST   /auth/logout                   # User logout
POST   /auth/logout-all               # Logout all devices
POST   /auth/refresh                  # Refresh token
POST   /auth/forgot-password          # Request password reset
POST   /auth/reset-password           # Reset password
POST   /auth/change-password          # Change password
GET    /auth/profile                  # Get user profile
PUT    /auth/profile                  # Update user profile
GET    /auth/devices                  # List user devices
DELETE /auth/devices/{deviceId}       # Remove device
GET    /auth/login-history            # Login history

AI Features:
GET    /ai-queries                    # List AI queries
POST   /ai-queries                    # Create AI query
GET    /ai-queries/{id}               # Get AI query
POST   /ai-queries/{id}/retry         # Retry AI query
POST   /ai-queries/{id}/cancel        # Cancel AI query
```

### 4.3 Response Format

**Success Response:**
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful",
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  },
  "code": "ERROR_CODE"
}
```

### 4.4 Authentication Flow

1. **Login:** `POST /api/api/v1/auth/login`
   - Input: email, password, device_name
   - Output: access_token, user_data, permissions

2. **API Request:** Include token in header
   ```
   Authorization: Bearer {access_token}
   ```

3. **Token Refresh:** `POST /api/api/v1/auth/refresh`
   - Automatic token renewal before expiry

4. **Logout:** `POST /api/api/v1/auth/logout`
   - Revoke current device token

---

## 5. Database Schema & Migrations

### 5.1 Migration Strategy

**Total Migrations Deployed:** 59 migrations

**Migration Categories:**
1. **Core System (9 migrations):**
   - organizations, branches, users
   - permission_tables (Spatie)
   - personal_access_tokens (Sanctum)
   - authentication tables (sessions, devices, login_histories)
   - audit_logs, system_logs, notifications
   - files table (attachments)

2. **Master Data (24 migrations):**
   - Geographic data (countries, provinces, cities, districts, villages)
   - Reference data (currencies, timezones, languages, nationalities)
   - Medical data (genders, religions, blood_types, marital_statuses)
   - Clinic data (patient_types, doctor_specialties, treatment_categories)
   - Financial data (payment_methods, insurance_companies, tax_rates)

3. **Business Domains (26 migrations):**
   - Patients, Doctors, Appointments
   - Treatments, Medical Records
   - Inventory, Procurement, Assets
   - Finance, Invoices, Payments
   - HR Records, CRM Contacts
   - Reports, Dashboards, Integrations
   - Subscriptions, Payment Transactions
   - AI Queries

### 5.2 Database Configuration

**Current Setup:**
```
Database: PostgreSQL 16
Host: 172.31.20.84 (EC2 Local)
Port: 5432
Database Name: dentalerp
User: dentalerp
Connection: Direct TCP/IP
```

**Features Enabled:**
- Row Level Security (RLS) on public tables
- Multi-tenancy via organization_id
- Audit logging on sensitive tables
- Soft deletes on most entities
- UUID primary keys for scalability

### 5.3 Data Security

- **Multi-tenancy:** Organization-based data isolation
- **RLS:** Row-level security policies
- **Encryption:** Sensitive data encrypted at rest
- **Audit Trail:** All changes logged with user context
- **Soft Deletes:** Data recovery capability

---

## 6. Deployment Architecture

### 6.1 Current Production Setup

**Backend Deployment:**
```
Platform: AWS EC2
OS: Ubuntu 24.04 LTS
IP: 108.136.48.83
Port: 8080 (public)
Container: Docker
Image: dentalerp:staging
Status: Running (migrations completed)
```

**Database:**
```
Type: PostgreSQL 16 (Local)
Host: 172.31.20.84
Port: 5432
Access: From Docker containers via host IP
Configuration: listen_addresses = '*'
Security: pg_hba.conf allows Docker networks
```

**Cache & Queue:**
```
Type: Redis 7
Host: 172.31.20.84
Port: 6379
Access: From Docker containers via host IP
Configuration: bind 0.0.0.0, protected-mode no
```

**Frontend Deployment:**
```
Platform: Vercel
URL: my-dent-care-q11342jnv-blackid.vercel.app
Build: Vite + React
CDN: Vercel Edge Network
Environment: VITE_API_BASE_URL=http://108.136.48.83:8080/api/api/v1
```

### 6.2 Deployment History & Challenges

**Major Milestones:**
1. ✅ Docker image built successfully
2. ✅ Configuration fixes for DATABASE_URL parsing
3. ✅ PostgreSQL local deployment (workaround for Supabase IPv6 issue)
4. ✅ Redis local deployment
5. ✅ All 59 migrations completed successfully
6. ✅ API endpoints responding correctly

**Challenges Resolved:**
1. **IPv6 Connectivity Issue:**
   - Problem: Supabase only provides IPv6 address, AWS EC2 doesn't support IPv6
   - Solution: Deployed PostgreSQL locally on EC2 server
   
2. **Container Networking:**
   - Problem: Docker containers can't access localhost services
   - Solution: Configured services to listen on 0.0.0.0 and use host IP from containers
   
3. **Environment Variable Loading:**
   - Problem: `.env` file not found in container
   - Solution: Volume mount `.env.staging` as `.env` in docker-compose

4. **Redis Connection:**
   - Problem: Redis protected mode blocking Docker connections
   - Solution: Disabled protected mode and configured bind to 0.0.0.0

### 6.3 Docker Configuration

**docker-compose.staging.yaml:**
```yaml
services:
  app:
    image: dentalerp:staging
    ports:
      - "8080:8000"
    env_file:
      - ../.env.staging
    volumes:
      - ../.env.staging:/var/www/.env:ro
    healthcheck:
      test: ["CMD-SHELL", "php -r \"exit(@file_get_contents('http://127.0.0.1:8000/up') === false ? 1 : 0);\""]
      interval: 10s
      timeout: 5s
      retries: 10
      start_period: 30s
    networks:
      - dentalerp_staging

  queue:
    image: dentalerp:staging
    command: php artisan queue:work
    depends_on:
      app:
        condition: service_healthy

  scheduler:
    image: dentalerp:staging
    command: php artisan schedule:work
    depends_on:
      app:
        condition: service_healthy
```

### 6.4 Environment Variables

**Critical Configuration:**
```env
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:vfRBjbEJbcuX9+lpwU5MewhyyTyE2Al3FW39ApyOkvg=
APP_URL=http://108.136.48.83:8080

DATABASE_URL=postgresql://dentalerp:Ifansetiawan093600@172.31.20.84:5432/dentalerp

REDIS_HOST=172.31.20.84
REDIS_PORT=6379

CACHE_DRIVER=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

FRONTEND_URL=https://my-dent-care-q11342jnv-blackid.vercel.app
SANCTUM_STATEFUL_DOMAINS=my-dent-care-q11342jnv-blackid.vercel.app
```

---

## 7. Testing Strategy

### 7.1 Testing Pyramid

```
           E2E Tests (5%)
         /               \
    Integration Tests (15%)
   /                         \
Unit Tests (80%)
```

### 7.2 Test Coverage Requirements

- **Unit Tests:** 80%+ coverage
  - Services, Repositories, DTOs, Enums, Policies
  
- **Feature Tests:** All API endpoints
  - Authentication flows
  - CRUD operations
  - Business logic validation
  
- **Integration Tests:** Cross-domain workflows
  - Appointment → Treatment → Invoice flow
  - Stock movement → Inventory update

### 7.3 Testing Tools

- **Framework:** Pest PHP / PHPUnit
- **Factories:** Model factories for test data
- **Database:** In-memory SQLite for fast tests
- **CI/CD:** GitHub Actions (planned)

---

## 8. Security & Compliance

### 8.1 Authentication & Authorization

**Authentication:**
- Laravel Sanctum (token-based)
- Multi-device session management
- Refresh token mechanism
- Login history tracking

**Authorization:**
- Spatie Laravel Permission
- Role-based access control (RBAC)
- Permission-based authorization
- Row Level Security (RLS)

### 8.2 Data Protection

- **Encryption at Rest:** Database encryption
- **Encryption in Transit:** HTTPS (planned for production)
- **Password Security:** Bcrypt hashing
- **Sensitive Data:** Masked in logs
- **Audit Trail:** All data changes logged

### 8.3 Compliance Requirements

**GDPR/Privacy:**
- Data access control
- Right to be forgotten (soft deletes)
- Data export capability
- Consent management

**Healthcare (Indonesia):**
- SATUSEHAT integration ready
- BPJS integration ready
- Medical record confidentiality
- Audit trail for medical data

---

## 9. Integration Requirements

### 9.1 Planned Integrations

**Government Systems:**
- **SATUSEHAT:** Indonesian health data exchange
- **BPJS Kesehatan:** National health insurance

**Payment Gateway:**
- **Midtrans:** Payment processing
  - Credit/Debit cards
  - Bank transfer
  - E-wallet (GoPay, OVO, etc.)

**Messaging:**
- **SMS Gateway:** Appointment reminders
- **Email:** Notifications and reports
- **WhatsApp Business API:** Patient communication

### 9.2 Third-party APIs

**Geolocation:**
- Indonesia region data (built-in)
- Google Maps API (optional)

**File Storage:**
- Local filesystem (current)
- AWS S3 / Cloud storage (planned)

---

## 10. Performance Requirements

### 10.1 Response Time

| Endpoint Type | Target (p95) | Maximum (p99) |
|---------------|--------------|---------------|
| Read Operations | <100ms | <200ms |
| Write Operations | <200ms | <500ms |
| Reports | <2s | <5s |
| File Upload | <5s | <10s |

### 10.2 Scalability

**Current Capacity:**
- Concurrent users: 100+
- Database size: Unlimited (PostgreSQL)
- File storage: Depends on disk space

**Scaling Strategy:**
- Horizontal: Add more EC2 instances + load balancer
- Vertical: Increase EC2 instance size
- Database: PostgreSQL replication + read replicas
- Cache: Redis cluster

### 10.3 Availability

- **Target Uptime:** 99.5% (43.8 hours downtime/year)
- **Backup Frequency:** Daily automated backups
- **Disaster Recovery:** Database backup + restore procedures
- **Monitoring:** Application and infrastructure monitoring

---

## 11. User Roles & Permissions

### 11.1 Role Hierarchy

```
Super Admin (Platform Owner)
    ├── Admin (Organization Owner)
    │   ├── Doctor
    │   ├── Receptionist
    │   └── Cashier
    └── Staff (General)
```

### 11.2 Permission Matrix

| Feature | Super Admin | Admin | Doctor | Receptionist | Cashier |
|---------|-------------|-------|--------|--------------|---------|
| Manage Organizations | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage Branches | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage Users | ✅ | ✅ | ❌ | ❌ | ❌ |
| View Patients | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit Patients | ✅ | ✅ | ✅ | ✅ | ❌ |
| View Appointments | ✅ | ✅ | ✅ | ✅ | ❌ |
| Create Appointments | ✅ | ✅ | ❌ | ✅ | ❌ |
| View Treatments | ✅ | ✅ | ✅ | ❌ | ❌ |
| Create Treatments | ✅ | ✅ | ✅ | ❌ | ❌ |
| View Invoices | ✅ | ✅ | ❌ | ❌ | ✅ |
| Create Invoices | ✅ | ✅ | ❌ | ❌ | ✅ |
| View Reports | ✅ | ✅ | ✅ | ❌ | ✅ |

---

## 12. Future Roadmap

### 12.1 Phase 1 - MVP (Completed)
✅ Core domain implementation  
✅ Authentication & Authorization  
✅ Basic CRUD operations  
✅ Database migrations  
✅ API endpoints  
✅ Docker deployment  

### 12.2 Phase 2 - Production Ready (Current)
🔄 Frontend integration  
🔄 Complete API documentation  
⏳ SSL/HTTPS setup  
⏳ Production database (migrate to managed PostgreSQL or resolve Supabase IPv6)  
⏳ Monitoring & logging setup  
⏳ Automated backups  

### 12.3 Phase 3 - Enhanced Features (Q1 2027)
⏳ SMS/Email notifications  
⏳ Payment gateway integration (Midtrans)  
⏳ Advanced reporting & analytics  
⏳ Mobile app (React Native)  
⏳ WhatsApp integration  
⏳ Document management  

### 12.4 Phase 4 - Integration & Scale (Q2 2027)
⏳ SATUSEHAT integration  
⏳ BPJS integration  
⏳ Multi-language support  
⏳ Advanced inventory (batch tracking, expiry alerts)  
⏳ CRM features (marketing campaigns)  
⏳ Telemedicine features  

### 12.5 Phase 5 - AI & Automation (Q3-Q4 2027)
⏳ AI-powered diagnosis assistance  
⏳ Automated appointment scheduling  
⏳ Predictive analytics (patient no-show)  
⏳ Intelligent inventory forecasting  
⏳ Automated report generation  
⏳ Voice-to-text for medical notes  

---

## 13. Known Issues & Technical Debt

### 13.1 Current Issues

1. **Route Configuration:**
   - Issue: Duplicate `api/api/v1` prefix in routes
   - Impact: Endpoints accessible at `/api/api/v1/` instead of `/api/v1/`
   - Priority: Medium
   - Resolution: Fix route service provider configuration

2. **Health Check:**
   - Issue: `/up` endpoint returns 500 error
   - Impact: Container shows as unhealthy, but API functions normally
   - Priority: Low
   - Resolution: Debug health check implementation

3. **Database Connection:**
   - Issue: Using local PostgreSQL instead of Supabase (IPv6 limitation)
   - Impact: Manual backup management required
   - Priority: High
   - Resolution: Either enable IPv6 on AWS EC2 or migrate to managed PostgreSQL with IPv4

### 13.2 Technical Debt

1. **Testing:**
   - Current coverage: ~0% (no tests written yet)
   - Target: 80%+ coverage
   - Action: Implement unit and feature tests

2. **Documentation:**
   - API documentation incomplete
   - Missing OpenAPI specification
   - Action: Generate and maintain OpenAPI docs

3. **Monitoring:**
   - No application monitoring
   - No error tracking (Sentry)
   - Action: Setup monitoring and alerting

4. **CI/CD:**
   - Manual deployment process
   - No automated testing pipeline
   - Action: Setup GitHub Actions CI/CD

---

## 14. Success Criteria & KPIs

### 14.1 Technical KPIs

| Metric | Target | Measurement |
|--------|--------|-------------|
| API Response Time (p95) | <200ms | APM monitoring |
| Uptime | 99.5% | Uptime monitor |
| Test Coverage | >80% | Code coverage tool |
| Bug Density | <5 bugs/1000 LOC | Issue tracker |
| Security Vulnerabilities | 0 critical/high | Security scanner |

### 14.2 Business KPIs

| Metric | Target | Measurement |
|--------|--------|-------------|
| User Adoption | 50+ clinics in 12 months | User registration |
| User Satisfaction | >90% | NPS survey |
| Monthly Active Users | 500+ | Analytics |
| Churn Rate | <5% | Subscription data |
| Support Tickets | <10/month | Support system |

### 14.3 Development Velocity

| Metric | Target | Measurement |
|--------|--------|-------------|
| Sprint Velocity | 40-50 story points | Sprint planning |
| Deployment Frequency | 2x/week | Deployment logs |
| Lead Time | <7 days | Issue tracker |
| MTTR (Mean Time to Repair) | <4 hours | Incident logs |

---

## 15. Risk Assessment & Mitigation

### 15.1 Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Database performance degradation | Medium | High | Implement query optimization, add indexes, setup monitoring |
| IPv6 connectivity issues | Low | Medium | Already mitigated with local PostgreSQL |
| Security vulnerabilities | Low | Critical | Regular security audits, dependency updates |
| Data loss | Low | Critical | Automated daily backups, disaster recovery plan |

### 15.2 Business Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Low user adoption | Medium | High | User training, documentation, support |
| Competition | High | Medium | Continuous feature development, competitive pricing |
| Regulatory changes | Low | High | Stay updated with healthcare regulations |
| Technical team capacity | Medium | High | Documentation, code standards, knowledge sharing |

---

## 16. Support & Maintenance

### 16.1 Support Channels

- **Documentation:** Comprehensive user guide and API docs
- **Email Support:** support@mydentcare.com (planned)
- **Ticket System:** Issue tracking system
- **Training:** Video tutorials and webinars

### 16.2 Maintenance Schedule

- **Daily:** Automated backups
- **Weekly:** Security patches, dependency updates
- **Monthly:** Performance review, capacity planning
- **Quarterly:** Major feature releases, security audits

---

## 17. Pricing & Subscription Model

### 17.1 Pricing Strategy

**Simple, Transparent, Per-Branch Pricing**

My Dent Care menggunakan model pricing yang sederhana dan mudah dipahami:

```
Rp 300.000 / cabang / bulan
```

**What's Included:**
- ✅ **Full Features** - Semua fitur tanpa batasan
- ✅ **Unlimited Users** - Tidak ada batasan jumlah user per cabang
- ✅ **Unlimited Patients** - Tidak ada batasan jumlah data pasien
- ✅ **Unlimited Transactions** - Tidak ada batasan jumlah transaksi
- ✅ **Free Updates** - Semua fitur baru otomatis tersedia
- ✅ **Technical Support** - Email support dan dokumentasi lengkap
- ✅ **Data Backup** - Automated daily backup
- ✅ **99.5% Uptime SLA** - Service level agreement

### 17.2 Free Trial

**1 Bulan Free Trial - Full Access**

Setiap organisasi baru mendapatkan:
- **Duration:** 30 hari penuh
- **Access:** Semua fitur tanpa batasan
- **No Credit Card:** Tidak perlu kartu kredit untuk memulai trial
- **No Commitment:** Bisa cancel kapan saja tanpa biaya
- **Data Retention:** Data tidak hilang setelah trial berakhir

**Trial to Paid Transition:**
1. User mendaftar dan langsung mendapat akses penuh selama 30 hari
2. 7 hari sebelum trial berakhir: Notifikasi reminder pertama
3. 3 hari sebelum trial berakhir: Notifikasi reminder kedua
4. Hari terakhir trial: Notifikasi untuk subscribe
5. Setelah trial berakhir:
   - Akses read-only untuk melihat data
   - Tidak bisa tambah/edit data sampai subscribe
   - Data tetap tersimpan aman

### 17.3 Billing Calculation

**Per-Branch Basis:**
- Billing dihitung berdasarkan jumlah cabang aktif
- 1 cabang = Rp 300.000/bulan
- 5 cabang = Rp 1.500.000/bulan
- 10 cabang = Rp 3.000.000/bulan

**Example Scenarios:**

| Clinic Type | Branches | Monthly Cost | Yearly Cost |
|-------------|----------|--------------|-------------|
| Single Clinic | 1 | Rp 300.000 | Rp 3.600.000 |
| Small Chain | 3 | Rp 900.000 | Rp 10.800.000 |
| Medium Chain | 10 | Rp 3.000.000 | Rp 36.000.000 |
| Large Network | 50 | Rp 15.000.000 | Rp 180.000.000 |

### 17.4 Payment Methods

**Supported Payment Methods:**
1. **Bank Transfer** (Manual verification)
   - BCA, Mandiri, BNI, BRI
   - Konfirmasi dalam 1x24 jam
   
2. **Virtual Account** (Auto verification via Midtrans)
   - BCA VA, Mandiri VA, BNI VA, BRI VA, Permata VA
   - Otomatis aktif setelah pembayaran
   
3. **Credit/Debit Card** (via Midtrans)
   - Visa, MasterCard, JCB
   - Recurring billing otomatis
   
4. **E-Wallet** (via Midtrans)
   - GoPay, OVO, DANA, ShopeePay
   - Langsung aktif setelah pembayaran

### 17.5 Subscription Management

**Features:**
- **Self-Service Portal:** User bisa upgrade/downgrade sendiri
- **Add Branch:** Langsung aktif, prorated billing
- **Remove Branch:** Adjustment di billing cycle berikutnya
- **Pause Subscription:** Freeze account (data tetap tersimpan)
- **Cancel Anytime:** Tidak ada minimum contract
- **Refund Policy:** Prorated refund jika cancel mid-cycle

**Billing Cycle:**
- **Monthly Billing:** Ditagih setiap tanggal subscription dimulai
- **Auto-Renewal:** Otomatis perpanjang jika payment method active
- **Grace Period:** 7 hari setelah jatuh tempo sebelum suspend
- **Suspend:** Read-only access, tidak bisa tambah/edit data
- **Terminate:** 30 hari setelah suspend, data di-archive

### 17.6 No Hidden Fees

**What's NOT Charged:**
- ❌ Setup fee
- ❌ Training fee
- ❌ Data migration fee (dari sistem lama)
- ❌ Per-user fee
- ❌ Per-transaction fee
- ❌ Storage fee (reasonable limits)
- ❌ Support fee

**Only Pay For:**
- ✅ Number of active branches
- ✅ Nothing else

### 17.7 Enterprise & Volume Discount

**Coming Soon:**
- Custom pricing untuk 50+ cabang
- Dedicated account manager
- Priority support
- Custom integration
- On-premise deployment option
- SLA customization

**Contact for Enterprise:**
- Email: enterprise@mydentcare.com
- Minimum: 50 branches

### 17.8 Competitive Advantage

**Why This Pricing Works:**

1. **Predictable Costs:** Easy budgeting, no surprise fees
2. **Scale Friendly:** Pay only for what you use
3. **Full Features:** No feature gating or upselling
4. **Unlimited Users:** Hire more staff without extra cost
5. **Fair Value:** Rp 300k/month = Rp 10k/day for full ERP system

**Market Positioning:**
- **Target Market:** SME dental clinics (1-20 branches)
- **Competitive:** Lower than competitors while offering more features
- **Value Prop:** Enterprise-grade system at SME pricing

### 17.9 Financial Projections

**Revenue Model (Conservative):**

| Month | Clinics | Avg Branches | MRR | ARR |
|-------|---------|--------------|-----|-----|
| M3 | 5 | 2 | Rp 3.000.000 | Rp 36.000.000 |
| M6 | 15 | 2.5 | Rp 11.250.000 | Rp 135.000.000 |
| M12 | 50 | 3 | Rp 45.000.000 | Rp 540.000.000 |
| M24 | 150 | 4 | Rp 180.000.000 | Rp 2.160.000.000 |

**Assumptions:**
- 5% monthly churn rate
- Average 3 branches per clinic
- 80% conversion from trial to paid
- 10 new trials per month

---

## 18. Appendix

### 18.1 Glossary

- **DDD:** Domain Driven Design - architectural pattern organizing code by business domains
- **SOLID:** Software design principles (Single responsibility, Open-closed, Liskov substitution, Interface segregation, Dependency inversion)
- **RLS:** Row Level Security - PostgreSQL feature for data isolation
- **Sanctum:** Laravel authentication package for API tokens
- **ERP:** Enterprise Resource Planning - integrated management software
- **SATUSEHAT:** Indonesian national health data exchange platform
- **BPJS:** Indonesian national health insurance system

### 18.2 References

- **Architecture Documentation:** `DentalERP/AGENTS.md`
- **Deployment Guide:** `DEPLOYMENT_STATUS_FINAL.md`
- **Domain README:** `DentalERP/app/Domains/*/README.md`
- **API Spec:** OpenAPI specification (to be generated)

### 18.3 Contact Information

- **Project Repository:** https://github.com/ifansetiawan27/My-Dent-Care
- **Project Manager:** Ifan Setiawan
- **Development Team:** TBD
- **Infrastructure:** AWS EC2 (108.136.48.83), Vercel

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-08-23 | Development Team | Initial PRD creation based on deployment and architecture |
| 1.1 | 2026-08-23 | Development Team | Added Pricing & Subscription Model section |

---

**End of Product Requirements Document**
