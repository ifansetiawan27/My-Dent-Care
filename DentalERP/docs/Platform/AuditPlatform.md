# Audit Platform

## Overview

| Item        | Detail                                          |
|-------------|-------------------------------------------------|
| Location    | `app/Platform/Audit`                            |
| Type        | Platform Service (reusable by all domains)      |
| Scope       | Global — records activity across the entire ERP |
| Storage     | PostgreSQL (`audit_logs` table)                 |
| Principle   | Every data change MUST be recorded              |

> The Audit Platform is a shared service. Every domain (Patient, Finance, HR, etc.)
> reports activity to it through a single interface. No domain implements its own audit logic.

---

## Purpose

Semua perubahan data wajib tercatat.
Audit trail adalah sumber kebenaran untuk kepatuhan (compliance), keamanan, dan investigasi insiden.

---

## Recorded Events

| Event         | Description                                        |
|---------------|----------------------------------------------------|
| `login`       | User berhasil masuk ke sistem                      |
| `logout`      | User keluar dari sistem                            |
| `create`      | Data baru dibuat                                   |
| `update`      | Data diubah                                        |
| `delete`      | Data dihapus (soft delete)                         |
| `restore`     | Data yang terhapus dipulihkan                      |
| `export`      | Data diekspor (Excel, PDF, CSV)                    |
| `import`      | Data diimpor ke sistem                             |
| `print`       | Dokumen dicetak                                    |
| `sync`        | Sinkronisasi data dengan sistem lain               |
| `integration` | Pertukaran data dengan integrasi eksternal         |

---

## Recorded Fields

| Field             | Type         | Description                                        |
|-------------------|--------------|----------------------------------------------------|
| `user_id`         | uuid         | User yang melakukan aksi                           |
| `organization_id` | uuid         | Organization tempat aksi terjadi                   |
| `branch_id`       | uuid         | Branch tempat aksi terjadi                         |
| `module`          | varchar      | Nama modul/domain (e.g. `patient`, `finance`)      |
| `action`          | varchar      | Jenis event (login, create, update, dll)           |
| `auditable_type`  | varchar      | Class model yang terpengaruh                       |
| `auditable_id`    | uuid         | ID record yang terpengaruh                         |
| `old_value`       | jsonb        | Nilai data sebelum perubahan                       |
| `new_value`       | jsonb        | Nilai data setelah perubahan                       |
| `ip_address`      | varchar(45)  | Alamat IP (IPv4/IPv6)                              |
| `user_agent`      | text         | Browser / client user agent                        |
| `device`          | varchar      | Jenis perangkat (desktop, mobile, tablet, API)     |
| `created_at`      | timestamptz  | Timestamp kapan aksi terjadi                       |

---

## Design Principles

1. **Reusable** — satu Audit Platform dipakai seluruh domain ERP.
2. **Non-blocking** — pencatatan audit tidak boleh memperlambat request utama (gunakan Queue).
3. **Immutable** — record audit tidak boleh diubah atau dihapus.
4. **Multi-tenant** — setiap record wajib menyimpan `organization_id` dan `branch_id`.
5. **Structured diff** — `old_value` dan `new_value` disimpan sebagai JSONB untuk perbandingan.
6. **Interface-driven** — domain memanggil `AuditServiceInterface`, bukan implementasi langsung.

---

## Business Rules

1. Setiap operasi write (create, update, delete, restore) WAJIB menghasilkan audit record.
2. Audit record tidak boleh dihapus — hanya di-archive setelah masa retensi.
3. `old_value` bernilai null pada event `create`.
4. `new_value` bernilai null pada event `delete`.
5. Login dan logout dicatat tanpa `auditable_type`/`auditable_id`.
6. Sensitive fields (password, token) TIDAK boleh disimpan dalam `old_value`/`new_value`.
7. Audit record di-scope ke organization — user hanya bisa melihat audit organisasinya sendiri.
8. Pencatatan audit dijalankan melalui Queue agar non-blocking.

---

## Integration Pattern

```
Domain Service (e.g. PatientService)
        │
        │  emits event / calls interface
        ▼
AuditServiceInterface  (app/Platform/Audit)
        │
        │  dispatches to queue
        ▼
Queue → AuditLogJob → audit_logs table
```

Domain tidak pernah menulis ke `audit_logs` secara langsung.
Semua melalui `AuditServiceInterface`.

---

## Notes

- Audit records adalah kandidat untuk partitioning berdasarkan `created_at` (monthly) di production.
- Masa retensi default: 7 tahun (sesuai regulasi rekam medis Indonesia).
- Audit Platform juga mencatat aktivitas integrasi (SATUSEHAT, BPJS) untuk keperluan compliance.
- `device` dideteksi dari `user_agent` menggunakan parser di Platform layer.
