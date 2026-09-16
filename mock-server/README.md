# Mock Backend — Local Preview Only

Server HTTP ringkas (Node.js murni, **tanpa dependency**) yang meniru backend
Laravel untuk keperluan preview lokal frontend.

## Kenapa perlu?

Frontend mem-proxy semua panggilan API ke `http://127.0.0.1:8080` (lihat
`frontend/vite.config.ts`). PHP/Composer/Docker tidak terinstall di mesin ini,
dan backend yang di-deploy tidak terjangkau, sehingga login dengan akun demo
selalu gagal. Mock server ini mengimplementasikan kontrak API yang dipakai
frontend (auth + list paginator) agar ketiga portal bisa dipreview.

**Peringatan:** mock ini tidak ada validasi nyata, tidak ada database, dan
menerima password demo apa pun. **Jangan pernah di-deploy.** Hanya untuk
preview lokal.

## Akun demo (dari `DentalERP/database/seeders/DemoSeeder.php`)

| Role          | Email / Username               | Password     | Portal       |
|---------------|--------------------------------|--------------|--------------|
| Super Admin   | `superadmin@demodental.com`    | `password123`| Admin        |
| Dokter        | `drjane@demodental.com`        | `password123`| Dokter       |
| Resepsionis   | `sarah@demodental.com`         | `password123`| Resepsionis  |

Permission per role mengikuti `RolePermissionSeeder.php`, yang mengendalikan
menu yang tampil di tiap portal.

## Cara menjalankan

```bash
node mock-server/server.mjs
```

Lalu buka frontend Vite (sudah berjalan di `http://localhost:5173/`) dan login
dengan akun di atas.

## Yang diimplementasikan

- `POST /api/v1/auth/lookup` — org & branch identifier
- `POST /api/v1/auth/login` — token + user + roles + permissions
- `GET  /api/v1/auth/profile` — profil + roles + permissions
- `POST /api/v1/auth/logout`
- `GET  /api/v1/appointments` — paginator (data demo relatif terhadap hari ini)
- `GET  /api/v1/patients` — paginator + search
- fallback `GET /api/v1/**` — paginator kosong (supaya halaman module tetap
  render empty-state, bukan crash)
