# My Dent Care — User Guide & Integration Architecture

**Version:** 1.0  
**Date:** 25 August 2026  
**Status:** Production Deployment Phase

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Memulai](#2-memulai)
3. [Navigasi Aplikasi](#3-navigasi-aplikasi)
4. [Modul Utama](#4-modul-utama)
5. [Modul Operasional](#5-modul-operasional)
6. [Modul Manajemen](#6-modul-manajemen)
7. [Modul Analitik & Integrasi](#7-modul-analitik--integrasi)
8. [Modul Sistem](#8-modul-sistem)
9. [Odontogram 3D Interaktif](#9-odontogram-3d-interaktif)
10. [Subscription & Billing](#10-subscription--billing)
11. [Role-Based Access Control](#11-role-based-access-control)
12. [Integrasi & Arsitektur Konektivitas](#12-integrasi--arsitektur-konektivitas)
13. [Demo Accounts](#13-demo-accounts)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Pendahuluan

**My Dent Care** adalah platform **Enterprise Resource Planning (ERP)** yang dirancang khusus untuk manajemen klinik gigi multi-cabang. Platform ini dibangun dengan arsitektur enterprise-grade menggunakan **Domain Driven Design (DDD)** dan prinsip **SOLID** untuk memastikan skalabilitas, maintainability, dan extensibility.

### Key Highlights
- **Target:** Klinik gigi single-location hingga multi-branch (1–100+ cabang)
- **Architecture:** Domain Driven Design (DDD) + SOLID Principles
- **Tech Stack:** Laravel 12 (PHP 8.4), PostgreSQL 16, Redis, Docker
- **Frontend:** Vue 3 + Vite (Material UI / Mantis design system)
- **Deployment:** AWS EC2 (Backend) + Vercel (Frontend)
- **Security:** Laravel Sanctum, Row Level Security (RLS)

### URL Aplikasi
| Environment | URL |
|-------------|-----|
| Landing Page | https://my-dent-care.vercel.app |
| Web App (Login) | https://my-dent-care.vercel.app/login |
| Backend API | http://108.136.48.83:8080/api/v1 |
| Development (Local) | http://localhost:5173 |

---

## 2. Memulai

### 2.1 Registrasi & Free Trial

1. Kunjungi **Landing Page** di `https://my-dent-care.vercel.app`
2. Klik **"Mulai Free Trial 30 Hari"**
3. Anda akan diarahkan ke halaman **Login** — klik **"Belum punya akun? Daftar"** (jika tersedia) atau gunakan akun demo
4. Setiap organisasi baru mendapat **30 hari free trial penuh** — semua fitur terbuka, tanpa kartu kredit

### 2.2 Login

**Menggunakan Akun Demo:**

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@demodental.com` | `password123` |
| Dokter | `drjane@demodental.com` | `password123` |
| Resepsionis | `sarah@demodental.com` | `password123` |

**Langkah:**
1. Buka halaman **Login**
2. Masukkan email & password
3. Klik **"Masuk"** — atau klik tombol **"Super Admin"** / **"Dokter"** / **"Resepsionis"** untuk auto-fill
4. Setelah login berhasil, Anda akan diarahkan ke **Dashboard** halaman utama aplikasi

### 2.3 Logout

Klik **"Logout"** di bagian bawah sidebar untuk keluar. Sesi akan diakhiri dan token dihapus.

---

## 3. Navigasi Aplikasi

### 3.1 Layout

Setelah login, tampilan terdiri dari:

```
+------------------+------------------------------------------+
|    SIDEBAR       |         TOPBAR                          |
| (Menu Navigasi)  | (Judul Halaman + Avatar User)            |
|                  +------------------------------------------+
|  ▸ Dashboard     |                                          |
|  ▸ Appointment   |         CONTENT AREA                     |
|  ▸ Patients      |         (Router View)                    |
|  ▸ ...           |                                          |
|                  |                                          |
|  ▸ Logout        |                                          |
+------------------+------------------------------------------+
```

### 3.2 Sidebar Menu

Sidebar terdiri dari **5 grup menu**:

| Grup | Menu | Deskripsi |
|------|------|-----------|
| **Main** | Dashboard | Ringkasan operasional klinik |
| | Appointment | Jadwal janji temu pasien |
| | Patients | Registrasi & data pasien |
| | Medical Records | Rekam medis digital (EMR) |
| | Odontogram | Tooth charting 3D interaktif |
| | Treatment | Katalog tindakan & perawatan |
| **Operations** | Billing | Invoice & billing pasien |
| | Inventory | Stok barang & alat |
| | Pharmacy | Obat & bahan farmasi |
| | Laboratory | Order lab & hasil |
| | Radiology | Foto X-ray & citra radiologi |
| **Management** | Doctors | Profil & jadwal dokter |
| | Employees | Data kepegawaian |
| | Branches | Multi-cabang klinik |
| | Organization | Profil organisasi |
| | Users & Roles | Manajemen user & RBAC |
| | CRM | Kontak & follow-up pasien |
| **Reports & Integration** | Reports | Laporan & analitik |
| | AI Assistant | AI-powered diagnosis |
| | Integrations | SATUSEHAT, BPJS, dll |
| **System** | Subscription | Paket langganan |
| | Settings | Pengaturan klinik |

### 3.3 Navigasi Antar Menu

- **Klik menu** di sidebar untuk berpindah halaman — tampilan langsung berganti tanpa delay
- **Breadcrumb**: judul halaman aktif ditampilkan di topbar
- **Mobile**: sidebar otomatis collapse, buka dengan tombol hamburger (☰) di pojok kiri atas

---

## 4. Modul Utama

### 4.1 Dashboard

**Fungsi:** Ringkasan operasional klinik Anda.

**Tampilan:**
- **Sapaan user**: "Hi, [Nama] 👋"
- **Banner subscription**: status trial, peringatan pembayaran, atau info kedaluwarsa
- **Stat cards**: Status Langganan, Paket, Penyimpanan, Users
- **Quick Actions**: 8 tombol akses cepat ke modul Appointment, Patients, Medical Records, Billing, Treatment, Reports, Inventory, Settings

**Tombol Atas:**
- **"+ Appointment"** — buat janji temu baru
- **"+ Patient"** — registrasi pasien baru

### 4.2 Appointment (Penjadwalan)

**Fungsi:** Kelola jadwal janji temu pasien.

**Fitur:**
- **List Appointment**: tabel dengan kolom Pasien, Dokter, Jadwal, Tipe, Status
- **Tambah Appointment**: modal form dengan dropdown pasien (auto-load dari API), dokter, pilih jadwal (datetime-local), tipe janji, status
- **Search**: berdasarkan nama pasien, dokter, status, tipe
- **Delete**: hapus appointment

**Alur Kerja:**
1. Klik **"+ Tambah Appointment"**
2. Pilih **Pasien** dari dropdown (nama + kode RM)
3. Pilih **Dokter** dari dropdown
4. Tentukan **Jadwal** (tanggal & jam)
5. Pilih **Tipe** (checkup, treatment, consultation, follow_up, emergency)
6. Pilih **Status** (scheduled, confirmed, completed, cancelled, no_show)
7. Tambahkan **Catatan** jika perlu
8. Klik **Simpan**

### 4.3 Patients (Pasien)

**Fungsi:** Registrasi & manajemen data pasien.

**Fitur:**
- **List Pasien**: tabel dengan No. RM, Nama, Jenis Kelamin, Tgl Lahir, Telepon
- **Tambah Pasien**: form dengan No. Rekam Medis, Nama Lengkap, Tgl Lahir, Jenis Kelamin, Golongan Darah, Agama, Status, Telepon, Email, Alamat
- **Delete**: hapus data pasien

### 4.4 Medical Records (EMR)

**Fungsi:** Rekam medis digital pasien.

**Fitur:**
- **List EMR**: tabel dengan Pasien, Keluhan Utama, Diagnosa, Status
- **Tambah EMR**: pilih pasien, dokter, appointment (semua dari dropdown), isi keluhan utama, diagnosa, catatan perawatan, tanda vital (JSON)
- **Delete**: hapus rekam medis

### 4.5 Odontogram (3D Interactive Tooth Chart)

**Fungsi:** Tooth charting digital interaktif untuk pemetaan kondisi gigi.

**Fitur:**
- **Chart 3D**: 32 gigi permanen (FDI numbering) dengan bentuk anatomis, shading 3D, drop shadow, dan gusi (gingiva) pink
- **7 kondisi gigi**: Karies (merah), Tumpatan (biru), Hilang (abu), Mahkota (emas), RCT (ungu), Ekstraksi (oranye), Stain (hijau)
- **Klik gigi** untuk menandai — klik lagi untuk menghapus tanda
- **Upload gambar**: JPG/PNG untuk melampirkan foto/X-ray pasien (max 2MB, preview inline)
- **Report**: setiap gigi ditandai disimpan sebagai record individu dengan kondisi + gambar

> Lihat [§9 — Odontogram 3D Interaktif](#9-odontogram-3d-interaktif) untuk detail lengkap.

### 4.6 Treatment (Perawatan)

**Fungsi:** Katalog & riwayat tindakan dental.

**Fitur:**
- **List Treatment**: tabel dengan Pasien, Tindakan, Biaya, Status
- **Tambah Treatment**: pilih pasien, dokter, appointment; isi jenis tindakan, biaya, deskripsi
- **Delete**: hapus perawatan

---

## 5. Modul Operasional

### 5.1 Billing & Invoice

**Fungsi:** Penagihan pasien & invoice profesional.

**Fitur:**
- **List Invoice**: No. Invoice, Pasien, Total, Jatuh Tempo, Status
- **Tambah Invoice**: pilih pasien, nomor invoice, total, jatuh tempo, status (draft, unpaid, paid, overdue, dll), catatan
- **Status badge**: draft (abu), unpaid (kuning), paid (hijau), overdue (merah)

### 5.2 Inventory (Inventaris)

**Fungsi:** Stok barang & alat per cabang.

**Fitur:**
- **List Barang**: Kode Barang, Nama, Qty, Satuan, Harga, Status Aktif
- **Tambah Barang**: kode, nama, jumlah, min stok, satuan, harga satuan, status
- **Delete**: hapus item

### 5.3 Pharmacy (Farmasi)

**Fungsi:** Obat & bahan farmasi, batch tracking.

**Fitur:**
- **List Obat**: Kode Obat, Nama, Qty, Satuan, Kedaluwarsa, Status
- **Tambah Obat**: kode, nama, jumlah, satuan, harga, no. batch, tanggal kedaluwarsa
- **Delete**: hapus item

### 5.4 Laboratory (Laboratorium)

**Fungsi:** Order lab & hasil pemeriksaan.

**Fitur:**
- **List Order**: No. Order, Pasien, Dokter, Status, Selesai
- **Tambah Order**: pilih pasien, dokter, nomor order, status (ordered, processing, completed, cancelled), deskripsi, hasil
- **Status badge**: ordered (biru), processing (kuning), completed (hijau), cancelled (abu)

### 5.5 Radiology (Radiologi)

**Fungsi:** Manajemen foto X-ray & citra radiologi.

**Fitur:**
- **List Pemeriksaan**: Pasien, Dokter, Jenis, Status, Tanggal
- **Tambah Pemeriksaan**: nama pasien, dokter, jenis (Panoramic, CBCT, Cephalometric, Periapical, Bitewing, Occlusal), status (ordered, processing, completed, abnormal), URL gambar, catatan
- **Edit/Delete**: kelola data radiologi
- Data disimpan di **localStorage** (backend endpoint dalam pengembangan)

---

## 6. Modul Manajemen

### 6.1 Doctors (Dokter)

**Fungsi:** Profil dokter, spesialisasi, jadwal praktik.

**Fitur:**
- **List Dokter**: Kode, Nama, Spesialisasi, Biaya Konsultasi, Telepon
- **Tambah Dokter**: kode dokter, nama lengkap, no. STR/SIP, biaya konsultasi, telepon, email, jenis kelamin
- **Delete**: hapus data dokter

### 6.2 Employees (Karyawan)

**Fungsi:** Data kepegawaian klinik.

**Fitur:**
- **List Karyawan**: NIP, Nama, Jabatan, Status Kerja, Mulai Kerja
- **Tambah Karyawan**: NIP, nama, status kerja (active, contract, probation, resigned, terminated), tanggal masuk, jabatan, telepon, email
- **Status badge**: active (hijau), contract (biru), probation (kuning), resigned (abu), terminated (merah)

### 6.3 Branches (Cabang)

**Fungsi:** Kelola multi-cabang klinik.

**Fitur:**
- **List Cabang**: Kode, Nama, Tipe, Kota, Telepon, Status
- **Tambah Cabang**: kode cabang, nama, tipe (main/branch), telepon, email, alamat, kota, provinsi, kode pos, zona waktu, status (active/inactive)
- **Delete**: hapus cabang

### 6.4 Organization (Organisasi)

**Fungsi:** Profil organisasi klinik.

**Halaman ini terhubung ke API nyata** (`api/v1/settings`).

**Fitur:**
- **Banner profil**: menampilkan nama klinik, nama legal, kota
- **Form edit**: nama klinik, nama legal, email, telepon, website, alamat, kota, provinsi, kode pos
- **Simpan** untuk update profil organisasi

### 6.5 Users & Roles

**Fungsi:** Manajemen user & role-based access control.

**Fitur:**
- **List User**: Nama, Email, Role, Status, Dibuat
- **Tambah User**: nama, email, role (super_admin, admin, doctor, receptionist, staff), status (active/inactive), telepon
- **Role badge**: super_admin (merah), admin (biru), doctor (cyan), receptionist (kuning), staff (abu)
- Data disimpan di **localStorage** (backend Spatie Permission sudah terpasang, endpoint UI dalam pengembangan)

### 6.6 CRM

**Fungsi:** Kontak & follow-up pasien.

**Fitur:**
- **List Kontak**: Pasien, Tipe Kontak, Kanal, Subjek, Status
- **Tambah Kontak**: pilih pasien, tipe kontak, kanal (whatsapp, email, sms, call), subjek, pesan, status (new, contacted, follow_up, closed)
- **Delete**: hapus kontak

---

## 7. Modul Analitik & Integrasi

### 7.1 Reports (Laporan)

**Fungsi:** Laporan keuangan, kunjungan, dan analitik.

**Fitur:**
- **List Laporan**: Nama, Tipe, Tanggal, Status
- **Tambah Laporan**: nama, tipe (revenue, visit, patient, doctor, finance, inventory), tanggal, parameter JSON
- **Delete**: hapus laporan

### 7.2 AI Assistant

**Fungsi:** AI-powered diagnosis assistance & predictive analytics.

**Fitur:**
- **List Query**: Tipe Query, Prompt, Model, Tokens, Status
- **Tambah Query**: tipe (diagnosis_assist, risk_alert, no_show_prediction, general), prompt
- **Status badge**: ordered (biru), in_progress (kuning)
- Roadmap Q3 2027: AI diagnosis, risk alert, no-show prediction

### 7.3 Integrations (Integrasi)

**Fungsi:** Konfigurasi integrasi pihak ketiga.

**Fitur:**
- **List Konfigurasi**: Provider, Nama, Aktif, Sync Terakhir
- **Tambah Konfigurasi**: provider, nama, status aktif
- Mendukung integrasi SATUSEHAT, BPJS, Midtrans, WhatsApp, Email, SMS

---

## 8. Modul Sistem

### 8.1 Subscription (Langganan)

**Fungsi:** Kelola paket langganan & billing.

**Tampilan:**
- **Paket Aktif**: status saat ini, nama paket, harga, badge trial/active
- **Banner trial**: jumlah hari tersisa
- **Plan Card**: paket **My Dent Care** — Rp 299.000/bulan per cabang, Free Trial 30 Hari
- **Fitur termasuk**: Full features, unlimited users/pasien/transaksi, rekam medis & odontogram, billing & invoice, backup harian otomatis, SLA 99,5%, integrasi SATUSEHAT/BPJS/Midtrans

### 8.2 Settings (Pengaturan)

**Fungsi:** Pengaturan profil klinik & invoice (halaman yang sudah ada sebelumnya).

---

## 9. Odontogram 3D Interaktif

### 9.1 Tampilan Chart

Odontogram menampilkan **32 gigi permanen** dalam dua busur (rahang atas & bawah):

```
          RAHANG ATAS
  18 17 16 15 14 13 12 11 | 21 22 23 24 25 26 27 28
  ─────────────────────────────────────────────────
  48 47 46 45 44 43 42 41 | 31 32 33 34 35 36 37 38
          RAHANG BAWAH
```

### 9.2 Cara Menandai Gigi

1. Pilih **kondisi** dari legend (Karies, Tumpatan, Hilang, Mahkota, RCT, Ekstraksi, Stain)
2. **Klik gigi** pada chart — gigi berubah warna sesuai kondisi
3. **Klik lagi** dengan kondisi yang sama untuk **menghapus tanda**
4. Jumlah gigi yang ditandai ditampilkan di kanan atas legend
5. Klik **"✕ Hapus Semua"** untuk mereset chart

### 9.3 Upload Gambar

1. Klik **"Choose File"** di field Upload Gambar
2. Pilih file JPG atau PNG (max 2MB)
3. **Preview** gambar akan ditampilkan di bawah field
4. Klik **"✕ Hapus"** untuk menghapus gambar sebelum submit
5. Gambar disimpan sebagai base64 di record odontogram

### 9.4 Menyimpan

1. Pastikan **Pasien** sudah dipilih dari dropdown
2. Minimal **satu gigi** harus ditandai (kondisi selain "Sehat")
3. Isi **Catatan** jika perlu
4. Klik **"Simpan Odontogram"**
5. Setiap gigi yang ditandai disimpan sebagai **satu record** di database

---

## 10. Subscription & Billing

### 10.1 Model Harga

**Satu Paket — My Dent Care:**
- **Rp 299.000 / bulan / cabang**
- **Free Trial**: 30 hari penuh, semua fitur terbuka
- **Tanpa kartu kredit** untuk memulai trial
- **Tanpa biaya tersembunyi** — tidak ada setup fee, training fee, per-user fee, atau per-transaction fee

### 10.2 Contoh Billing

| Jumlah Cabang | Biaya Bulanan | Biaya Tahunan |
|---------------|---------------|---------------|
| 1 cabang | Rp 299.000 | Rp 3.588.000 |
| 3 cabang | Rp 897.000 | Rp 10.764.000 |
| 10 cabang | Rp 2.990.000 | Rp 35.880.000 |

### 10.3 Status Subscription

| Status | Arti | Tampilan |
|--------|------|----------|
| `trial` | Free trial aktif | Badge biru |
| `active` | Berlangganan aktif | Badge hijau |
| `past_due` | Pembayaran gagal | Badge kuning |
| `grace` | Masa tenggang | Badge oranye |
| `expired` | Kedaluwarsa | Badge merah |
| `cancelled` | Dibatalkan | Badge merah |

---

## 11. Role-Based Access Control

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

| Fitur | Super Admin | Admin | Doctor | Receptionist | Cashier |
|-------|:-----------:|:-----:|:------:|:------------:|:-------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| Manage Patients | ✅ | ✅ | ✅ | ✅ | ❌ |
| Appointments | ✅ | ✅ | ❌ | ✅ | ❌ |
| Treatments | ✅ | ✅ | ✅ | ❌ | ❌ |
| Medical Records | ✅ | ✅ | ✅ | ❌ | ❌ |
| Odontogram | ✅ | ✅ | ✅ | ❌ | ❌ |
| Billing & Invoice | ✅ | ✅ | ❌ | ❌ | ✅ |
| Inventory | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reports | ✅ | ✅ | ✅ | ❌ | ✅ |
| Manage Users | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage Branches | ✅ | ✅ | ❌ | ❌ | ❌ |
| Organization Settings | ✅ | ✅ | ❌ | ❌ | ❌ |
| Subscription | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 12. Integrasi & Arsitektur Konektivitas

### 12.1 Diagram Integrasi

```
┌─────────────────────────────────────────────────────────────────┐
│                      MY DENT CARE PLATFORM                       │
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │  Pasien  │  │  Dokter  │  │  Staff   │  │  Admin   │        │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘        │
│       │              │              │              │              │
│  ┌────┴──────────────┴──────────────┴──────────────┴────┐       │
│  │                   FRONTEND (Vue 3)                    │       │
│  │              my-dent-care.vercel.app                  │       │
│  └────────────────────────┬─────────────────────────────┘       │
│                           │ HTTPS                                │
│  ┌────────────────────────┴─────────────────────────────┐       │
│  │                BACKEND API (Laravel 12)               │       │
│  │              api/v1/*  (108.136.48.83:8080)           │       │
│  │                                                       │       │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────────────┐    │       │
│  │  │PostgreSQL│  │  Redis   │  │  File Storage    │    │       │
│  │  │  (RLS)   │  │ (Cache)  │  │  (Local / S3)    │    │       │
│  │  └──────────┘  └──────────┘  └──────────────────┘    │       │
│  └──────────────────────────────────────────────────────┘       │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │            EXTERNAL INTEGRATIONS (Roadmap)                │   │
│  │                                                           │   │
│  │  ┌───────────┐  ┌───────────┐  ┌───────────────────┐    │   │
│  │  │ SATUSEHAT │  │   BPJS    │  │     Midtrans      │    │   │
│  │  │ Kesehatan │  │ Kesehatan │  │  Payment Gateway  │    │   │
│  │  │ (Q2 2027) │  │ (Q2 2027) │  │    (Q1 2027)      │    │   │
│  │  └───────────┘  └───────────┘  └───────────────────┘    │   │
│  │                                                           │   │
│  │  ┌───────────┐  ┌───────────┐  ┌───────────────────┐    │   │
│  │  │ WhatsApp  │  │   Email   │  │    SMS Gateway    │    │   │
│  │  │ Business  │  │  (SMTP)   │  │   (Q1 2027)       │    │   │
│  │  │  (Q3 2027)│  │ (Q1 2027) │  │                   │    │   │
│  │  └───────────┘  └───────────┘  └───────────────────┘    │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 12.2 Integrasi Pemerintah

#### SATUSEHAT (Q2 2027)
- **Fungsi**: Indonesian national health data exchange
- **Domain**: `IntegrationHub`
- **API**: FHIR-based interoperability
- **Data**: Rekam medis, data pasien, laporan kesehatan
- **Status**: Roadmap — backend IntegrationHub domain sudah siap (15 files), endpoint API sudah terdaftar

#### BPJS Kesehatan (Q2 2027)
- **Fungsi**: National health insurance integration
- **Domain**: `IntegrationHub`
- **API**: Klaim BPJS, verifikasi kepesertaan, rujukan
- **Data**: SEP (Surat Eligibilitas Peserta), klaim, status pembayaran
- **Status**: Roadmap — backend IntegrationHub domain sudah siap

### 12.3 Integrasi Pembayaran

#### Midtrans Payment Gateway (Q1 2027)
- **Fungsi**: Payment processing untuk subscription & billing
- **Domain**: `Billing` / `Subscription`
- **Metode Pembayaran**:
  - Bank Transfer (BCA, Mandiri, BNI, BRI)
  - Virtual Account (auto-verification)
  - Credit/Debit Card (Visa, MasterCard, JCB)
  - E-Wallet (GoPay, OVO, DANA, ShopeePay)
- **Status**: Roadmap — backend billing & subscription sudah siap, Midtrans SDK pending

### 12.4 Integrasi Komunikasi

#### Email (Q1 2027)
- **Fungsi**: Notifikasi appointment, invoice, reminder trial
- **Domain**: `Notification` (planned)
- **Provider**: SMTP / SendGrid / Mailgun
- **Status**: Roadmap

#### SMS Gateway (Q1 2027)
- **Fungsi**: Appointment reminder, OTP verifikasi
- **Domain**: `Notification` (planned)
- **Provider**: Twilio / Vonage / lokal (TELKOMSEL, dll)
- **Status**: Roadmap

#### WhatsApp Business API (Q3 2027)
- **Fungsi**: Komunikasi pasien, reminder appointment, follow-up
- **Domain**: `CRM` / `Notification` (planned)
- **Provider**: WhatsApp Business API (Meta)
- **Status**: Roadmap

### 12.5 Integrasi Internal

#### Row Level Security (RLS)
- **Fungsi**: Isolasi data per organisasi di level database
- **Domain**: Semua (PostgreSQL RLS policies)
- **Status**: ✅ Sudah aktif — setiap query otomatis difilter oleh `organization_id`

#### Spatie Permission (RBAC)
- **Fungsi**: Role-based access control dengan permission granular
- **Domain**: `Authentication` / `RolePermission`
- **Status**: ✅ Sudah aktif — 5 role, permission matrix per fitur

#### Audit Trail
- **Fungsi**: Logging seluruh aktivitas user dengan konteks
- **Domain**: `Core` (audit_logs table)
- **Status**: ✅ Sudah aktif — semua perubahan data tercatat

### 12.6 Arsitektur Domain

Platform terdiri dari **30 domain bisnis** yang independen:

| Domain | Files | Status | Integrasi |
|--------|:-----:|:------:|-----------|
| AI | 16 | API Ready | AI queries endpoint |
| Appointment | 14 | API Ready | — |
| Asset | 16 | API Ready | — |
| Authentication | 42 | API Ready | Sanctum, Spatie RBAC |
| Billing | 16 | API Ready | Midtrans (roadmap) |
| Branch | 17 | API Ready | — |
| CRM | 16 | API Ready | WhatsApp (roadmap) |
| Dashboard | 15 | API Ready | — |
| Doctor | 15 | API Ready | — |
| EMR | 13 | API Ready | SATUSEHAT (roadmap) |
| Employee | 16 | API Ready | — |
| HR | 16 | API Ready | — |
| IntegrationHub | 15 | API Ready | SATUSEHAT, BPJS, Midtrans |
| Inventory | 15 | API Ready | — |
| Laboratory | 16 | API Ready | — |
| MasterData | 108 | API Ready | — |
| Odontogram | 13 | API Ready | — |
| Organization | 9 | API Ready | — |
| Patient | 15 | API Ready | SATUSEHAT (roadmap) |
| Pharmacy | 15 | API Ready | — |
| Procurement | 16 | API Ready | — |
| Reporting | 16 | API Ready | — |
| Subscription | 24 | API Ready | Midtrans (roadmap) |
| Treatment | 16 | API Ready | — |
| User | 17 | API Ready | Spatie RBAC |

---

## 13. Demo Accounts

### 13.1 Akun Demo

| Role | Email | Password | Organisasi |
|------|-------|----------|-----------|
| Super Admin | `superadmin@demodental.com` | `password123` | Demo Dental |
| Dokter | `drjane@demodental.com` | `password123` | Demo Dental |
| Resepsionis | `sarah@demodental.com` | `password123` | Demo Dental |

### 13.2 Data Demo

- **Organisasi**: Demo Dental (ID: `a293e9b9-aace-470e-929f-baa04992aa22`)
- **Cabang**: Main Branch (ID: `a293e9be-f7f4-4bb3-819c-3236384c11e7`)
- **Subscription**: Free Trial 30 hari (Professional plan)
- **Roles**: super_admin, admin, doctor, receptionist, staff

---

## 14. Troubleshooting

### 14.1 Tidak Bisa Login

1. Pastikan backend berjalan: `php artisan serve --port=8080`
2. Pastikan frontend berjalan: `npm run dev` (port 5173)
3. Cek `.env.local` frontend: `VITE_API_BASE_URL=/api`
4. Pastikan Vite proxy mengarah ke `http://127.0.0.1:8080`
5. Coba login dengan akun demo

### 14.2 Network Error

1. Pastikan backend PHP server berjalan di port 8080
2. Cek tidak ada proses lain yang menduduki port 8080
3. Refresh browser (Ctrl+Shift+R)

### 14.3 Menu Tidak Langsung Berganti

- Refresh halaman — ini adalah bug yang sudah diperbaiki : pastikan sudah menggunakan versi terbaru
- Jika masih terjadi, laporkan ke tim development

### 14.4 Form Tidak Bisa Submit

1. Pastikan field bertanda `*` (wajib) sudah diisi
2. Untuk field lookup (dropdown pasien/dokter), pastikan sudah memilih dari daftar
3. Untuk odontogram, pastikan minimal satu gigi ditandai
4. Cek pesan error di bawah form

### 14.5 Gambar Tidak Terupload

1. Pastikan format JPG atau PNG
2. Ukuran maksimal 2MB
3. Jika gambar besar, kompres terlebih dahulu

---

## Support

- **Email**: support@mydentcare.com
- **Enterprise**: enterprise@mydentcare.com
- **Repository**: https://github.com/ifansetiawan27/My-Dent-Care
- **Dokumentasi API**: OpenAPI 3.1 (dalam pengembangan)

---

*Dokumen ini dibuat berdasarkan arsitektur dan implementasi terkini per 25 Agustus 2026.*