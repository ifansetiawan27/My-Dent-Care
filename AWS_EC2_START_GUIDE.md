# Panduan Lengkap — Start & Setup EC2 Server di AWS

## 1. Login ke AWS Console

1. Buka **https://aws.amazon.com/console/**
2. Klik **Sign In to the Console**
3. Masukkan **Account ID** atau **Root user email**
4. Masukkan **Password**
5. Selesaikan verifikasi (MFA jika ada)

---

## 2. Buka EC2 Dashboard

1. Setelah login, di halaman utama AWS Console, cari **"EC2"** di search bar atas
2. Klik **EC2** (atau buka langsung: https://console.aws.amazon.com/ec2/)
3. Pastikan **Region** yang benar (pojok kanan atas) — biasanya **Asia Pacific (Jakarta)** atau **Singapore**

---

## 3. Cek Status Instance

1. Di menu kiri, klik **Instances**
2. Kamu akan lihat daftar instance EC2
3. Cari instance dengan IP `16.79.58.178` (kolom **Public IPv4**)
4. Lihat kolom **Instance state**:

| Status | Arti |
|---|---|
| 🟢 **Running** | Server aktif — lanjut ke step 4 |
| 🔴 **Stopped** | Server mati — klik kanan → **Start Instance** |
| 🟡 **Pending** | Baru starting — tunggu 1-2 menit |
| ⚫ **Terminated** | Server sudah dihapus — harus buat baru |

---

## 4. Start Instance (Jika Stopped)

1. **Centang** checkbox di sebelah instance kamu
2. Klik **Instance state** (atas tabel) → **Start instance**
3. Klik **Start** untuk konfirmasi
4. Tunggu 1-3 menit sampai status jadi **Running**
5. Cek **Public IPv4** — pastikan masih `16.79.58.178` (bisa berubah jika bukan Elastic IP)

---

## 5. Cek Security Group (Firewall)

1. Klik **instance name** (link biru) untuk buka detail
2. Di tab **Details**, scroll ke **Security groups**
3. Klik nama **Security group** (link biru)
4. Di halaman Security Group, tab **Inbound rules**, pastikan ada rules ini:

| Type | Protocol | Port | Source |
|---|---|---|---|
| SSH | TCP | 22 | 0.0.0.0/0 (atau IP kamu) |
| Custom TCP | TCP | 8080 | 0.0.0.0/0 |
| HTTP | TCP | 80 | 0.0.0.0/0 (opsional) |
| HTTPS | TCP | 443 | 0.0.0.0/0 (opsional) |

**Jika rule tidak ada:**
1. Klik **Edit inbound rules**
2. Klik **Add rule**
3. Isi:
   - **Type:** Custom TCP
   - **Port range:** 8080
   - **Source:** 0.0.0.0/0 (atau IP spesifik kamu)
4. Klik **Save rules**

---

## 6. Test Koneksi dari Komputer Kamu

### Test SSH (Windows PowerShell):
```powershell
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@16.79.58.178
```

**Jika berhasil:** Kamu akan masuk ke terminal Ubuntu server.

**Jika timeout:**
- Security group belum buka port 22
- IP keypair tidak cocok
- Server belum fully started (tunggu 2-3 menit)

### Test HTTP:
```powershell
curl http://16.79.58.178:8080/up
```

**Jika berhasil:** Akan return HTML "Application up".

**Jika timeout:** Server running tapi aplikasi belum deploy/start.

---

## 7. SSH ke Server & Cek Docker

Setelah berhasil SSH:

```bash
# Cek apakah Docker berjalan
docker ps

# Jika kosong atau error, start Docker
sudo systemctl start docker

# Cek containers
docker compose -f /home/ubuntu/My-Dent-Care/DentalERP/docker/compose.staging.yaml ps
```

---

## 8. Deploy Aplikasi ke Staging

Jika containers belum jalan:

```bash
cd /home/ubuntu/My-Dent-Care/DentalERP

# Upload .env.staging (buat baru jika belum ada)
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
SESSION_LIFETIME=120

SANCTUM_STATEFUL_DOMAINS=16.79.58.178:8080,mydentcare.com,*.mydentcare.com
CORS_ALLOWED_ORIGINS=https://mydentcare.com,http://localhost:5173,http://16.79.58.178:8080

FILESYSTEM_DISK=local
MIDTRANS_IS_PRODUCTION=false
EOF

# Build & start
docker compose -f docker/compose.staging.yaml up -d --build

# Tunggu app healthy
sleep 20

# Migrate database
docker compose -f docker/compose.staging.yaml exec app php artisan migrate:fresh --force

# Seed demo data
docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --class=DemoSeeder
docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --class=ExtendedDemoSeeder
```

---

## 9. Verifikasi Deployment

### Dari Server:
```bash
curl http://16.79.58.178:8080/up
curl http://16.79.58.178:8080/api/v1/auth/lookup \
  -X POST -H "Content-Type: application/json" \
  -d '{"identifier":"superadmin@demodental.com"}'
```

### Dari Komputer Lokal:
```powershell
# Health check
curl http://16.79.58.178:8080/up

# Login test
curl -X POST http://16.79.58.178:8080/api/v1/auth/lookup `
  -H "Content-Type: application/json" `
  -d '{"identifier":"superadmin@demodental.com"}'
```

---

## 10. Troubleshooting

### Instance Stuck di "Starting"
- Tunggu 5 menit
- Jika masih stuck → **Stop** → tunggu 30 detik → **Start** lagi

### IP Public Berubah
- Instance tanpa Elastic IP akan dapat IP baru setiap stop/start
- Update `.env.staging` dengan IP baru
- Atau request Elastic IP (gratis 1 per akun)

### Docker Tidak Install
```bash
sudo apt update
sudo apt install -y docker.io docker-compose-v2
sudo usermod -aG docker ubuntu
sudo systemctl enable docker
sudo systemctl start docker
# Logout & login lagi
```

### Port 8080 Tidak Bisa Diakses
- Cek Security Group → Inbound rules → Port 8080 harus open
- Cek di server: `sudo ufw status` → jika active, `sudo ufw allow 8080`

### Database Connection Error
- Neon.tech mungkin down atau credential berubah
- Cek di https://console.neon.tech/
- Update `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` di `.env.staging`

---

## Quick Reference

| Info | Value |
|---|---|
| Server IP | `16.79.58.178` |
| SSH User | `ubuntu` |
| SSH Key | `C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem` |
| API URL | `http://16.79.58.178:8080` |
| Health URL | `http://16.79.58.178:8080/up` |
| Project Path | `/home/ubuntu/My-Dent-Care/DentalERP` |
| Compose File | `docker/compose.staging.yaml` |

---

**Last Updated:** 2026-08-27
