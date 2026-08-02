# File Storage Platform

## Overview

| Item        | Detail                                              |
|-------------|-----------------------------------------------------|
| Location    | `app/Platform/FileStorage`                          |
| Type        | Platform Service (reusable by all domains)          |
| Scope       | Global — handles all file uploads across the ERP    |
| Drivers     | Local, S3-Compatible                                |
| Principle   | Every file MUST have a UUID name — never the original filename |

> The File Storage Platform is a shared service. Every domain (Patient, Radiology, Lab, etc.)
> stores files through a single interface. No domain talks to the disk or S3 directly.

---

## Purpose

Menyediakan penyimpanan file terpusat, aman, dan konsisten untuk seluruh ERP.
Setiap file WAJIB memiliki nama UUID dan TIDAK boleh memakai nama file asli pengguna.

---

## Storage Drivers

| Driver            | Code    | Use Case                                       |
|-------------------|---------|------------------------------------------------|
| **Local**         | `local` | Development, on-premise deployment             |
| **S3-Compatible** | `s3`    | Production — AWS S3, MinIO, DigitalOcean Spaces, Wasabi |

> Driver dipilih via environment (`FILESYSTEM_DISK`) — tidak hardcode.
> Domain tidak tahu driver mana yang sedang aktif.

---

## Folder Structure

| Folder           | Purpose                                        |
|------------------|------------------------------------------------|
| `patient/`       | Foto pasien, dokumen identitas, consent form   |
| `doctor/`        | Foto dokter, sertifikat, SIP/STR               |
| `organization/`  | Logo, dokumen legal organisasi                 |
| `branch/`        | Logo cabang, foto cabang                        |
| `lab/`           | Hasil pemeriksaan laboratorium                  |
| `radiology/`     | Citra radiologi (X-ray, panoramic, CBCT)        |
| `asset/`         | Foto aset, dokumen kepemilikan                  |

### Path Convention

```
{folder}/{organization_id}/{branch_id}/{yyyy}/{mm}/{uuid}.{ext}
```

Contoh:
```
patient/01927f3e.../01927abc.../2026/08/01927def-....jpg
radiology/01927f3e.../01927abc.../2026/08/01927aaa-....dcm
```

> Path selalu di-scope ke `organization_id` dan `branch_id` untuk isolasi multi-tenant.

---

## File Metadata Schema — `files`

| Field             | Type         | Description                                    |
|-------------------|--------------|------------------------------------------------|
| `id`              | uuid         | Ordered UUID primary key (juga nama file)      |
| `organization_id` | uuid         | Organization pemilik file                      |
| `branch_id`       | uuid         | Branch pemilik file (nullable)                 |
| `fileable_type`   | varchar(255) | Model pemilik (Patient, Doctor, Asset)         |
| `fileable_id`     | uuid         | ID record pemilik                              |
| `folder`          | varchar(50)  | Folder kategori (patient, radiology, dll)      |
| `disk`            | varchar(20)  | Driver penyimpanan (local / s3)                |
| `path`            | varchar(500) | Path lengkap file di storage                   |
| `original_name`   | varchar(255) | Nama asli (disimpan sebagai metadata saja)     |
| `stored_name`     | varchar(255) | Nama UUID yang tersimpan di disk               |
| `mime_type`       | varchar(100) | Tipe MIME (image/jpeg, application/pdf)        |
| `extension`       | varchar(10)  | Ekstensi file                                  |
| `size`            | bigint       | Ukuran file dalam byte                         |
| `hash`            | varchar(64)  | SHA-256 hash untuk deduplikasi & integritas    |
| `created_by`      | uuid         | User yang mengunggah                           |
| `created_at`      | timestamptz  | Timestamp unggah                               |
| `updated_at`      | timestamptz  | Timestamp diperbarui                           |
| `deleted_at`      | timestamptz  | Soft delete timestamp                          |

> `original_name` disimpan HANYA sebagai metadata untuk display.
> File fisik di disk SELALU bernama UUID (`stored_name`).

---

## Design Principles

