# Critical Blockers Fix - Manual Instructions

## Overview
Script to fix 4 critical production blockers for My Dent Care deployment.

## Prerequisites
- SSH access to server: `ssh -i "path/to/key.pem" ubuntu@108.136.48.83`
- sudo privileges on server
- Docker containers running

## Upload and Execute

### 1. Upload Script to Server
```bash
# From local machine
scp -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" scripts/fix-critical-blockers.sh ubuntu@108.136.48.83:~/
```

### 2. SSH to Server
```bash
ssh -i "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem" ubuntu@108.136.48.83
```

### 3. Run Script
```bash
chmod +x ~/fix-critical-blockers.sh
sudo ~/fix-critical-blockers.sh
```

## What the Script Does

### 1. Fix Route Prefix Issue
- Removes duplicate `/api` prefix from routes
- Changes `/api/api/v1/` to `/api/v1/`
- Clears route cache
- Verifies new routes

### 2. Setup HTTPS (Nginx Reverse Proxy)
- Installs Nginx and Certbot
- Configures Nginx as reverse proxy on port 80
- Adds CORS headers
- Prepares for SSL certificate (requires domain)

**To complete HTTPS:**
```bash
# After pointing domain to server IP
sudo certbot --nginx -d yourdomain.com
```

### 3. Automated Database Backup
- Creates backup script: `/usr/local/bin/backup-mydentcare-db.sh`
- Configures daily cron job (2 AM)
- 30-day retention policy
- Compressed SQL dumps
- Backup location: `/var/backups/mydentcare/postgresql/`

**Manual backup:**
```bash
sudo -u postgres /usr/local/bin/backup-mydentcare-db.sh
```

**Restore from backup:**
```bash
gunzip -c /var/backups/mydentcare/postgresql/dentalerp_YYYYMMDD_HHMMSS.sql.gz | psql -U dentalerp -d dentalerp
```

### 4. Error Monitoring (Sentry)
- Installs Sentry Laravel SDK
- Creates configuration file
- Captures exceptions and performance data
- SQL query tracking
- Queue job monitoring

**Complete Sentry setup:**
1. Create account: https://sentry.io
2. Create Laravel project
3. Get DSN from project settings
4. Add to `.env.staging`:
   ```env
   SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
   SENTRY_ENABLED=true
   SENTRY_TRACES_SAMPLE_RATE=0.2
   ```
5. Restart containers:
   ```bash
   cd ~/My-Dent-Care/DentalERP
   sudo docker compose -f docker/compose.staging.yaml restart
   ```

## Verification

### Check API Routes
```bash
sudo docker compose -f docker/compose.staging.yaml exec app php artisan route:list | grep "api/v1"
```

### Test API Endpoint
```bash
# Via Nginx (port 80)
curl http://108.136.48.83/api/v1/auth/login

# Direct to container (port 8080)
curl http://108.136.48.83:8080/api/v1/auth/login
```

### Check Backups
```bash
ls -lh /var/backups/mydentcare/postgresql/
```

### View Backup Logs
```bash
tail -f /var/log/mydentcare-backup.log
```

### Check Nginx Status
```bash
sudo systemctl status nginx
```

### Test Sentry
```bash
# Trigger test error
sudo docker compose -f docker/compose.staging.yaml exec app php artisan tinker
# In tinker:
throw new \Exception('Test Sentry error');
```

## Post-Fix Actions

### 1. Update Frontend Environment
```env
VITE_API_BASE_URL=http://108.136.48.83/api/v1
```

### 2. Update Backend .env.staging
```env
APP_URL=http://108.136.48.83

# After domain + SSL:
# APP_URL=https://yourdomain.com

SENTRY_LARAVEL_DSN=your-sentry-dsn
SENTRY_ENABLED=true
```

### 3. Restart Containers
```bash
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml down
sudo docker compose -f docker/compose.staging.yaml up -d
```

## Troubleshooting

### Route prefix still wrong
```bash
# Check bootstrap/app.php and routes/api.php in container
sudo docker compose -f docker/compose.staging.yaml exec app cat /var/www/bootstrap/app.php
sudo docker compose -f docker/compose.staging.yaml exec app cat /var/www/routes/api.php

# Clear all caches
sudo docker compose -f docker/compose.staging.yaml exec app php artisan optimize:clear
```

### Nginx not starting
```bash
# Check nginx config
sudo nginx -t

# Check nginx logs
sudo tail -f /var/log/nginx/error.log
```

### Backup fails
```bash
# Check PostgreSQL connection
sudo -u postgres psql -d dentalerp -c "SELECT version();"

# Check backup directory permissions
ls -ld /var/backups/mydentcare/postgresql/
```

### Sentry not capturing errors
```bash
# Check Sentry config
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:show sentry

# Check .env has DSN
sudo docker compose -f docker/compose.staging.yaml exec app grep SENTRY /var/www/.env
```

## Files Created/Modified

- `/usr/local/bin/backup-mydentcare-db.sh` - Backup script
- `/etc/cron.d/mydentcare-backup` - Cron job
- `/etc/nginx/sites-available/mydentcare` - Nginx config
- `/var/backups/mydentcare/postgresql/` - Backup directory
- `config/sentry.php` - Sentry configuration (in container)

## Security Notes

- Backup script contains database password (secure file permissions)
- Nginx configured with CORS (adjust for production domains)
- Sentry PII disabled by default
- Database backups retained 30 days (adjust as needed)

## Monitoring & Maintenance

### Daily Tasks
- Check backup logs: `tail /var/log/mydentcare-backup.log`
- Monitor Sentry dashboard for errors
- Check API health: `curl http://108.136.48.83/api/v1/`

### Weekly Tasks
- Review Sentry issues and fix critical bugs
- Check disk space: `df -h`
- Review Nginx access logs: `tail /var/log/nginx/access.log`

### Monthly Tasks
- Test backup restore procedure
- Review and clean old backups if needed
- Check SSL certificate expiry (if using Let's Encrypt)
- Review security updates: `sudo apt update && sudo apt list --upgradable`
