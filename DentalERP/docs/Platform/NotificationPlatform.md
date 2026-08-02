# Notification Platform

## Overview

| Item        | Detail                                              |
|-------------|-----------------------------------------------------|
| Location    | `app/Platform/Notification`                         |
| Type        | Platform Service (reusable by all domains)          |
| Scope       | Global — sends notifications across the entire ERP  |
| Queue       | Laravel Queue (mandatory)                           |
| Principle   | All notifications MUST be dispatched via Queue      |

> The Notification Platform is a shared service. Every domain (Appointment, Billing, etc.)
> sends notifications through a single interface. No domain talks to Email/WhatsApp/SMS providers directly.

---

## Purpose

Menyediakan pengiriman notifikasi terpusat, multi-channel, dan non-blocking untuk seluruh ERP.
Notification Queue WAJIB menggunakan Queue Laravel agar tidak memperlambat request utama.

---

## Channels

| Channel                | Code       | Provider (example)              | Description                              |
|------------------------|------------|---------------------------------|------------------------------------------|
| **Email**              | `email`    | SMTP / Mailgun / SES            | Notifikasi via email                     |
| **WhatsApp**           | `whatsapp` | WhatsApp Business API / Twilio  | Pesan WhatsApp (reminder, konfirmasi)    |
| **SMS**                | `sms`      | Twilio / Vonage / local gateway | Pesan teks singkat                       |
| **Push Notification**  | `push`     | Firebase Cloud Messaging (FCM)  | Push ke mobile app                       |
| **In-App Notification**| `in_app`   | Database + Realtime             | Notifikasi di dalam aplikasi             |

---

## Channel Routing

Sebuah notifikasi dapat dikirim ke **satu atau beberapa channel** sekaligus.

| Use Case                     | Recommended Channels              |
|------------------------------|-----------------------------------|
| Appointment reminder         | WhatsApp, SMS, Push               |
| Appointment confirmation     | WhatsApp, Email, In-App           |
| Invoice / payment receipt    | Email, In-App                     |
| Password reset               | Email                             |
| System alert (staff)         | In-App, Push                      |
| Marketing / promo            | WhatsApp, Email                   |

---

## Database Schema — `notifications`

| Field             | Type         | Description                                        |
|-------------------|--------------|----------------------------------------------------|
| `id`              | uuid         | Ordered UUID primary key                           |
| `organization_id` | uuid         | Organization terkait                               |
| `branch_id`       | uuid         | Branch terkait (nullable)                          |
| `notifiable_type` | varchar(255) | Model penerima (User, Patient)                     |
| `notifiable_id`   | uuid         | ID penerima                                        |
| `channel`         | varchar(20)  | email / whatsapp / sms / push / in_app             |
| `type`            | varchar(100) | Jenis notifikasi (e.g. appointment_reminder)       |
| `title`           | varchar(255) | Judul notifikasi                                   |
| `body`            | text         | Isi notifikasi                                     |
| `data`            | jsonb        | Payload tambahan (deep link, metadata)             |
| `status`          | varchar(20)  | pending / sent / failed / read                     |
| `sent_at`         | timestamptz  | Waktu terkirim (nullable)                          |
| `read_at`         | timestamptz  | Waktu dibaca — untuk in-app (nullable)             |
| `failed_reason`   | text         | Alasan gagal (nullable)                            |
| `created_at`      | timestamptz  | Timestamp dibuat                                   |
| `updated_at`      | timestamptz  | Timestamp diperbarui                               |

---

## Notification Status

| Status    | Description                                    |
|-----------|------------------------------------------------|
| `pending` | Menunggu di antrian untuk dikirim              |
| `sent`    | Berhasil dikirim ke provider                   |
| `failed`  | Gagal dikirim (akan di-retry)                  |
| `read`    | Sudah dibaca penerima (khusus in-app)          |

---

## Design Principles

1. **Reusable** — satu Notification Platform dipakai seluruh domain ERP.
2. **Queue-based** — SEMUA notifikasi dikirim melalui Laravel Queue (non-blocking).
3. **Multi-channel** — satu notifikasi dapat menuju beberapa channel.
4. **Provider-agnostic** — domain tidak tahu provider mana yang dipakai.
5. **Interface-driven** — domain memanggil `NotificationServiceInterface`.
6. **Retryable** — notifikasi gagal di-retry otomatis dengan backoff.
7. **Template-based** — isi notifikasi menggunakan template yang dapat dikelola.

---

## Business Rules

1. SEMUA notifikasi WAJIB dikirim melalui Queue Laravel — tidak boleh synchronous.
2. Notifikasi gagal WAJIB di-retry (default: 3x dengan exponential backoff).
3. Setiap notifikasi WAJIB menyimpan `organization_id` untuk multi-tenant scoping.
4. Channel yang tidak dikonfigurasi untuk sebuah organization harus di-skip dengan graceful.
5. In-App notification menyimpan `read_at` untuk menandai sudah dibaca.
6. Sensitive data tidak boleh disimpan dalam kolom `body`/`data` dalam bentuk plaintext.
7. Preferensi channel penerima harus dihormati (opt-out honored).
8. Notifikasi WhatsApp/SMS ke nomor tidak valid ditandai `failed`, tidak retry tak terbatas.

---

## Integration Pattern

```
Domain Service (e.g. AppointmentService)
        │
        │  calls interface with recipient + type + channels
        ▼
NotificationServiceInterface  (app/Platform/Notification)
        │
        │  creates notification record (status: pending)
        │  dispatches to Laravel Queue
        ▼
Queue → SendNotificationJob
        │
        ├──► EmailChannel      ──► SMTP / Mailgun / SES
        ├──► WhatsAppChannel   ──► WhatsApp Business API
        ├──► SmsChannel        ──► SMS Gateway
        ├──► PushChannel       ──► Firebase FCM
        └──► InAppChannel      ──► notifications table + Realtime
```

Domain tidak pernah memanggil provider (SMTP, Twilio, FCM) secara langsung.
Semua melalui `NotificationServiceInterface`.

---

## Usage Example (Conceptual)

```php
// Domain service calls the platform interface — never a provider directly.
$notifier->send(
    to:       $patient,
    type:     'appointment_reminder',
    channels: ['whatsapp', 'sms', 'push'],
    data:     [
        'appointment_date' => $appointment->scheduled_at,
        'doctor_name'      => $doctor->name,
        'branch_name'      => $branch->name,
    ],
);
```

---

## Notes

- WhatsApp menggunakan Integration Hub (`app/Platform/IntegrationHub`) untuk koneksi ke provider.
- Channel provider dikonfigurasi per-organization (multi-tenant provider credentials).
- Template notifikasi mendukung multi-bahasa (Bahasa Indonesia default).
- Realtime in-app notification menggunakan broadcasting (Laravel Reverb / Pusher).
- Failed notification dapat dilihat dan di-resend manual oleh admin.
- Notification Platform mencatat aktivitas ke Audit Platform dan Logging Platform.
