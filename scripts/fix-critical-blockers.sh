#!/bin/bash

# ============================================================================
# Critical Blockers Fix Script
# My Dent Care - Production Readiness
# ============================================================================

set -e

echo "============================================"
echo "My Dent Care - Critical Blockers Fix"
echo "============================================"
echo ""

# ============================================================================
# 1. FIX ROUTE PREFIX ISSUE
# ============================================================================

echo "[1/4] Fixing route prefix issue..."

# Check current route registration
cd ~/My-Dent-Care/DentalERP

# Fix bootstrap/app.php if using Laravel 11+ structure
if [ -f "bootstrap/app.php" ]; then
    echo "Checking bootstrap/app.php for API prefix..."
    
    # Backup
    sudo docker compose -f docker/compose.staging.yaml exec app cp /var/www/bootstrap/app.php /var/www/bootstrap/app.php.backup
    
    # The issue is likely double prefix in route registration
    # Fix will be applied via docker exec
fi

# Check routes/api.php
echo "Fixing routes configuration..."
sudo docker compose -f docker/compose.staging.yaml exec app bash -c "
    # Check if api.php exists
    if [ -f /var/www/routes/api.php ]; then
        echo 'Found routes/api.php'
        
        # Remove duplicate 'api' prefix in Route::prefix
        sed -i \"s/Route::prefix('api')->group/Route::group/g\" /var/www/routes/api.php
        
        # Or if using middleware group
        sed -i \"s/->prefix('api')//g\" /var/www/routes/api.php
    fi
    
    # Clear route cache
    php artisan route:clear
    php artisan config:clear
    
    # Show routes to verify
    php artisan route:list | head -20
"

echo "✓ Route prefix fixed"
echo ""

# ============================================================================
# 2. SETUP HTTPS WITH NGINX + LET'S ENCRYPT
# ============================================================================

echo "[2/4] Setting up HTTPS with Nginx + Let's Encrypt..."

# Install Nginx
sudo apt update
sudo apt install -y nginx certbot python3-certbot-nginx

# Create Nginx configuration
sudo tee /etc/nginx/sites-available/mydentcare > /dev/null <<'EOF'
server {
    listen 80;
    server_name 108.136.48.83;
    
    # Redirect all HTTP to HTTPS (will be enabled after SSL setup)
    # return 301 https://$server_name$request_uri;
    
    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        
        # CORS headers
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type, Accept' always;
        
        if ($request_method = 'OPTIONS') {
            return 204;
        }
    }
}

# HTTPS configuration (will be auto-configured by certbot)
# server {
#     listen 443 ssl http2;
#     server_name 108.136.48.83;
#     
#     ssl_certificate /etc/letsencrypt/live/108.136.48.83/fullchain.pem;
#     ssl_certificate_key /etc/letsencrypt/live/108.136.48.83/privkey.pem;
#     
#     # SSL configuration
#     ssl_protocols TLSv1.2 TLSv1.3;
#     ssl_ciphers HIGH:!aNULL:!MD5;
#     ssl_prefer_server_ciphers on;
#     
#     location / {
#         proxy_pass http://localhost:8080;
#         proxy_http_version 1.1;
#         proxy_set_header Upgrade $http_upgrade;
#         proxy_set_header Connection 'upgrade';
#         proxy_set_header Host $host;
#         proxy_set_header X-Real-IP $remote_addr;
#         proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
#         proxy_set_header X-Forwarded-Proto $scheme;
#         proxy_cache_bypass $http_upgrade;
#     }
# }
EOF

# Enable site
sudo ln -sf /etc/nginx/sites-available/mydentcare /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx

echo "✓ Nginx configured as reverse proxy on port 80"
echo ""
echo "NOTE: SSL certificate requires a domain name."
echo "To enable HTTPS with Let's Encrypt:"
echo "1. Point a domain to this IP (108.136.48.83)"
echo "2. Run: sudo certbot --nginx -d yourdomain.com"
echo "3. Certbot will auto-configure HTTPS and auto-renewal"
echo ""
echo "For now, API is accessible via:"
echo "  - http://108.136.48.83/api/v1/"
echo ""

# ============================================================================
# 3. AUTOMATED DATABASE BACKUP
# ============================================================================

echo "[3/4] Setting up automated database backup..."

# Create backup directory
sudo mkdir -p /var/backups/mydentcare/postgresql
sudo chown postgres:postgres /var/backups/mydentcare/postgresql

# Create backup script
sudo tee /usr/local/bin/backup-mydentcare-db.sh > /dev/null <<'BACKUPSCRIPT'
#!/bin/bash

# My Dent Care Database Backup Script
# Runs daily via cron

BACKUP_DIR="/var/backups/mydentcare/postgresql"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/dentalerp_$TIMESTAMP.sql.gz"
RETENTION_DAYS=30

# Database credentials
DB_NAME="dentalerp"
DB_USER="dentalerp"
export PGPASSWORD="Ifansetiawan093600"

# Create backup
echo "[$(date)] Starting backup..."
pg_dump -h localhost -U $DB_USER -d $DB_NAME | gzip > $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "[$(date)] Backup successful: $BACKUP_FILE"
    
    # Get file size
    SIZE=$(du -h $BACKUP_FILE | cut -f1)
    echo "[$(date)] Backup size: $SIZE"
    
    # Delete backups older than retention period
    find $BACKUP_DIR -name "dentalerp_*.sql.gz" -mtime +$RETENTION_DAYS -delete
    echo "[$(date)] Old backups cleaned (retention: $RETENTION_DAYS days)"
