# Fix Database Connection - Supabase Pooler

## Masalah yang Ditemukan

1. ✅ **Container sudah bisa baca `.env`** - File sudah ter-mount dengan benar
2. ✅ **IPv4 connectivity berhasil** - Pooler resolve ke IPv4 addresses
3. ❌ **Pooler authentication gagal** - Format connection string salah

## Root Cause

Supabase Pooler memerlukan **tenant identifier** di connection string. Format yang benar harus menggunakan subdomain dengan project reference.

### Format yang Salah (sekarang)
```
postgresql://postgres:PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

### Format yang Benar (diperlukan)
Ada 3 kemungkinan format untuk Supabase pooler:

#### Option 1: Session Mode (Recommended untuk migrations)
```
postgresql://postgres.PROJECT_REF:PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

#### Option 2: Transaction Mode (Untuk production workload)
```
postgresql://postgres.PROJECT_REF:PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres?pgbouncer=true
```

#### Option 3: Direct Connection dengan IPv4 Lookup
Jika pooler tidak tersedia, gunakan IP address langsung:
```bash
# Di server, resolve IP address
nslookup db.iccktgeijswtupjcgswx.supabase.co 1.1.1.1

# Tambahkan ke /etc/hosts dengan IPv4 address
sudo bash -c 'echo "IP_ADDRESS db.iccktgeijswtupjcgswx.supabase.co" >> /etc/hosts'

# Gunakan connection string original
postgresql://postgres:PASSWORD@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres
```

## Langkah Untuk Memperbaiki

### Cara 1: Dapatkan Connection String dari Supabase Dashboard

1. **Buka Supabase Dashboard:**
   ```
   https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/database
   ```

2. **Di bagian "Connection String" atau "Connection Pooling":**
   - Pilih **"Session"** mode (untuk Laravel migrations)
   - Copy connection string yang formatnya seperti:
     ```
     postgresql://postgres.iccktgeijswtupjcgswx:[PASSWORD]@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
     ```

3. **Update `.env.staging` di server:**
   ```bash
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
   
   cd ~/My-Dent-Care/DentalERP
   nano .env.staging
   
   # Update line DATABASE_URL dengan connection string dari dashboard
   # Save: Ctrl+X, Y, Enter
   
   # Restart containers
   sudo docker compose -f docker/compose.staging.yaml down
   sudo docker compose -f docker/compose.staging.yaml up -d
   
   # Wait 30 seconds
   sleep 30
   
   # Test migrations
   sudo docker exec dentalerp_staging_app php artisan migrate --force
   ```

### Cara 2: Gunakan Direct Connection dengan /etc/hosts Workaround

Jika pooler tidak tersedia di dashboard atau masih error:

```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83

# Try to get IPv4 address dari Cloudflare DNS
nslookup db.iccktgeijswtupjcgswx.supabase.co 1.1.1.1

# Atau coba DNS lain
host -t A db.iccktgeijswtupjcgswx.supabase.co 1.1.1.1

# Jika dapat IPv4, tambahkan ke /etc/hosts
sudo nano /etc/hosts
# Tambahkan line (ganti IP_ADDRESS dengan hasil nslookup):
# IP_ADDRESS db.iccktgeijswtupjcgswx.supabase.co

# Update .env.staging kembali ke original
cd ~/My-Dent-Care/DentalERP
sed -i 's|DATABASE_URL=.*|DATABASE_URL=postgresql://postgres:Ifansetiawan093600@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres|g' .env.staging

# Restart
sudo docker compose -f docker/compose.staging.yaml down
sudo docker compose -f docker/compose.staging.yaml up -d
sleep 30
sudo docker exec dentalerp_staging_app php artisan migrate --force
```

## Verifikasi Setelah Fix

Setelah connection berhasil, jalankan:

```bash
# 1. Run migrations
sudo docker exec dentalerp_staging_app php artisan migrate --force

# 2. Create cache and session tables
sudo docker exec dentalerp_staging_app php artisan cache:table
sudo docker exec dentalerp_staging_app php artisan session:table
sudo docker exec dentalerp_staging_app php artisan migrate --force

# 3. Clear and optimize
sudo docker exec dentalerp_staging_app php artisan config:clear
sudo docker exec dentalerp_staging_app php artisan cache:clear
sudo docker exec dentalerp_staging_app php artisan route:cache
sudo docker exec dentalerp_staging_app php artisan config:cache

# 4. Check container health
sudo docker ps
# STATUS harus: Up X minutes (healthy)

# 5. Test endpoints
curl http://localhost:8080/health
curl http://localhost:8080/api/

# 6. Test dari luar
curl http://108.136.48.83:8080/api/
```

Expected result: JSON response dengan data, bukan connection error.

---

## Summary

**Project ID:** iccktgeijswtupjcgswx  
**Password:** Ifansetiawan093600  
**Server:** 108.136.48.83  
**Port:** 8080 (public) → 8000 (container)

**Next Action:** Dapatkan connection pooler string yang lengkap dari Supabase dashboard dengan format: `postgresql://postgres.PROJECT_REF:PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres`
