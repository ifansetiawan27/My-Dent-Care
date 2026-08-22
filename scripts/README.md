# AWS Deployment Scripts

Kumpulan script untuk otomatisasi deployment dan management server AWS EC2.

## Prerequisites

- Git Bash, WSL, atau Linux/Mac terminal
- File PEM key untuk EC2 instance
- IP address EC2 instance
- Supabase database URL (connection string)

## Scripts Available

### 1. setup_aws.sh - Initial Deployment

Deploy aplikasi DentalERP ke AWS EC2 dari awal.

**Usage:**
```bash
./scripts/setup_aws.sh <PEM_FILE> <AWS_IP> <SUPABASE_URL>
```

**Example:**
```bash
./scripts/setup_aws.sh ~/my-key.pem 54.123.45.67 "postgresql://postgres:password@db.supabase.co:5432/postgres"
```

**What it does:**
1. Update apt dan install dependencies (git, curl)
2. Install Docker
3. Install docker-compose-plugin
4. Clone repository dari GitHub
5. Buat file `.env.staging` dengan konfigurasi production
6. Start Docker containers
7. Update DATABASE_URL dengan Supabase URL

### 2. aws_init.sh - Post-Deployment Setup

Jalankan initial setup setelah deployment pertama kali.

**Usage:**
```bash
./scripts/aws_init.sh <PEM_FILE> <AWS_IP>
```

**What it does:**
1. Generate APP_KEY
2. Run database migrations
3. Cache configuration

### 3. aws_logs.sh - View Logs

Streaming logs dari Docker containers.

**Usage:**
```bash
./scripts/aws_logs.sh <PEM_FILE> <AWS_IP>
```

Press `Ctrl+C` to stop streaming.

### 4. aws_restart.sh - Restart Containers

Restart semua Docker containers.

**Usage:**
```bash
./scripts/aws_restart.sh <PEM_FILE> <AWS_IP>
```

### 5. aws_shell.sh - SSH to Server

Buka SSH connection ke server.

**Usage:**
```bash
./scripts/aws_shell.sh <PEM_FILE> <AWS_IP>
```

### 6. aws_artisan.sh - Run Artisan Commands

Jalankan Laravel Artisan commands di server.

**Usage:**
```bash
./scripts/aws_artisan.sh <PEM_FILE> <AWS_IP> <ARTISAN_COMMAND>
```

**Examples:**
```bash
# Run migrations
./scripts/aws_artisan.sh ~/key.pem 1.2.3.4 migrate --force

# Seed database
./scripts/aws_artisan.sh ~/key.pem 1.2.3.4 db:seed --force

# Clear cache
./scripts/aws_artisan.sh ~/key.pem 1.2.3.4 cache:clear

# List routes
./scripts/aws_artisan.sh ~/key.pem 1.2.3.4 route:list
```

## Complete Deployment Workflow

### First Time Deployment

1. **Deploy aplikasi:**
   ```bash
   ./scripts/setup_aws.sh ~/my-key.pem 54.123.45.67 "postgresql://user:pass@host:5432/db"
   ```

2. **Run initial setup:**
   ```bash
   ./scripts/aws_init.sh ~/my-key.pem 54.123.45.67
   ```

3. **Test health check:**
   ```bash
   curl http://54.123.45.67:8080/up
   ```

4. **Seed data (optional):**
   ```bash
   ./scripts/aws_artisan.sh ~/my-key.pem 54.123.45.67 db:seed --force
   ```

5. **Check logs:**
   ```bash
   ./scripts/aws_logs.sh ~/my-key.pem 54.123.45.67
   ```

### Regular Maintenance

**View logs:**
```bash
./scripts/aws_logs.sh ~/my-key.pem 54.123.45.67
```

**Restart after code update:**
```bash
./scripts/aws_restart.sh ~/my-key.pem 54.123.45.67
```

**Run migrations:**
```bash
./scripts/aws_artisan.sh ~/my-key.pem 54.123.45.67 migrate --force
```

**SSH to server:**
```bash
./scripts/aws_shell.sh ~/my-key.pem 54.123.45.67
```

## Troubleshooting

### Permission Denied for PEM file

```bash
chmod 400 ~/my-key.pem
```

### Cannot connect to server

Check EC2 security group:
- Port 22 (SSH) must be open
- Port 8080 (HTTP) must be open for API access

### Docker not found after installation

Re-login atau restart SSH session:
```bash
exit
./scripts/aws_shell.sh ~/my-key.pem 54.123.45.67
```

### Containers not starting

Check logs:
```bash
./scripts/aws_logs.sh ~/my-key.pem 54.123.45.67
```

Common issues:
- Database connection failed (check Supabase URL)
- Port already in use
- Insufficient memory

### APP_KEY not set

Generate key:
```bash
./scripts/aws_artisan.sh ~/my-key.pem 54.123.45.67 key:generate --force
```

## Environment Variables

Script `setup_aws.sh` akan membuat file `.env.staging` dengan konfigurasi berikut:

```env
APP_NAME=DentalERP
APP_ENV=staging
APP_URL=https://api.mydentcare.com
FRONTEND_URL=https://mydentcare.com

DB_CONNECTION=pgsql
DATABASE_URL=<SUPABASE_URL>

SANCTUM_STATEFUL_DOMAINS=mydentcare.com
SESSION_DOMAIN=mydentcare.com

REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

FILESYSTEM_DISK=local
```

Untuk mengubah environment variables:
1. SSH ke server
2. Edit file `.env.staging`
3. Restart containers

## Port Mapping

- **8080** → Backend API (exposed to host)
- **8000** → PHP built-in server (inside container)
- **5432** → PostgreSQL (Supabase external)
- **6379** → Redis (internal)

## Next Steps After Deployment

1. **Configure DNS** untuk domain Anda
2. **Setup SSL/TLS** dengan Let's Encrypt:
   ```bash
   sudo apt install certbot
   sudo certbot certonly --standalone -d api.mydentcare.com
   ```
3. **Setup Nginx** sebagai reverse proxy (optional)
4. **Configure monitoring** dan logging
5. **Setup automated backups**
6. **Deploy frontend** ke Vercel/Netlify

## Support

Untuk issues atau pertanyaan, buka GitHub Issues di repository project.