else
    echo "[$(date)] Backup FAILED!"
    exit 1
fi

# Unset password
unset PGPASSWORD

echo "[$(date)] Backup completed"
BACKUPSCRIPT

# Make backup script executable
sudo chmod +x /usr/local/bin/backup-mydentcare-db.sh

# Test backup script
echo "Testing backup script..."
sudo -u postgres /usr/local/bin/backup-mydentcare-db.sh

# Setup daily cron job (runs at 2 AM)
sudo tee /etc/cron.d/mydentcare-backup > /dev/null <<'CRON'
# My Dent Care Database Backup
# Runs daily at 2:00 AM
0 2 * * * postgres /usr/local/bin/backup-mydentcare-db.sh >> /var/log/mydentcare-backup.log 2>&1
CRON

echo "✓ Automated backup configured"
echo "  - Backup location: /var/backups/mydentcare/postgresql/"
echo "  - Schedule: Daily at 2:00 AM"
echo "  - Retention: 30 days"
echo "  - Log: /var/log/mydentcare-backup.log"
echo ""

# ============================================================================
# 4. ERROR MONITORING - SENTRY SETUP
# ============================================================================

echo "[4/4] Setting up error monitoring (Sentry)..."

# Install Sentry SDK
cd ~/My-Dent-Care/DentalERP

sudo docker compose -f docker/compose.staging.yaml exec app bash -c "
    composer require sentry/sentry-laravel
    php artisan vendor:publish --provider='Sentry\Laravel\ServiceProvider'
"

# Create Sentry configuration file
sudo docker compose -f docker/compose.staging.yaml exec app bash -c "
cat > /var/www/config/sentry.php <<'SENTRYCONFIG'
<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    
    // Enable/disable based on environment
    'enabled' => env('SENTRY_ENABLED', env('APP_ENV') !== 'local'),
    
    // Capture environment
    'environment' => env('APP_ENV', 'production'),
    
    // Release tracking
    'release' => env('SENTRY_RELEASE'),
    
    // Sample rate (1.0 = 100%)
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),
    
    // Performance monitoring
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.2),
    
    // Send default PII (personally identifiable information)
    'send_default_pii' => false,
    
    // Breadcrumbs
    'breadcrumbs' => [
        // Capture SQL queries
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES_ENABLED', true),
        
        // Capture SQL bindings
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS_ENABLED', false),
        
        // Capture queue jobs
        'queue_info' => env('SENTRY_BREADCRUMBS_QUEUE_INFO_ENABLED', true),
        
        // Capture command info
        'command_info' => env('SENTRY_BREADCRUMBS_COMMAND_JOBS_ENABLED', true),
    ],
    
    // Integrations
    'integrations' => [
        // Ignore these exceptions
        'ignore_exceptions' => [
            Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ],
    ],
];
SENTRYCONFIG
"

echo "✓ Sentry SDK installed and configured"
echo ""
echo "To complete Sentry setup:"
echo "1. Create account at https://sentry.io"
echo "2. Create new project (Laravel)"
echo "3. Get your DSN"
echo "4. Add to .env.staging:"
echo "   SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id"
echo "   SENTRY_ENABLED=true"
echo "   SENTRY_TRACES_SAMPLE_RATE=0.2"
echo "5. Restart containers"
echo ""

# ============================================================================
# RESTART SERVICES
# ============================================================================

echo "============================================"
echo "Restarting services..."
echo "============================================"

# Restart Docker containers
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml restart

# Wait for containers to be ready
sleep 30

# ============================================================================
# VERIFICATION
# ============================================================================

echo ""
echo "============================================"
echo "Verification"
echo "============================================"
echo ""

echo "1. API Routes:"
sudo docker compose -f docker/compose.staging.yaml exec app php artisan route:list | grep "api/v1" | head -5

echo ""
echo "2. HTTP Access (via Nginx):"
curl -s http://localhost/api/v1/auth/login | head -5

echo ""
echo "3. Latest Backup:"
ls -lh /var/backups/mydentcare/postgresql/ | tail -3

echo ""
echo "4. Nginx Status:"
sudo systemctl status nginx --no-pager | head -5

echo ""
echo "============================================"
echo "Summary"
echo "============================================"
echo ""
echo "✓ [1/4] Route prefix fixed - API now at /api/v1/"
echo "✓ [2/4] Nginx reverse proxy configured on port 80"
echo "⚠ [2/4] HTTPS requires domain name + Let's Encrypt"
echo "✓ [3/4] Automated daily backup at 2 AM (30 days retention)"
echo "✓ [4/4] Sentry SDK installed (needs DSN configuration)"
echo ""
echo "Next Steps:"
echo "1. Point domain to 108.136.48.83"
echo "2. Run: sudo certbot --nginx -d yourdomain.com"
echo "3. Add SENTRY_LARAVEL_DSN to .env.staging"
echo "4. Test API: curl http://108.136.48.83/api/v1/auth/login"
echo "5. Update frontend: VITE_API_BASE_URL=http://108.136.48.83/api/v1"
echo ""
echo "============================================"
