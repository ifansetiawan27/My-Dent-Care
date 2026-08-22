# FINAL DEPLOYMENT STEPS - Execute di Server

## Status
- ✅ Docker image ter-build dengan benar
- ✅ APP_KEY sudah di-generate
- ✅ DATABASE_URL sudah diupdate di .env.staging  
- ❌ Container masih load env vars lama
- ❌ Migrations belum berhasil

## Root Cause
Docker Compose `env_file` directive hanya load saat container creation pertama kali. Perubahan di .env.staging tidak otomatis ter-apply ke running containers.

## Solution: SSH ke Server dan Execute Manual

### Langkah 1: SSH ke Server
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83
```

### Langkah 2: Navigate ke Project
```bash
cd My-Dent-Care/DentalERP
```

### Langkah 3: Stop dan Remove Orphan Containers
```bash
sudo docker compose -f docker/compose.staging.yaml down --remove-orphans
```

### Langkah 4: Verify .env.staging Content
```bash
cat .env.staging | head -15
```

Pastikan melihat:
- `APP_KEY=base64:vfRBjbEJbcuX9+lpwU5MewhyyTyE2Al3FW39ApyOkvg=`
- `DATABASE_URL=postgresql://postgres:Ifansetiawan093600@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres`
- `FRONTEND_URL=https://my-dent-care-q11342jnv-blackid.vercel.app`

### Langkah 5: Start Fresh Containers
```bash
sudo docker compose -f docker/compose.staging.yaml up -d
```

### Langkah 6: Wait for Healthy Status
```bash
sleep 20
sudo docker compose -f docker/compose.staging.yaml ps
```

### Langkah 7: Verify Database Connection Inside Container
```bash
sudo docker compose -f docker/compose.staging.yaml exec app env | grep DATABASE_URL
```

Harus menunjukkan URL Supabase yang benar, BUKAN "SUPABASE_URL_PLACEHOLDER"

### Langkah 8: Run Migrations
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force
```

### Langkah 9: Create Cache/Queue/Session Tables
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan queue:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan cache:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan session:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force
```

### Langkah 10: Cache Config
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:cache
```

### Langkah 11: Test Health Check
```bash
curl http://localhost:8080/up
```

Expected output: `{"status":"ok"}` atau HTTP 200

### Langkah 12: Test from Outside (dari komputer local)
```bash
curl http://108.136.48.83:8080/up
```

---

## Alternative: One-Liner (Copy-Paste di Server)

Setelah SSH ke server, jalankan ini:

```bash
cd My-Dent-Care/DentalERP && \
sudo docker compose -f docker/compose.staging.yaml down --remove-orphans && \
sudo docker compose -f docker/compose.staging.yaml up -d && \
sleep 25 && \
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force && \
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan queue:table && \
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan cache:table && \
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan session:table && \
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force && \
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:cache && \
echo "Deployment complete" && \
curl http://localhost:8080/up
```

---

## After Deployment Success

### Backend URLs:
- API Base: `http://108.136.48.83:8080`
- Health: `http://108.136.48.83:8080/up`
- API Endpoints: `http://108.136.48.83:8080/api/*`

### Frontend Vercel:
Your frontend is already deployed at: `https://my-dent-care-q11342jnv-blackid.vercel.app`

Environment variable sudah diset di backend:
- `FRONTEND_URL=https://my-dent-care-q11342jnv-blackid.vercel.app`
- `SANCTUM_STATEFUL_DOMAINS=my-dent-care-q11342jnv-blackid.vercel.app`

### Update Frontend Environment di Vercel:
1. Go to Vercel Dashboard → your project
2. Settings → Environment Variables
3. Add: `VITE_API_BASE_URL=http://108.136.48.83:8080/api`
4. Redeploy frontend

Atau update `frontend/.env.production` locally dan redeploy:
```bash
cd frontend
vercel --prod
```

---

## Troubleshooting

### Jika migrations error:
```bash
sudo docker compose -f docker/compose.staging.yaml logs app --tail=50
```

### Jika DATABASE_URL masih salah di container:
```bash
sudo docker compose -f docker/compose.staging.yaml exec app env | grep DATABASE
```

### Force rebuild image jika perlu:
```bash
sudo docker compose -f docker/compose.staging.yaml build --no-cache
sudo docker compose -f docker/compose.staging.yaml up -d
```

---

**Next Action**: SSH ke server dan jalankan one-liner command di atas.
