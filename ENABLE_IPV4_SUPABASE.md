# Enable IPv4 di Supabase - Via Dashboard

## Cara Tercepat: Enable IPv4 dari Dashboard

Supabase sekarang menyediakan IPv4 add-on yang bisa diaktifkan dari dashboard.

### Langkah-langkah:

1. **Buka Supabase Project Settings:**
   ```
   https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/addons
   ```

2. **Cari "IPv4" Add-on:**
   - Scroll ke bagian "Add-ons" atau "Network"
   - Cari option "IPv4 Address" atau "Dedicated IPv4"

3. **Enable IPv4:**
   - Click "Enable" atau "Add"
   - Confirm jika ada billing confirmation

4. **Tunggu Provisioning (2-5 menit):**
   - Supabase akan provision IPv4 address untuk database
   - Status akan berubah dari "Provisioning" ke "Active"

5. **Dapatkan Connection String Baru:**
   - Setelah IPv4 active, buka:
     ```
     https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/database
     ```
   - Di section "Connection String", sekarang akan ada:
     - **IPv4 Connection String** (NEW)
     - IPv6 Connection String (existing)
   
   - Copy **IPv4 Connection String**

6. **Update `.env.staging` di Server:**
   ```bash
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
   
   nano ~/My-Dent-Care/DentalERP/.env.staging
   # Update DATABASE_URL dengan IPv4 connection string dari dashboard
   # Save: Ctrl+X, Y, Enter
   
   # Restart containers
   cd ~/My-Dent-Care/DentalERP
   sudo docker compose -f docker/compose.staging.yaml down
   sudo docker compose -f docker/compose.staging.yaml up -d
   
   # Wait 30 seconds
   sleep 30
   
   # Run migrations
   sudo docker exec dentalerp_staging_app php artisan cache:table
   sudo docker exec dentalerp_staging_app php artisan session:table
   sudo docker exec dentalerp_staging_app php artisan migrate --force
   
   # Optimize
   sudo docker exec dentalerp_staging_app php artisan config:clear
   sudo docker exec dentalerp_staging_app php artisan cache:clear
   sudo docker exec dentalerp_staging_app php artisan route:cache
   sudo docker exec dentalerp_staging_app php artisan config:cache
   
   # Check health
   sudo docker ps
   curl http://localhost:8080/api/
   ```

7. **Verify dari Luar:**
   ```bash
   curl http://108.136.48.83:8080/api/
   ```

---

## Jika IPv4 Add-on Tidak Tersedia di Dashboard

Jika tidak ada option untuk enable IPv4, gunakan **Supabase CLI**:

### Install Supabase CLI (di local machine):

**Windows:**
```powershell
scoop install supabase
# Or
choco install supabase
# Or download from: https://github.com/supabase/cli/releases
```

### Enable IPv4 via CLI:

```bash
# Login
supabase login

# Link project
supabase link --project-ref iccktgeijswtupjcgswx

# Enable IPv4 (if command available)
supabase projects addons enable ipv4

# Check status
supabase projects addons list
```

---

## Alternative: Connection Pooler dengan IPv4

Jika IPv4 add-on berbayar atau tidak tersedia, coba cari **Connection Pooler** di dashboard:

1. Buka: `https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/database`
2. Scroll ke section "Connection Pooling"
3. Jika ada, copy **Session Mode** connection string
4. Format biasanya: `postgresql://postgres.[PROJECT]:PASSWORD@aws-0-REGION.pooler.supabase.com:5432/postgres`

Connection pooler biasanya support IPv4.

---

## Status Saat Ini

**Blocker:** AWS EC2 tidak bisa connect ke Supabase IPv6-only database.

**Next Step:** Enable IPv4 add-on di Supabase dashboard atau via CLI, lalu update DATABASE_URL dengan IPv4 connection string.

Setelah IPv4 active, deployment bisa dilanjutkan dengan run migrations dan verify endpoints.
