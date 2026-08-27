# Deployment Guide — Staging Server (EC2)

## Status Server Saat Ini
- **IP:** 16.79.58.178
- **Status:** ⚠️ Tidak accessible (timeout)
- **Kemungkinan:** Server down / Security group block

---

## Cara Deploy ke Staging Server

### Opsi 1: Jalankan dari Windows (PowerShell)

1. **Pastikan server online** — cek di AWS Console
2. **Pastikan SSH key tersedia:**
   ```
   C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem
   ```
3. **Test koneksi SSH:**
   ```powershell
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@16.79.58.178
   ```
4. **Jika berhasil connect, jalankan deploy:**
   ```powershell
   cd "C:\Users\ifan.setiawan_klikde\Documents\My Dent Care\DentalERP"
   .\scripts\deploy-staging.ps1
   ```

### Opsi 2: Jalankan Manual via SSH

1. **SSH ke server:**
   ```bash
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@16.79.58.178
   ```

2. **Upload code dari local (terminal baru):**
   ```bash
   # Dari Windows (Git Bash)
   rsync -avz --exclude='node_modules' --exclude='vendor' --exclude='.git' \
     -e "ssh -i C:/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem" \
     "/c/Users/ifan.setiawan_klikde/Documents/My Dent Care/DentalERP/" \
     ubuntu@16.79.58.178:/home/ubuntu/My-Dent-Care/DentalERP/
   ```

3. **Di server, jalankan:**
   ```bash
   cd /home/ubuntu/My-Dent-Care/DentalERP

   # Buat .env.staging
   cat > .env.staging << 'EOF'
   APP_NAME=DentalERP
   APP_ENV=staging
   APP_KEY=base64:0AUqeIwG3ZIkXiD+g4+wAGt1m6dS6C4D7xeP9nWcOpE=
   APP_DEBUG=false
   APP_URL=http://16.79.58.178:8080

   DB_CONNECTION=pgsql
   DB_HOST=ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech
   DB_PORT=5432
   DB_DATABASE=neondb
   DB_USERNAME=neondb_owner
   DB_PASSWORD=npg_sKYcbX3LPd4I
   DB_SCHEMA=public
   DB_SSLMODE=require

   CACHE_STORE=database
   QUEUE_CONNECTION=database
   SESSION_DRIVER=cookie

   SANCTUM_STATEFUL_DOMAINS=16.79.58.178:8080,mydentcare.com,*.mydentcare.com
   CORS_ALLOWED_ORIGINS=https://mydentcare.com,http://localhost:5173,http://16.79.58.178:8080

   FILESYSTEM_DISK=local
   MIDTRANS_IS_PRODUCTION=false
   EOF

   # Build & start
   docker compose -f docker/compose.staging.yaml down
   docker compose -f docker/compose.staging.yaml up -d --build

   # Tunggu container healthy
   sleep 15

   # Migrate + seed
   docker compose -f docker/compose.staging.yaml exec app php artisan migrate:fresh --force
   docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --class=DemoSeeder
   docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --class=ExtendedDemoSeeder
   ```

---

## Verifikasi Deployment

Setelah deploy, test:

```bash
# Health check
curl http://16.79.58.178:8080/up

# Login API test
curl -X POST http://16.79.58.178:8080/api/v1/auth/lookup \
  -H "Content-Type: application/json" \
  -d '{"identifier":"superadmin@demodental.com"}'
```

---

## Demo Credentials

| Role | Email | Password |
|---|---|---|
| Super Admin | `superadmin@demodental.com` | `password123` |
| Doctor | `drjane@demodental.com` | `password123` |
| Receptionist | `sarah@demodental.com` | `password123` |

---

## Troubleshooting

### SSH Timeout
- Cek AWS Console → EC2 → Instance state = Running
- Cek Security Group → Inbound rules: port 22 (SSH) dan 8080 (HTTP) open
- Cek IP public kamu — mungkin berubah

### Docker Error
```bash
# Lihat log
docker compose -f docker/compose.staging.yaml logs app

# Restart
docker compose -f docker/compose.staging.yaml restart

# Rebuild
docker compose -f docker/compose.staging.yaml up -d --build --force-recreate
```

### Database Error
```bash
# Test connection dari container
docker compose -f docker/compose.staging.yaml exec app php artisan db:show
```

---

**Last Updated:** 2026-08-27
