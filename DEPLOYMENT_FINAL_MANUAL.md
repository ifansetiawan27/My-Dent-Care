# Deployment Final - Manual Steps

Karena beberapa issue dengan automation script, berikut adalah langkah manual untuk complete deployment:

## Status Saat Ini
- ✅ Docker containers sudah ter-build
- ✅ Repository sudah di-clone di server
- ❌ APP_KEY masih placeholder
- ❌ Database belum di-migrate

## Langkah Manual untuk Complete Deployment

### 1. SSH ke Server
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83
```

### 2. Masuk ke Project Directory
```bash
cd My-Dent-Care/DentalERP
```

### 3. Generate APP_KEY
```bash
# Generate random key
APP_KEY=$(openssl rand -base64 32)

# Update .env.staging file
sudo sed -i "s|APP_KEY=base64:replace-with-your-app-key|APP_KEY=base64:$APP_KEY|g" .env.staging

# Verify
grep APP_KEY .env.staging
```

### 4. Restart Containers
```bash
sudo docker compose -f docker/compose.staging.yaml restart
```

### 5. Run Migrations
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force
```

### 6. Create cache tables (untuk queue & cache driver=database)
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan queue:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan cache:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan session:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force
```

### 7. Cache Config
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:cache
```

### 8. Check Container Status
```bash
sudo docker compose -f docker/compose.staging.yaml ps
```

### 9. Test Health Check
```bash
curl http://localhost:8080/up
```

Atau dari komputer local:
```bash
curl http://108.136.48.83:8080/up
```

### 10. View Logs (jika ada error)
```bash
sudo docker compose -f docker/compose.staging.yaml logs app -f
```

Press Ctrl+C to stop

## Alternatif: One-Liner Command dari Local

Jika Anda menggunakan Git Bash di Windows:

```bash
ssh -i /c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem ubuntu@108.136.48.83 'cd My-Dent-Care/DentalERP && APP_KEY=$(openssl rand -base64 32) && sudo sed -i "s|APP_KEY=base64:replace-with-your-app-key|APP_KEY=base64:$APP_KEY|g" .env.staging && sudo docker compose -f docker/compose.staging.yaml restart && sleep 10 && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan queue:table && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan cache:table && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan session:table && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:cache && echo "Deployment completed!" && curl http://localhost:8080/up'
```

## Setelah Berhasil

Backend API akan accessible di:
- **http://108.136.48.83:8080**
- Health check: **http://108.136.48.83:8080/up**
- API endpoints: **http://108.136.48.83:8080/api/**

## Troubleshooting

### Container tidak healthy
```bash
sudo docker compose -f docker/compose.staging.yaml logs app --tail=50
```

### Database connection error
Verify Supabase URL di `.env.staging`:
```bash
grep DATABASE_URL .env.staging
```

### Permission errors
```bash
sudo docker compose -f docker/compose.staging.yaml exec app chown -R www-data:www-data storage bootstrap/cache
```

## Next Steps Setelah Backend Live

1. Update frontend `.env.production`:
```env
VITE_API_BASE_URL=http://108.136.48.83:8080/api
```

2. Deploy frontend ke Vercel atau Netlify

3. Configure DNS dan SSL (opsional untuk production)

---

**Koneksi:**
- IP: 108.136.48.83
- User: ubuntu  
- PEM: C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem
- Database: Supabase (already configured in .env.staging)
