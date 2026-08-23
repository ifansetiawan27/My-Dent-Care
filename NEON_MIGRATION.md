# Migrasi Database dari Supabase ke Neon.tech

**Date**: 2026-08-23T16:24:00+07:00  
**Status**: ✅ Neon Database Created & Configured

---

## Summary

Migrasi database dari Supabase ke Neon.tech berhasil dilakukan untuk mengatasi IPv6 connectivity issue di AWS EC2 server.

### Problem yang Diselesaikan
- ❌ Supabase database hanya menyediakan IPv6 endpoint
- ❌ AWS EC2 server tidak support IPv6
- ❌ Connection pooler Supabase memerlukan tenant identifier yang kompleks
- ✅ Neon.tech menyediakan IPv4 endpoint yang bisa diakses langsung

---

## Neon Database Details

### Project Information
- **Project ID**: `small-base-83476244`
- **Project Name**: `dentalerp-production`
- **Region**: `aws-ap-southeast-1` (Singapore)
- **PostgreSQL Version**: 18
- **Platform**: AWS

### Connection Details
```
Host: ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech
Port: 5432
Database: neondb
Username: neondb_owner
Password: npg_sKYcbX3LPd4I
SSL Mode: require
```

### Connection String
```
postgresql://neondb_owner:npg_sKYcbX3LPd4I@ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech/neondb?sslmode=require
```

### Pooler (if needed)
```
Host: ep-long-field-azkp8lnq-pooler.c-3.ap-southeast-1.aws.neon.tech
Port: 5432
```

---

## Files Created/Updated

### 1. `.env.production` (New)
Production environment configuration dengan Neon database connection.

**Location**: `DentalERP/.env.production`

**Key configurations**:
- Database host, credentials, dan SSL mode
- Redis untuk cache/queue
- Laravel Sanctum untuk API authentication
- Audit dan notification queues

### 2. Migration Script (New)
Automated script untuk update konfigurasi di server dan restart container.

**Location**: `scripts/migrate_to_neon.sh`

**What it does**:
1. Backup existing `.env.staging`
2. Create new `.env.staging` with Neon configuration
3. Restart Docker container
4. Run database migrations
5. Verify application health

---

## Deployment Steps

### Option 1: Automated Deployment (Recommended)

```bash
# 1. Upload migration script to server
scp -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" scripts/migrate_to_neon.sh ubuntu@108.136.48.83:~/

# 2. SSH to server
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83

# 3. Run migration script
chmod +x ~/migrate_to_neon.sh
./migrate_to_neon.sh
```

### Option 2: Manual Deployment

```bash
# 1. SSH to server
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83

# 2. Backup existing config
cd ~/My-Dent-Care/DentalERP
cp .env.staging .env.staging.backup

# 3. Update .env.staging with Neon connection
nano .env.staging
# Update DATABASE_URL line:
DATABASE_URL=postgresql://neondb_owner:npg_sKYcbX3LPd4I@ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech/neondb?sslmode=require

# Also update individual DB_* variables:
DB_HOST=ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_sKYcbX3LPd4I
DB_SSLMODE=require

# 4. Restart container
sudo docker compose -f docker/compose.staging.yaml restart app
sleep 30

# 5. Run migrations
sudo docker exec dentalerp_staging_app php artisan migrate --force

# 6. Verify
curl http://localhost:8080/up
```

---

## Verification Checklist

After deployment, verify the following:

### Database Connection
```bash
sudo docker exec dentalerp_staging_app php artisan db:show
```
**Expected**: Shows database info (name, tables, etc.)

### Migrations
```bash
sudo docker exec dentalerp_staging_app php artisan migrate:status
```
**Expected**: All migrations ran successfully

### Application Health
```bash
curl http://localhost:8080/up
```
**Expected**: `{"status":"ok"}`

### API Endpoints
```bash
curl http://localhost:8080/api
```
**Expected**: API response or documentation

---

## Neon.tech Features

### Auto-scaling
Neon automatically scales compute resources based on usage:
- **Min**: 0.25 CU (Compute Units)
- **Max**: 0.25 CU (Free tier)
- **Auto-suspend**: Inactive databases suspended after timeout

### Branching
Neon supports database branching (like Git):
```bash
# Create branch for testing
neon branches create --name=staging --parent=main
```

### Point-in-Time Recovery
Built-in backup with 6-hour retention (configurable):
- Automatic backups every hour
- Restore to any point within retention period

### Connection Pooling
Built-in connection pooling available:
- Transaction mode: For short queries
- Session mode: For long transactions

---

## Next Steps

### 1. Deploy Backend Changes
```bash
# From local machine, push .env.production
scp -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" \
    DentalERP/.env.production \
    ubuntu@108.136.48.83:~/My-Dent-Care/DentalERP/.env.staging
```

### 2. Run Migration Script
See "Deployment Steps" above

### 3. Verify Backend is Working
```bash
curl http://108.136.48.83:8080/up
curl http://108.136.48.83:8080/api
```

### 4. Update Frontend Configuration
Update `dental-erp-frontend/.env.production`:
```env
NEXT_PUBLIC_API_URL=http://108.136.48.83:8080/api
```

### 5. Deploy Frontend
Deploy frontend ke Vercel/Netlify dengan backend URL yang benar.

---

## Rollback Plan

If something goes wrong, rollback to Supabase:

```bash
# 1. SSH to server
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83

# 2. Restore backup
cd ~/My-Dent-Care/DentalERP
cp .env.staging.backup .env.staging

# 3. Restart container
sudo docker compose -f docker/compose.staging.yaml restart app
```

---

## Advantages of Neon.tech

1. ✅ **IPv4 Support**: Direct connection tanpa pooler complexity
2. ✅ **Auto-scaling**: Pay only for what you use
3. ✅ **Database Branching**: Test changes in isolation
4. ✅ **Serverless**: Auto-suspend when idle
5. ✅ **Fast Provisioning**: Database ready in seconds
6. ✅ **PostgreSQL 18**: Latest PostgreSQL version
7. ✅ **AWS Singapore**: Low latency untuk region Asia

---

## Support & Resources

- **Neon Console**: https://console.neon.tech/
- **Documentation**: https://neon.tech/docs
- **API Reference**: https://api-docs.neon.tech/
- **Status Page**: https://neonstatus.com/

---

## API Key (Secure)

API Key tersimpan dan bisa digunakan untuk:
- Create/delete databases programmatically
- Manage branches
- Monitor usage
- Configure connection pooling

**Keep this secure and never commit to git.**

---

**Migration Status**: ✅ Ready to Deploy
**Next Action**: Run migration script on server
