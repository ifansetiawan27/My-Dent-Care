# Fix Database Connection - Step by Step Guide

**Date**: 2026-08-23T16:12:00+07:00  
**Status**: ✅ Connection pooler IPv4 verified working

---

## Problem Summary

Backend di AWS EC2 (108.136.48.83) tidak bisa connect ke Supabase database karena:
- Supabase direct database hanya IPv6
- AWS EC2 tidak support IPv6
- **Solution**: Gunakan Supabase Connection Pooler (IPv4) ✅ VERIFIED

---

## Step 1: Dapatkan Connection String dari Supabase Dashboard

### 1.1 Buka Supabase Dashboard
```
https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/database
```

### 1.2 Cari "Connection Pooling" Section
Di halaman Database Settings, scroll ke bawah dan cari section:
- **"Connection Pooling"** atau
- **"Connection String"** atau  
- **"Pooler Configuration"**

### 1.3 Pilih Mode yang Benar
Untuk Laravel + migrations, pilih **"Session Mode"** atau **"Transaction Mode"**

### 1.4 Copy Connection String
Connection string akan dalam format:
```
postgresql://postgres.iccktgeijswtupjcgswx:[YOUR_PASSWORD]@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

**PENTING**: 
- Jangan construct manual
- Copy exact string dari dashboard
- Password harus sama dengan yang di `.env` Anda: `Ifansetiawan093600`

---

## Step 2: Update .env.staging di Server

### 2.1 SSH ke Server
```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
```

### 2.2 Backup File Lama
```bash
cd ~/My-Dent-Care/DentalERP
cp .env.staging .env.staging.backup
```

### 2.3 Edit .env.staging
```bash
nano .env.staging
```

### 2.4 Update DATABASE_URL Line
Ganti line DATABASE_URL dengan connection string dari dashboard:

**SEBELUM:**
```env
DATABASE_URL=postgresql://postgres:Ifansetiawan093600@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

**SESUDAH:** (paste dari dashboard)
```env
DATABASE_URL=postgresql://postgres.iccktgeijswtupjcgswx:Ifansetiawan093600@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

**Key Difference**: Format `postgres.iccktgeijswtupjcgswx` (dengan tenant identifier) vs `postgres` (tanpa)

### 2.5 Save File
- Press `Ctrl+O` → Enter (save)
- Press `Ctrl+X` (exit)

---

## Step 3: Restart Container

### 3.1 Restart Docker Container
```bash
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml restart app
```

### 3.2 Wait for Container Ready
```bash
sleep 30
```

### 3.3 Check Container Status
```bash
sudo docker compose -f docker/compose.staging.yaml ps
```

Expected output: `dentalerp_staging_app` status = `Up`

---

## Step 4: Test Database Connection

### 4.1 Test dengan Artisan
```bash
sudo docker exec dentalerp_staging_app php artisan db:show
```

**Expected output**: Database connection info (name, tables, etc.)

### 4.2 Run Migrations
```bash
sudo docker exec dentalerp_staging_app php artisan migrate --force
```

**Expected output**: 
```
Migration table created successfully.
Migrating: xxxx_create_users_table
Migrated:  xxxx_create_users_table (XX.XXms)
...
```

---

## Step 5: Verify Backend is Working

### 5.1 Check Health Endpoint
```bash
curl http://localhost:8080/up
```

**Expected output**: `{"status":"ok"}`

### 5.2 Check dari Outside
Di local machine Windows:
```powershell
curl http://108.136.48.83:8080/up
```

**Expected output**: `{"status":"ok"}`

---

## Troubleshooting

### Issue: "No tenant identifier found"
**Cause**: Connection string tidak include tenant identifier  
**Fix**: Pastikan format `postgres.iccktgeijswtupjcgswx` (DENGAN tenant)

### Issue: "Connection timeout"
**Cause**: Network issue  
**Fix**: 
```bash
# Test dari container
sudo docker exec dentalerp_staging_app ping -c 2 aws-0-ap-southeast-1.pooler.supabase.com

# Test DNS
sudo docker exec dentalerp_staging_app nslookup aws-0-ap-southeast-1.pooler.supabase.com
```

### Issue: "Authentication failed"
**Cause**: Password salah  
**Fix**: Verify password di Supabase dashboard dan pastikan sama dengan `.env.staging`

---

## Alternative: Jika Pooler Tidak Tersedia di Dashboard

### Opsi A: Enable IPv6 di AWS EC2
Ikuti panduan di `SOLUSI_IPV6_FINAL.md` - Opsi 1

### Opsi B: Contact Supabase Support
```
https://supabase.com/support
```
Request akses ke Connection Pooler atau upgrade plan jika diperlukan.

### Opsi C: Setup SSH Tunnel (Temporary)
Ikuti panduan di `SOLUSI_IPV6_FINAL.md` - Opsi 2

---

## Next Steps Setelah Database Working

1. ✅ Verify backend fully functional: `http://108.136.48.83:8080/api`
2. 🔄 Update frontend `.env.production` dengan backend URL
3. 🚀 Deploy frontend ke Vercel/Netlify
4. 🔧 Configure CORS di backend untuk frontend domain
5. ✅ Test end-to-end integration

---

## Quick Command Reference

**SSH ke server:**
```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
```

**Edit .env.staging:**
```bash
nano ~/My-Dent-Care/DentalERP/.env.staging
```

**Restart container:**
```bash
cd ~/My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml restart app
```

**Test connection:**
```bash
sudo docker exec dentalerp_staging_app php artisan db:show
```

**Run migrations:**
```bash
sudo docker exec dentalerp_staging_app php artisan migrate --force
```

---

**Connection Pooler Verified**: ✅ `aws-0-ap-southeast-1.pooler.supabase.com:5432` (IPv4: 52.77.146.31)
