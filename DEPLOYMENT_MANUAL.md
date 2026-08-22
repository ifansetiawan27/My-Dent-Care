# AWS Deployment - Manual Instructions

## Informasi Deployment

**Server AWS EC2:**
- IP: `108.136.48.83`
- User: `ubuntu`
- PEM File: `C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem`

**Database:**
- Supabase URL: `postgresql://postgres:Ifansetiawan093600@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres`

**Repository:**
- GitHub: `https://github.com/ifansetiawan27/My-Dent-Care.git`

---

## Cara Deploy (Manual via Git Bash atau PuTTY)

### Option 1: Menggunakan Git Bash

1. **Buka Git Bash**

2. **Set permission untuk file PEM:**
```bash
chmod 400 /c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem
```

3. **Test koneksi SSH:**
```bash
ssh -i /c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem ubuntu@108.136.48.83
```

4. **Jalankan deployment script:**
```bash
cd /c/Users/ifan.setiawan_klikde/Documents/"My Dent Care"
chmod +x scripts/setup_aws.sh
./scripts/setup_aws.sh \
  /c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem \
  108.136.48.83 \
  "postgresql://postgres:Ifansetiawan093600@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres"
```

5. **Jalankan initial setup:**
```bash
chmod +x scripts/aws_init.sh
./scripts/aws_init.sh \
  /c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem \
  108.136.48.83
```

6. **Test deployment:**
```bash
curl http://108.136.48.83:8080/up
```

---

### Option 2: Deployment Manual Step-by-Step (via SSH)

Jika script tidak bisa dijalankan, ikuti langkah manual ini:

**1. SSH ke server:**
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83
```

**2. Update sistem dan install dependencies:**
```bash
sudo apt-get update -y
sudo apt-get install -y git curl
```

**3. Install Docker:**
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker ubuntu
rm get-docker.sh
```

**4. Install docker-compose-plugin:**
```bash
sudo apt-get install -y docker-compose-plugin
```

**5. Clone repository:**
```bash
# Hapus folder lama jika ada
rm -rf My-Dent-Care

# Clone repository
git clone https://github.com/ifansetiawan27/My-Dent-Care.git
cd My-Dent-Care/DentalERP
```

**6. Buat file `.env.staging`:**
```bash
cat > .env.staging << 'EOF'
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:replace-with-your-app-key
APP_DEBUG=false
APP_URL=https://api.mydentcare.com
FRONTEND_URL=https://mydentcare.com

DB_CONNECTION=pgsql
DATABASE_URL=postgresql://postgres:Ifansetiawan093600@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres

SANCTUM_STATEFUL_DOMAINS=mydentcare.com
SESSION_DOMAIN=mydentcare.com

REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

FILESYSTEM_DISK=local

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
EOF
```

**7. Start Docker containers:**
```bash
sudo docker compose -f docker/compose.staging.yaml --env-file .env.staging up -d
```

**8. Generate APP_KEY:**
```bash
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan key:generate --force
```

**9. Run migrations:**
```bash
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force
```

**10. Cache config:**
```bash
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:cache
```

**11. Check status:**
```bash
sudo docker compose -f docker/compose.staging.yaml ps
```

**12. Exit SSH dan test dari komputer lokal:**
```bash
exit
curl http://108.136.48.83:8080/up
```

---

### Option 3: Menggunakan PuTTY (Windows)

1. **Convert PEM ke PPK menggunakan PuTTYgen:**
   - Buka PuTTYgen
   - Click "Load" dan pilih file `Ifansetiawan093600.pem`
   - Click "Save private key" dan simpan sebagai `.ppk`

2. **Connect menggunakan PuTTY:**
   - Host Name: `ubuntu@108.136.48.83`
   - Connection → SSH → Auth → Private key: browse file `.ppk`
   - Click "Open"

3. **Ikuti langkah manual 2-12 di atas**

---

## Management Commands

### View Logs
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83 \
  'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml logs -f'
```

### Restart Containers
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83 \
  'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml restart'
```

### Run Artisan Commands
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83 \
  'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force'
```

### Stop Containers
```bash
ssh -i C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem ubuntu@108.136.48.83 \
  'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml down'
```

---

## Setelah Deployment Berhasil

### 1. Update Frontend Configuration

Update file `frontend/.env.production`:
```env
VITE_API_BASE_URL=http://108.136.48.83:8080/api
```

### 2. Update Backend CORS (opsional, setelah frontend di-deploy)

SSH ke server dan edit `.env.staging`:
```bash
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_DOMAIN=your-frontend-domain.com
```

Kemudian restart containers.

### 3. Deploy Frontend ke Vercel/Netlify

```bash
cd frontend
vercel
# atau
netlify deploy --prod
```

### 4. Configure DNS (untuk production domain)

Jika ingin menggunakan domain custom:
- Setup DNS A record: `api.mydentcare.com` → `108.136.48.83`
- Setup SSL dengan Let's Encrypt di server

---

## Troubleshooting

### Port 8080 tidak bisa diakses
Check EC2 Security Group:
- Inbound rules harus allow port 8080 dari 0.0.0.0/0

### Docker tidak bisa start
```bash
sudo systemctl status docker
sudo systemctl start docker
```

### Database connection error
Verify Supabase URL di `.env.staging` sudah benar.

### Check container logs
```bash
sudo docker compose -f docker/compose.staging.yaml logs app
sudo docker compose -f docker/compose.staging.yaml logs queue
sudo docker compose -f docker/compose.staging.yaml logs postgres
```

---

## Endpoints Setelah Deployment

- **Health Check:** http://108.136.48.83:8080/up
- **API Base:** http://108.136.48.83:8080/api
- **Backend:** http://108.136.48.83:8080

---

**Status:** Ready untuk deployment manual via Git Bash atau SSH client