1. **Reusable** — satu File Storage Platform dipakai seluruh domain ERP.
2. **UUID naming** — file fisik selalu bernama UUID, tidak pernah nama asli.
3. **Driver-agnostic** — domain tidak tahu Local atau S3 yang dipakai.
4. **Multi-tenant path** — path selalu di-scope ke organization & branch.
5. **Interface-driven** — domain memanggil `FileStorageServiceInterface`.
6. **Integrity check** — setiap file memiliki SHA-256 hash.
7. **Polymorphic ownership** — file terhubung ke model apapun via `fileable`.

---

## Business Rules

1. Setiap file WAJIB memiliki nama UUID — nama asli pengguna DILARANG dipakai sebagai nama file.
2. Nama asli file hanya disimpan sebagai metadata (`original_name`) untuk display.
3. Setiap file WAJIB di-scope ke `organization_id` dalam path dan metadata.
4. Ekstensi dan MIME type WAJIB divalidasi sebelum penyimpanan (whitelist).
5. Ukuran file maksimum diatur per folder/kategori (e.g. radiologi lebih besar).
6. File dihapus menggunakan soft delete — file fisik dihapus setelah masa retensi.
7. File medis (radiology, lab, patient) mengikuti masa retensi rekam medis (7 tahun).
8. Akses file WAJIB melalui signed URL / permission check — tidak boleh public langsung.
9. File hash (SHA-256) digunakan untuk mencegah duplikasi dan verifikasi integritas.

---

## Security

| Aspect              | Rule                                                        |
|---------------------|-------------------------------------------------------------|
| File naming         | UUID only — mencegah path traversal & enumeration           |
| Access control      | Signed URL dengan expiry, atau permission-based streaming    |
| MIME validation     | Whitelist per folder — tolak executable & script            |
| Storage isolation   | Path di-scope ke organization/branch                        |
| Sensitive documents | Encrypted at rest (S3 SSE / disk encryption)                |
| Direct access       | Public direct access DILARANG untuk file medis              |

---

## Allowed File Types (per Folder)

| Folder       | Allowed Types                          | Max Size |
|--------------|----------------------------------------|----------|
| `patient/`   | jpg, jpeg, png, pdf                     | 10 MB    |
| `doctor/`    | jpg, jpeg, png, pdf                     | 10 MB    |
| `organization/` | jpg, jpeg, png, svg, pdf             | 5 MB     |
| `branch/`    | jpg, jpeg, png                          | 5 MB     |
| `lab/`       | pdf, jpg, jpeg, png                      | 20 MB    |
| `radiology/` | dcm, jpg, jpeg, png, pdf                 | 100 MB   |
| `asset/`     | jpg, jpeg, png, pdf                      | 10 MB    |

---

## Integration Pattern

```
Domain Service (e.g. RadiologyService)
        │
        │  calls interface with file + folder + owner
        ▼
FileStorageServiceInterface  (app/Platform/FileStorage)
        │
        │  1. validate MIME + size (whitelist)
        │  2. generate UUID name
        │  3. build multi-tenant path
        │  4. compute SHA-256 hash
        │  5. store to active disk
        │  6. save metadata to files table
        ▼
Storage Disk (Local / S3-Compatible)
```

Domain tidak pernah memanggil `Storage::put()` atau S3 SDK secara langsung.
Semua melalui `FileStorageServiceInterface`.

---

## Usage Example (Conceptual)

```php
// Domain service calls the platform interface — never Storage directly.
$fileStorage->store(
    file:   $uploadedFile,
    folder: 'radiology',
    owner:  $patient,        // polymorphic fileable
);
// Returns a File metadata record with UUID stored_name.
```

---

## Notes

- Retrieval file menggunakan signed URL dengan expiry (default 15 menit).
- Radiologi DICOM (`.dcm`) dapat diintegrasikan dengan PACS via Integration Hub.
- Deduplikasi opsional: jika hash sama sudah ada, referensikan file yang sama.
- Thumbnail untuk gambar di-generate via Queue (non-blocking).
- File Storage Platform mencatat aktivitas upload/delete ke Audit Platform.
- Semua upload divalidasi ulang di server — tidak percaya validasi client.
