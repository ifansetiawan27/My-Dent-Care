# STAGING SETUP — Langkah Lengkap

## Cara Jalankan (WAJIB via EC2 Instance Connect):

### 1. Connect ke Server
1. Buka https://console.aws.amazon.com/ec2/
2. Pilih instance **16.79.58.178**
3. Klik **Connect** → **EC2 Instance Connect** → **Connect**
4. Terminal browser akan terbuka

### 2. Upload Code dari Local (di terminal Windows baru)
```powershell
# Pastikan Git Bash terinstall
# Jalankan di Git Bash (bukan PowerShell biasa):
cd "C:\Users\ifan.setiawan_klikde\Documents\My Dent Care"
rsync -avz --exclude='node_modules' --exclude='vendor' --exclude='.git' \
  --exclude='storage/logs/*' --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' \
  --exclude='bootstrap/cache/*' --exclude='.env' --exclude='*.pem' \
  -e "ssh -i C:/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem -o StrictHostKeyChecking=no" \
  ./ ubuntu@16.79.58.178:/home/ubuntu/My-Dent-Care/
```

### 3. Jalankan Setup Script di EC2 Instance Connect
Copy **SELURUH** isi file `scripts/RUN_ON_SERVER.sh` dan paste ke terminal EC2 Instance Connect.

ATAU jalankan manual command ini satu per satu:

```bash
# Stop nginx yang bikin 503
sudo systemctl stop nginx
sudo systemctl disable nginx

# Install Docker kalau belum
sudo apt-get update -qq && sudo apt-get install -y -qq docker.io docker-compose-v2
sudo systemctl enable docker && sudo systemctl start docker

# Setup project
mkdir -p /home/ubuntu/My-Dent-Care
cd /home/ubuntu/My-Dent-Care/DentalERP

# Buat .env.staging
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
SANCTUM_STATEFUL_DOMAINS=16.79.58.178:8080,mydentcare.com,*.mydentcare.com
CORS_ALLOWED_ORIGINS=https://mydentcare.com,http://localhost:5173,http://16.79.58.178:8080
FILESYSTEM_DISK=local
MIDTRANS_IS_PRODUCTION=false
EOF

# Start containers
docker compose -f docker/compose.staging.yaml up -d --build
sleep 20

# Migrate + seed
docker compose -f docker/compose.staging.yaml exec app php artisan migrate:fresh --force
docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --class=DemoSeeder
docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --class=ExtendedDemoSeeder

# Test
curl http://127.0.0.1:8080/up
```

### 4. Verifikasi
Buka browser:
- **http://16.79.58.178:8080/up** → harus tampil "Application up"
- **http://16.79.58.178:8080/api/v1/auth/lookup** (POST `{"identifier":"superadmin@demodental.com"}`) → harus return org/branch data

### 5. Connect Frontend
Di Vercel dashboard → Settings → Environment Variables:
- `VITE_API_BASE_URL` = `http://16.79.58.178:8080/api`
- Redeploy

### Demo Login
- superadmin@demodental.com / password123
- drjane@demodental.com / password123
- sarah@demodental.com / password123
