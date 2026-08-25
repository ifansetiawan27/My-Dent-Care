# Live Review Roadmap - Dental ERP

**Target:** Aplikasi siap untuk live review  
**Tanggal:** 2026-08-24

---

## 🚨 Blocker Utama: Database IPv6

### Masalah
- Supabase database hanya IPv6
- AWS EC2 (108.136.48.83) tidak support IPv6
- Tidak bisa connect ke database

### Solusi (Pilih Salah Satu)

#### Opsi 1: Migrate Database ke Neon PostgreSQL ⭐ **RECOMMENDED**
**Keuntungan:**
- Support IPv4 natively
- Serverless PostgreSQL
- Free tier tersedia
- Migration mudah via `pg_dump`

**Langkah:**
1. Buat account di Neon (neon.tech)
2. Create new database project
3. Export data dari Supabase: `pg_dump`
4. Import ke Neon: `psql`
5. Update `.env` dengan connection string Neon
6. Test koneksi dari EC2

#### Opsi 2: Setup IPv6 di AWS EC2
**Keuntungan:**
- Tetap pakai Supabase

**Kekurangan:**
- Butuh reconfigure network VPC
- Lebih kompleks
- Mungkin ada biaya tambahan

**Langkah:**
1. Enable IPv6 di VPC
2. Assign IPv6 address ke EC2 instance
3. Update security groups untuk IPv6
4. Test koneksi

#### Opsi 3: Setup Database Proxy dengan IPv6
**Keuntungan:**
- Tidak perlu migrate database

**Kekurangan:**
- Butuh server tambahan dengan IPv6
- Lebih kompleks

---

## 📝 Checklist Deployment (Setelah Database Fixed)

### 1. Backend API Setup
- [ ] Fix database connection issue
- [ ] Generate production `APP_KEY`
- [ ] Update `.env` production:
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://api.mydentcare.com
  DB_CONNECTION=pgsql
  DB_HOST=<production-db-host>
  DB_DATABASE=dentalerp_production
  ```
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Run seeders untuk demo data: `php artisan db:seed`
- [ ] Setup queue worker: `php artisan queue:work`
- [ ] Setup scheduler: `* * * * * cd /var/www && php artisan schedule:run`
- [ ] Verify API health: `curl https://api.mydentcare.com/up`

### 2. Frontend Deployment
- [ ] Verify frontend di Vercel: https://mydentcare.com
- [ ] Update API endpoint di frontend `.env`:
  ```env
  VITE_API_URL=https://api.mydentcare.com
  VITE_API_BASE_URL=https://api.mydentcare.com/api/v1
  ```
- [ ] Rebuild dan redeploy frontend
- [ ] Test koneksi frontend → backend

### 3. Domain & SSL
- [ ] Setup subdomain `api.mydentcare.com` untuk backend
- [ ] Install SSL certificate (Let's Encrypt)
- [ ] Update Nginx/Apache config untuk SSL
- [ ] Test HTTPS: `https://api.mydentcare.com`

### 4. Demo Data & Testing
- [ ] Create demo organization
- [ ] Create demo users:
  - Super Admin
  - Organization Admin
  - Branch Admin
  - Dokter
  - Resepsionis
- [ ] Create sample data:
  - 5-10 patients
  - 3-5 appointments
  - 2-3 treatment records
- [ ] Test authentication flow
- [ ] Test CRUD operations untuk setiap module

### 5. Monitoring & Logging
- [ ] Setup application logging
- [ ] Setup error tracking (optional: Sentry)
- [ ] Monitor server resources (CPU, memory, disk)
- [ ] Setup database connection pooling

---

## 🎯 Quick Start (Rekomendasi)

### Langkah Tercepat ke Live Review:

1. **Migrate ke Neon Database** (30 menit)
   ```bash
   # Export from Supabase
   pg_dump -h db.iccktgeijswtupjcgswx.supabase.co -U postgres -d dentalerp > backup.sql
   
   # Import to Neon
   psql postgresql://user:pass@ep-xxx.neon.tech/dentalerp < backup.sql
   ```

2. **Update Backend Config** (10 menit)
   ```bash
   # SSH to EC2
   ssh ubuntu@108.136.48.83
   
   # Update .env
   nano /path/to/.env.staging
   # Update DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
   
   # Restart container
   docker restart dental-erp
   ```

3. **Run Migrations & Seeds** (5 menit)
   ```bash
   docker exec -it dental-erp php artisan migrate --force
   docker exec -it dental-erp php artisan db:seed --class=DemoSeeder
   ```

4. **Update Frontend** (10 menit)
   ```bash
   # Update frontend .env di Vercel dashboard
   VITE_API_URL=http://108.136.48.83:8080
   
   # Trigger rebuild
   ```

5. **Test End-to-End** (15 menit)
   - Open https://mydentcare.com
   - Login dengan demo account
   - Test CRUD operations
   - Verify API responses

---

## 📊 Status Saat Ini

| Komponen | Status | Blocker |
|----------|--------|---------|
| Frontend | ✅ Deployed (Vercel) | - |
| Backend API | ⚠️ Deployed tapi tidak bisa connect | Database IPv6 |
| Database | ❌ Tidak bisa connect | Supabase IPv6 only |
| Domain | ✅ mydentcare.com | - |
| SSL | ⚠️ Frontend only | Backend belum setup |
| CI/CD | ✅ GitHub Actions pass | - |

---

## 💡 Rekomendasi Immediate Action

1. **Hari Ini:** Migrate database ke Neon PostgreSQL
2. **Besok:** Setup SSL untuk backend API
3. **Lusa:** Live review dengan stakeholder

**Estimasi Total:** 2-3 jam kerja untuk deployment lengkap
