# Solusi Final: IPv6 Connectivity untuk Supabase

## Masalah
AWS EC2 instance tidak memiliki global IPv6 address dan tidak bisa connect ke Supabase database yang hanya menyediakan IPv6 endpoint.

## Solusi yang Tersedia

### Opsi 1: Enable IPv6 di AWS EC2 (RECOMMENDED)

**Langkah di AWS Console:**

1. **Enable IPv6 di VPC:**
   - AWS Console → VPC → Your VPC
   - Actions → Edit CIDRs → Add IPv6 CIDR block
   - Associate IPv6 CIDR block

2. **Enable IPv6 di Subnet:**
   - AWS Console → Subnets → Select subnet tempat EC2 instance
   - Actions → Edit IPv6 CIDRs
   - Add IPv6 CIDR

3. **Update Route Table:**
   - Route Tables → Select route table
   - Add route: `::/0` → Internet Gateway

4. **Update Security Group:**
   - Security Groups → Select your SG
   - Add inbound/outbound rules untuk IPv6 (`::/0`)

5. **Assign IPv6 address ke EC2 instance:**
   - EC2 → Network Interfaces → Select network interface
   - Actions → Manage IP Addresses
   - Assign new IPv6 address
   - Save

6. **Restart networking di instance:**
   ```bash
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
   
   sudo systemctl restart networking
   # Or reboot
   sudo reboot
   
   # Verify IPv6
   ip -6 addr show
   ping6 google.com
   ping6 db.iccktgeijswtupjcgswx.supabase.co
   ```

7. **Test database connection:**
   ```bash
   cd ~/My-Dent-Care/DentalERP
   sudo docker compose -f docker/compose.staging.yaml restart app
   sleep 30
   sudo docker exec dentalerp_staging_app php artisan migrate --force
   ```

---

### Opsi 2: Setup PostgreSQL SSH Tunnel (WORKAROUND)

Jika tidak bisa enable IPv6 di AWS, gunakan SSH tunnel via local machine yang support IPv6.

**Di local machine (Windows) yang support IPv6:**

1. **Install dan run SSH tunnel:**
   ```powershell
   # Test local IPv6 connectivity
   ping -6 db.iccktgeijswtupjcgswx.supabase.co
   
   # If successful, create tunnel
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" -L 0.0.0.0:15432:db.iccktgeijswtupjcgswx.supabase.co:5432 ubuntu@108.136.48.83 -N
   ```

2. **Update DATABASE_URL di server:**
   ```bash
   ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
   
   # Get Windows host IP dari EC2 (biasanya default gateway)
   ip route | grep default
   
   nano ~/My-Dent-Care/DentalERP/.env.staging
   # Update:
   DATABASE_URL=postgresql://postgres:Ifansetiawan093600@HOST_IP:15432/postgres
   
   cd ~/My-Dent-Care/DentalERP
   sudo docker compose -f docker/compose.staging.yaml restart app
   ```

---

### Opsi 3: Gunakan Database Alternatif

Deploy PostgreSQL di server yang sama:

```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83

# Install PostgreSQL
sudo apt update
sudo apt install postgresql postgresql-contrib -y

# Setup database
sudo -u postgres psql
CREATE DATABASE dentalerp;
CREATE USER dentalerp WITH PASSWORD 'Ifansetiawan093600';
GRANT ALL PRIVILEGES ON DATABASE dentalerp TO dentalerp;
\q

# Update .env.staging
nano ~/My-Dent-Care/DentalERP/.env.staging
# DATABASE_URL=postgresql://dentalerp:Ifansetiawan093600@localhost:5432/dentalerp

# Restart
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml restart app
sleep 30
sudo docker exec dentalerp_staging_app php artisan migrate --force
```

---

### Opsi 4: Gunakan Supabase Connection Pooler dengan Subdomain

**Dapatkan correct pooler URL dari Supabase dashboard:**

Connection pooler seharusnya memiliki format dengan subdomain project:

```
postgresql://postgres.iccktgeijswtupjcgswx:PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
```

**Di dashboard Supabase:**
1. Buka: https://supabase.com/dashboard/project/iccktgeijswtupjcgswx/settings/database
2. Scroll ke "Connection Pooling"
3. Jika ada, pilih "Session Mode"
4. Copy exact connection string (jangan construct manual)

Jika pooler tidak tersedia di dashboard, contact Supabase support atau upgrade plan.

---

## Rekomendasi

**Pilih Opsi 1** jika memungkinkan - Enable IPv6 di AWS adalah solusi permanen dan proper.

**Gunakan Opsi 3** untuk development sementara - Deploy PostgreSQL lokal untuk test deployment, lalu migrate ke Supabase nanti.

---

## Status Deployment Saat Ini

- ✅ Application container: Running
- ✅ Configuration: Fixed and committed
- ✅ .env mounting: Working correctly
- ❌ **Database connection: BLOCKED by IPv6 issue**

Deployment tidak bisa dilanjutkan tanpa menyelesaikan masalah IPv6 connectivity ini terlebih dahulu.
