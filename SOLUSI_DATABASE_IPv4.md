# Solusi Database Connection - IPv4 Support

**Status:** ✅ SOLVED  
**Tanggal:** 2026-08-23T15:38:00+07:00

---

## Masalah

AWS EC2 server (108.136.48.83) hanya support IPv4, sedangkan Supabase database direct connection hanya tersedia di IPv6.

## Solusi: Gunakan Supabase Shared Pooler (Session Mode)

Supabase menyediakan **Shared Pooler (Supavisor)** yang support IPv4 untuk semua tier (Free & Paid).

### Format Connection String yang Benar

```
postgres://postgres.[PROJECT_REF]:[PASSWORD]@aws-[REGION].pooler.supabase.com:5432/postgres
```

**Key points:**
- ✅ Ada prefix `postgres.` sebelum project ref
- ✅ Host: `aws-[REGION].pooler.supabase.com` (bukan `db.[PROJECT_REF].supabase.co`)
- ✅ Port: `5432` untuk session mode
- ✅ Support IPv4 di semua tier

### Untuk Project Anda

**Project Ref:** `iccktgeijswtupjcgswx`  
**Region:** `ap-southeast-1`  
**Password:** `Ifansetiawan093600`

**Connection String:**
```
postgresql://postgres.iccktgeijswtupjcgswx:Ifansetiawan093600@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

---

## Langkah Update di Server

### 1. SSH ke Server
```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
```

### 2. Update `.env.staging`
```bash
cd ~/My-Dent-Care/DentalERP

# Backup dulu
cp .env.staging .env.staging.backup

# Edit file
nano .env.staging
```

**Update baris `DATABASE_URL` menjadi:**
```env
DATABASE_URL="postgresql://postgres.iccktgeijswtupjcgswx:Ifansetiawan093600@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres"
```

**Atau gunakan sed untuk update otomatis:**
```bash
sed -i 's|DATABASE_URL=.*|DATABASE_URL="postgresql://postgres.iccktgeijswtupjcgswx:Ifansetiawan093600@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres"|' .env.staging
```

### 3. Restart Container
```bash
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml down
sudo docker compose -f docker/compose.staging.yaml up -d
```

### 4. Verifikasi Connection
```bash
# Check container logs
sudo docker compose -f docker/compose.staging.yaml logs dental-erp-staging

# Test database connection dari dalam container
sudo docker compose -f docker/compose.staging.yaml exec dental-erp-staging php artisan db:show

# Test migrations
sudo docker compose -f docker/compose.staging.yaml exec dental-erp-staging php artisan migrate:status
```

### 5. Run Migrations (Jika Belum)
```bash
sudo docker compose -f docker/compose.staging.yaml exec dental-erp-staging php artisan migrate --force
```

---

## Verifikasi Connection dari Server (Optional)

Test koneksi langsung dari server Ubuntu:

```bash
# Install psql jika belum ada
sudo apt update
sudo apt install -y postgresql-client

# Test connection
PGPASSWORD=Ifansetiawan093600 psql -h aws-0-ap-southeast-1.pooler.supabase.com -p 5432 -U postgres.iccktgeijswtupjcgswx -d postgres -c "SELECT version();"
```

Jika berhasil, akan menampilkan versi PostgreSQL.

---

## Perbedaan Connection Modes

| Mode | Host | Port | IPv4 | Best For |
|------|------|------|------|----------|
| Direct Connection | `db.[ref].supabase.co` | 5432 | ❌ IPv6 only | Migrations (jika IPv6 tersedia) |
| **Session Mode** | `aws-[region].pooler.supabase.com` | 5432 | ✅ | **Persistent backend (EC2, VPS)** |
| Transaction Mode | `aws-[region].pooler.supabase.com` | 6543 | ✅ | Serverless functions |

**Untuk Laravel di EC2 IPv4-only → Gunakan Session Mode (port 5432)**

---

## Troubleshooting

### Jika masih gagal connect:

1. **Check firewall di Supabase Dashboard:**
   ```
   https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/database
   ```
   - Pastikan tidak ada IP whitelist yang memblokir
   - Atau tambahkan IP server: `108.136.48.83/32`

2. **Verify credentials:**
   - Username: `postgres.iccktgeijswtupjcgswx` (dengan prefix `postgres.`)
   - Password: sama dengan database password Anda
   - Database: `postgres` (default)

3. **Check dari dashboard:**
   - Buka Supabase Dashboard → Project Settings → Database
   - Klik "Connection String" → pilih "Session pooler (IPv4)"
   - Copy connection string yang diberikan

---

## Referensi

- [Supabase Database Connections](https://supabase.com/docs/guides/database/connecting-to-postgres)
- [Connection Pooling](https://supabase.com/docs/guides/database/connection-management)
- [IPv4 Support](https://supabase.com/docs/guides/database/connecting-to-postgres#how-do-you-connect-using-ipv4)

---

## Next Steps Setelah Database Connected

1. ✅ Update DATABASE_URL di `.env.staging`
2. ✅ Restart containers
3. ✅ Verify connection
4. ✅ Run migrations
5. 🔄 Fix critical blockers (lihat `CRITICAL_BLOCKERS_FIX.md`)
6. 🔄 Setup Nginx reverse proxy
7. 🔄 Configure SSL certificate
8. 🔄 Setup monitoring dan backup
