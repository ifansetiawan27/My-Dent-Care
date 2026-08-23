# Cara SSH dan Deploy ke Server

## Masalah File PEM

File `Ifansetiawan093600.pem` tidak ditemukan di `C:\Users\ifan\Downloads\`.

## Solusi: Cari File PEM Anda

### Opsi 1: Cari di Downloads
```powershell
Get-ChildItem -Path "$env:USERPROFILE\Downloads" -Filter "*.pem"
```

### Opsi 2: Cari di seluruh User folder
```powershell
Get-ChildItem -Path "$env:USERPROFILE" -Recurse -Filter "*093600*.pem" -ErrorAction SilentlyContinue | Select-Object FullName
```

### Opsi 3: Gunakan username yang benar
Mungkin ada perbedaan username:
- Saat ini mencari di: `C:\Users\ifan\Downloads\`
- Working directory ada di: `C:\Users\ifan.setiawan_klikde\`

Coba cek:
```powershell
Test-Path "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
```

## Langkah Deploy Setelah SSH Berhasil

### 1. SSH ke Server
```bash
# Ganti [PATH_TO_PEM] dengan lokasi file .pem yang benar
ssh -i "[PATH_TO_PEM]\Ifansetiawan093600.pem" ubuntu@108.136.48.83
```

Contoh:
```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
```

### 2. Download dan Jalankan Script
```bash
# Setelah SSH berhasil, jalankan:
cd ~/My-Dent-Care
git pull origin main

# Jalankan script deployment
cd DentalERP
chmod +x ../scripts/deploy_complete.sh
../scripts/deploy_complete.sh
```

### 3. Atau Jalankan Manual Step-by-Step
```bash
# Pull changes
cd ~/My-Dent-Care
git pull origin main

# Rebuild Docker
cd DentalERP
sudo docker compose -f docker/compose.staging.yaml build --no-cache

# Restart containers
sudo docker compose -f docker/compose.staging.yaml down
sudo docker compose -f docker/compose.staging.yaml up -d

# Wait for healthy
sleep 60

# Run migrations
sudo docker compose -f docker/compose.staging.yaml exec app php artisan cache:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan session:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force

# Optimize
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:clear
sudo docker compose -f docker/compose.staging.yaml exec app php artisan cache:clear
sudo docker compose -f docker/compose.staging.yaml exec app php artisan route:cache
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:cache

# Test
curl http://localhost:8080/health
curl http://localhost:8080/api/
```

### 4. Verifikasi dari Luar Server
```bash
# Di terminal local
curl http://108.136.48.83:8080/api/
```

## Alternatif: Gunakan SSH Config

Buat file `~/.ssh/config`:
```
Host mydentcare
    HostName 108.136.48.83
    User ubuntu
    IdentityFile C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem
```

Kemudian SSH dengan:
```bash
ssh mydentcare
```

## Troubleshooting

### Permission Denied
File .pem harus memiliki permission yang benar:
```bash
# Di PowerShell
icacls "path\to\Ifansetiawan093600.pem" /inheritance:r
icacls "path\to\Ifansetiawan093600.pem" /grant:r "$env:USERNAME:(R)"
```

### File Not Found
1. Download ulang file .pem dari AWS Console
2. EC2 → Key Pairs → Actions → Download
3. Simpan di lokasi yang mudah diakses

### Connection Timeout
1. Cek Security Group di AWS Console
2. Pastikan port 22 (SSH) terbuka untuk IP Anda
3. Pastikan server masih running

---

**File Reference:**
- Script deployment: `JALANKAN_DI_SERVER.sh`
- Status: `DEPLOYMENT_STATUS.md`
- Panduan lengkap: `LANGKAH_SELANJUTNYA.md`
