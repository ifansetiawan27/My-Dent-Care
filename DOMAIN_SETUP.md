# Custom Domain Setup - mydentcare.com

**Date**: 2026-08-23T18:03:00+07:00  
**Domain**: mydentcare.com  
**Hosting**: JualHosting.com (srv1.jualhosting.com)

---

## Vercel Domain Configuration

Domain `mydentcare.com` telah ditambahkan ke Vercel project `dental-erp-frontend`.

**DNS Record yang Diperlukan:**
```
Type: A
Name: @ (atau mydentcare.com)
Value: 76.76.21.98
TTL: 3600 (atau default)
```

**Atau gunakan 2 A records (Recommended):**
```
Type: A, Name: @, Value: 216.198.79.1, TTL: 3600
Type: A, Name: @, Value: 64.29.17.1, TTL: 3600
```

**Untuk subdomain www:**
```
Type: CNAME
Name: www
Value: cname.vercel-dns.com
TTL: 3600
```

---

## Langkah Konfigurasi DNS di JualHosting

### Step 1: Login ke Control Panel
1. Buka: https://file.jualhosting.com/
2. Login credentials:
   - User ID: `user507295`
   - Password: `Ifansetiawan093600`

### Step 2: Akses DNS Management
1. Di control panel, cari menu **"DNS Management"** atau **"Zone Editor"**
2. Pilih domain: `mydentcare.com`

### Step 3: Tambah A Record untuk Root Domain
1. Klik **"Add Record"** atau **"Add New Record"**
2. Isi form:
   ```
   Type: A
   Name: @ (atau kosongkan, atau mydentcare.com)
   Value/Target: 76.76.21.21
   TTL: 3600 (default)
   Priority: - (kosongkan untuk A record)
   ```
3. Klik **"Save"** atau **"Add Record"**

### Step 4: Tambah CNAME Record untuk www (Optional)
1. Klik **"Add Record"** lagi
2. Isi form:
   ```
   Type: CNAME
   Name: www
   Value/Target: cname.vercel-dns.com
   TTL: 3600
   ```
3. Klik **"Save"**

### Step 5: Hapus Record Konflik (Jika Ada)
Jika sudah ada A record atau CNAME untuk `@` atau root domain yang mengarah ke tempat lain:
1. Hapus atau edit record lama tersebut
2. Pastikan hanya ada 1 A record untuk root domain yang mengarah ke `76.76.21.21`

---

## Verifikasi Domain

### Dari Command Line (setelah DNS propagasi)
```bash
# Check A record
nslookup mydentcare.com

# Atau
dig mydentcare.com A
```

**Expected output:**
```
mydentcare.com has address 76.76.21.21
```

### Vercel Verification
Setelah DNS dikonfigurasi, Vercel akan otomatis memverifikasi domain dalam beberapa menit hingga 48 jam (tergantung propagasi DNS).

Check status verifikasi:
```bash
cd dental-erp-frontend
vercel domains verify mydentcare.com
```

---

## Alternative: Menggunakan Vercel Nameservers (Advanced)

Jika Anda ingin Vercel mengelola DNS sepenuhnya:

### Step 1: Update Nameservers di Registrar
Di tempat Anda membeli domain (registrar), ubah nameservers menjadi:
```
ns1.vercel-dns.com
ns2.vercel-dns.com
```

### Step 2: Import DNS Records
Setelah nameservers update, semua DNS akan dikelola Vercel.

**Note**: Cara ini lebih advanced dan mempengaruhi semua DNS records (email, subdomains, dll).

---

## Timeline

1. **Konfigurasi DNS**: 5-10 menit
2. **DNS Propagation**: 10 menit - 48 jam (biasanya < 1 jam)
3. **Vercel Verification**: Otomatis setelah DNS propagasi
4. **SSL Certificate**: Otomatis setelah verifikasi (gratis dari Vercel)

---

## Setelah Domain Terverifikasi

### 1. Akses Website
- https://mydentcare.com (dengan SSL)
- https://www.mydentcare.com (jika CNAME dikonfigurasi)

### 2. Redirect Vercel URL (Optional)
Setelah custom domain aktif, Vercel akan otomatis redirect dari:
- `dental-erp-frontend-730q4mfoi-blackid.vercel.app` → `mydentcare.com`

### 3. Update Backend CORS
Backend perlu dikonfigurasi untuk accept requests dari domain baru:

```bash
# SSH ke server
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83

# Edit .env.staging
sudo nano ~/My-Dent-Care/DentalERP/.env.staging

# Tambahkan domain ke SANCTUM_STATEFUL_DOMAINS
SANCTUM_STATEFUL_DOMAINS=mydentcare.com,www.mydentcare.com

# Restart container
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml restart app
```

---

## Troubleshooting

### DNS Not Propagating
```bash
# Clear local DNS cache (Windows)
ipconfig /flushdns

# Check propagation globally
https://www.whatsmydns.net/#A/mydentcare.com
```

### Domain Verification Failed
1. Pastikan A record benar: `76.76.21.21`
2. Tunggu 15-30 menit untuk propagasi
3. Check di Vercel dashboard: https://vercel.com/blackid/dental-erp-frontend/settings/domains

### SSL Certificate Not Issued
- SSL otomatis setelah domain terverifikasi
- Biasanya 1-2 menit setelah verifikasi
- Jika tidak, wait 10 minutes lalu check lagi

---

## Current Status

✅ Domain added to Vercel project  
⏳ **DNS Configuration needed** (your action required)  
⏳ Domain verification pending  
⏳ SSL certificate pending

**Next Action**: Configure DNS records di JualHosting control panel (Step 1-4 di atas)

---

## Support

- Vercel Dashboard: https://vercel.com/blackid/dental-erp-frontend/settings/domains
- JualHosting Support: Contact your hosting provider if you need help with DNS configuration
- Check domain status: `vercel domains inspect mydentcare.com`
