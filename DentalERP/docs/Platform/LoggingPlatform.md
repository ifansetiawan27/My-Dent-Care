# Logging Platform

## Overview

| Item        | Detail                                          |
|-------------|-------------------------------------------------|
| Location    | `app/Platform/Logging`                          |
| Type        | Platform Service (reusable by all domains)      |
| Scope       | Global — captures logs across the entire ERP    |
| Standard    | PSR-3 / RFC 5424 log levels                     |
| Principle   | Every exception MUST be logged                  |

> The Logging Platform is a shared service. Every domain and platform service
> writes structured logs through a single interface. No domain configures its own logger.

---

## Purpose

Menyediakan pencatatan log terpusat dan terstruktur untuk seluruh ERP.
Semua exception WAJIB tercatat agar dapat diinvestigasi dan dimonitor.

---

## Log Levels

Mengikuti standar PSR-3 (RFC 5424), dari paling kritis ke paling detail:

| # | Level       | Severity  | When to Use                                              |
|---|-------------|-----------|----------------------------------------------------------|
| 1 | `emergency` | Tertinggi | Sistem tidak dapat digunakan sama sekali (total outage)  |
| 2 | `alert`     | Sangat tinggi | Perlu tindakan segera (database down, disk penuh)    |
| 3 | `critical`  | Tinggi    | Kondisi kritis (komponen utama gagal)                    |
| 4 | `error`     | Menengah-tinggi | Runtime error yang perlu diperhatikan             |
| 5 | `warning`   | Menengah  | Kejadian tidak normal namun bukan error                  |
| 6 | `notice`    | Rendah    | Kejadian normal namun signifikan                         |
| 7 | `info`      | Info      | Informasi umum (user login, operasi berhasil)            |
| 8 | `debug`     | Terendah  | Detail teknis untuk debugging (development only)         |

---

## Destinations

| Destination      | Status    | Description                                             |
|------------------|-----------|--------------------------------------------------------|
| **Daily Log**    | Wajib     | File harian di `storage/logs/laravel-YYYY-MM-DD.log`   |
| **Database**     | Wajib     | Tabel `system_logs` untuk log level ≥ `error`          |
| **External**     | Opsional  | Sentry / Datadog / ELK / Papertrail (production)       |

### Routing per Level

| Level Range              | Daily Log | Database | External |
|--------------------------|:---------:|:--------:|:--------:|
| `debug`, `info`, `notice`| ✅        | —        | —        |
| `warning`                | ✅        | ✅       | —        |
| `error` and above        | ✅        | ✅       | ✅       |

> Log level rendah (`debug`, `info`) hanya ke file untuk menghindari beban database.
> Log kritis (`error` ke atas) dikirim ke database dan external monitoring.

---

## Database Schema — `system_logs`

| Field             | Type         | Description                                    |
|-------------------|--------------|------------------------------------------------|
| `id`              | uuid         | Ordered UUID primary key                       |
| `level`           | varchar(20)  | Log level (emergency … debug)                  |
| `message`         | text         | Pesan log                                      |
| `context`         | jsonb        | Data konteks terstruktur                       |
| `channel`         | varchar(50)  | Channel/module asal log                        |
| `user_id`         | uuid         | User terkait (nullable)                        |
| `organization_id` | uuid         | Organization terkait (nullable)                |
| `branch_id`       | uuid         | Branch terkait (nullable)                      |
| `exception_class` | varchar(255) | Class exception (jika ada)                     |
| `file`            | varchar(500) | File tempat log dibuat                         |
| `line`            | integer      | Baris kode                                     |
| `trace`           | text         | Stack trace (untuk error)                      |
| `ip_address`      | varchar(45)  | Alamat IP                                      |
| `created_at`      | timestamptz  | Timestamp log                                  |

---

## Design Principles

1. **Reusable** — satu Logging Platform dipakai seluruh domain dan platform service.
2. **Structured** — log selalu menyertakan context array, bukan string mentah.
3. **Non-blocking** — penulisan log ke database/external melalui Queue.
4. **Level-routed** — destinasi log ditentukan otomatis berdasarkan level.
5. **Interface-driven** — domain memanggil `LoggerServiceInterface`.
6. **Correlation** — setiap request memiliki `request_id` untuk menelusuri log terkait.

---

## Business Rules

1. Setiap exception yang tidak tertangani WAJIB tercatat minimal level `error`.
2. Log level `error` ke atas WAJIB masuk ke database.
3. Sensitive data (password, token, credit card) TIDAK boleh masuk ke log.
4. Log `debug` hanya aktif di environment non-production.
5. Setiap log WAJIB menyertakan `channel` (nama modul asal).
6. Log yang menyertakan user WAJIB menyimpan `organization_id` dan `branch_id`.
7. Penulisan ke database dan external dijalankan melalui Queue (non-blocking).
8. Format pesan konsisten: `[Module::action] message`.

---

## Integration Pattern

```
Domain / Platform Service
        │
        │  calls interface with level + message + context
        ▼
LoggerServiceInterface  (app/Platform/Logging)
        │
        ├─────────────► Daily Log file        (always)
        │
        ├─── Queue ───► system_logs table     (level ≥ error/warning)
        │
        └─── Queue ───► External monitoring   (level ≥ error, optional)
```

---

## Usage Example (Conceptual)

```php
// Domain service calls the platform interface — never Laravel Log directly.
$logger->error('[PatientService::create] Failed to create patient.', [
    'organization_id' => $orgId,
    'branch_id'       => $branchId,
    'exception'       => $e::class,
]);
```

---

## Notes

- Global exception handler mengirim semua unhandled exception ke Logging Platform.
- `request_id` di-generate di middleware dan disertakan di setiap log untuk korelasi.
- Retensi log database: 90 hari (dapat dikonfigurasi), file log: 14 hari (Laravel default).
- Log external (Sentry, dll.) dikonfigurasi via environment — tidak hardcode.
- Logging Platform terpisah dari Audit Platform: Logging untuk teknis/error, Audit untuk perubahan data bisnis.
