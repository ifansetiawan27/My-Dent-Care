# Role & Permission Business Rules

## Package

- Permission dikelola menggunakan **Spatie Laravel Permission**.
- Guard: `sanctum`
- Semua permission mengikuti konvensi `{domain}.{action}`.

---

## Role Rules

### Super Admin

- Memiliki **seluruh permission** di semua domain dan semua organization.
- Tidak dapat dihapus.
- Tidak dapat dinonaktifkan.
- Tidak dapat dicabut role-nya.

### Owner

- Memiliki akses penuh **hanya dalam Organization miliknya sendiri**.
- Tidak dapat melihat atau mengakses data Organization lain.
- Dapat mengelola Branch, User, dan Role dalam Organization sendiri.

### Branch Manager

- Hanya dapat mengakses **Branch yang ditugaskan kepadanya**.
- Tidak dapat mengakses Branch lain dalam Organization yang sama.
- Dapat melihat User dan laporan Branch sendiri.

### Doctor / Dentist Specialist

- Hanya dapat mengakses **Pasien yang ditangani oleh dokter tersebut**.
- Tidak dapat melihat Medical Record pasien dokter lain.
- Dapat membuat, melihat, dan memperbarui EMR dan Treatment pasien sendiri.

### Nurse

- Dapat melihat dan membantu entri Medical Record.
- Tidak dapat menghapus Medical Record.
- Akses terbatas pada pasien di Branch yang sama.

### Receptionist

- Dapat mendaftarkan Pasien baru dan membuat Appointment.
- Tidak dapat mengakses Medical Record secara penuh.
- Tidak dapat mengakses data Finance.

### Cashier

- Dapat membuat dan melihat Invoice serta Transaksi pembayaran.
- **Tidak dapat mengubah Medical Record.**
- Tidak dapat mengakses data klinis pasien.

### Pharmacist

- Hanya dapat mengakses modul Pharmacy dan Inventory terkait obat.
- Tidak dapat mengakses Finance atau Medical Record.

### Laboratory

- Hanya dapat mengakses hasil pemeriksaan laboratorium.
- Tidak dapat mengakses Finance, EMR penuh, atau Appointment.

### Inventory Staff

- Dapat mengelola stok dan pengadaan.
- **Tidak dapat mengakses Finance.**
- Tidak dapat mengakses data klinis pasien.

### HR

- Dapat mengelola data Staff (User), attendance, dan payroll.
- **Tidak dapat mengakses Finance** (laporan keuangan klinik).
- Tidak dapat mengakses data klinis pasien.

### Finance

- Dapat mengakses seluruh laporan dan transaksi keuangan.
- Tidak dapat mengakses Medical Record atau data klinis.

### Marketing

- Dapat mengakses modul CRM.
- Hanya dapat melihat data pasien — tidak dapat mengubah.
- Tidak dapat mengakses Finance atau Medical Record.

### Customer Service

- Hanya dapat melihat data pasien dan CRM.
- Tidak dapat mengubah Medical Record atau data klinis.

---

## General Rules

1. Satu User dapat memiliki **lebih dari satu Role** dalam Organization yang sama.
2. Permission dapat diberikan langsung ke User **atau** melalui Role.
3. Permission langsung pada User **mengoverride** permission dari Role (Spatie behavior).
4. Role dan Permission selalu di-scope ke `guard_name = 'sanctum'`.
5. Role dan Permission **tidak boleh dibuat melalui UI** — hanya melalui Seeder.
6. Setiap modul baru wajib mendefinisikan permission-nya di Seeder sebelum production.
7. Perubahan permission otomatis membersihkan cache via `permission:cache-reset`.
8. Cross-organization data access adalah **pelanggaran keamanan** — tidak diizinkan dalam kondisi apapun.
