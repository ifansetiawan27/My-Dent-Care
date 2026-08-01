# User Business Rules

## Relationships

- User wajib memiliki Organization.
- User wajib memiliki Branch.
- Branch yang ditetapkan harus berada di dalam Organization yang sama.

## Columns

- `username` harus unik di seluruh sistem.
- `email` harus unik di seluruh sistem.
- `employee_code` harus unik di seluruh sistem.
- `password` wajib di-hash (bcrypt) — tidak boleh disimpan dalam bentuk plaintext.
- `last_login_at` otomatis diperbarui setiap kali login berhasil.

## Status

| Value      | Description                       |
|------------|-----------------------------------|
| `active`   | User aktif dan dapat login        |
| `inactive` | User dinonaktifkan, tidak dapat login |

- User dapat dinonaktifkan kapan saja oleh Administrator.
- User dengan `status = inactive` tidak diperbolehkan login.

## Delete Rules

- User tidak boleh dihapus permanen apabila masih memiliki transaksi terkait:
  - Appointment
  - Treatment
  - EMR (Electronic Medical Record)
  - Finance Transaction
- Soft delete digunakan untuk menonaktifkan user secara aman tanpa menghapus riwayat.
