# Panduan Setup SSH ke EC2 Server - My Dent Care

## Step 1: Verifikasi File PEM

File private key harus ada di lokasi ini:
```
C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem
```

Cek file ada:
```powershell
Test-Path "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
```
Harus return `True`.

---

## Step 2: Setup Permission File PEM (Windows)

Di Windows, file PEM perlu permission yang benar agar SSH mau memakainya.

**Via PowerShell (Run as Administrator):**
```powershell
$pemPath = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
# Remove inherited permissions
icacls $pemPath /reset
# Grant full control to current user only
icacls $pemPath /grant:r "$($env:USERNAME):(F)"
# Remove inheritance
icacls $pemPath /inheritance:r
```

---

## Step 3: Test Koneksi SSH

**Via PowerShell atau CMD:**
```powershell
ssh -o StrictHostKeyChecking=no -o ConnectTimeout=30 -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83 "echo 'SSH connected!' && hostname && uptime"
```

**Expected output:**
```
SSH connected!
ip-xxx-xx-xx-xx
up XX:XX, X users, load average: ...
```

**Jika berhasil** → lanjut ke Step 4.

**Jika timeout** → cek Step Troubleshooting di bawah.

---

## Step 4: Setup Passwordless Sudo + Docker Access (WAJIB)

Setelah bisa SSH, jalankan **3 perintah ini** di server:

```bash
# Command 1: Passwordless sudo
echo 'ubuntu ALL=(ALL) NOPASSWD:ALL' | sudo tee /etc/sudoers.d/ubuntu-nopasswd

# Command 2: Tambah user ke group docker
sudo usermod -aG docker ubuntu

# Command 3: Verifikasi docker
docker compose version
```

**Expected output:**
```
ubuntu ALL=(ALL) NOPASSWD:ALL
Docker Compose version v2.x.x
```

---

## Step 5: Verifikasi Setup

Test sudo tanpa password:
```bash
sudo -n docker ps
```
Harus menampilkan daftar container (tanpa error `a terminal is required to read the password`).

Test tanpa sudo (jika sudah di group docker):
```bash
docker ps
```
Harus menampilkan daftar container.

---

## Step 6: Deploy via GitHub Actions

Setelah Step 4-5 berhasil:

1. Buka: https://github.com/ifansetiawan27/My-Dent-Care/actions
2. Klik **"Deploy to Staging"** di sidebar kiri
3. Klik **"Run workflow"** → pilih `main` → klik **"Run workflow"**
4. Tunggu ~10-15 menit (Docker build)
5. Lihat hasil deploy di Actions log

---

## Troubleshooting

### A. SSH Timeout / Connection Refused

**Cek 1: Server up?**
```powershell
Test-NetConnection -ComputerName 108.136.48.83 -Port 22
```
- `TcpTestSucceeded: True` = server reachable
- `TcpTestSucceeded: False` = server down / security group blocking port 22

**Cek 2: Security Group EC2**
Buka AWS Console → EC2 → Instances → pilih instance → tab "Security" → klik Security Group.
Pastikan ada inbound rule:
- Type: SSH
- Protocol: TCP
- Port: 22
- Source: 0.0.0.0/0 (atau IP Anda)

**Cek 3: Banner Exchange Timeout**
Jika TCP connect berhasil tapi SSH handshake timeout:
```powershell
ssh -v -o StrictHostKeyChecking=no -o ConnectTimeout=60 -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83 "echo test"
```
Flag `-v` akan menunjukkan di step mana koneksi gagal.

### B. Permission Denied (publickey)

File PEM salah atau permission tidak benar:
```powershell
# Cek file ada
Test-Path "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"

# Cek isi file (harus diawali -----BEGIN)
Get-Content "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" -Head 1
```

### C. Server Bukan EC2 tapi Lightsail / Instance Lain

Jika server bukan EC2 standard tapi Lightsail, user SSH mungkin bukan `ubuntu`. Coba:
- `ubuntu@108.136.48.83` (Ubuntu/Debian)
- `ec2-user@108.136.48.83` (Amazon Linux)
- `admin@108.136.48.83` (beberapa image)

---

## Quick Reference

| Item | Value |
|------|-------|
| Server IP | 108.136.48.83 |
| SSH User | ubuntu |
| PEM File | C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem |
| App Port | 8080 |
| Project Path | /home/ubuntu/My-Dent-Care/DentalERP |
| Health URL | http://108.136.48.83:8080/up |
