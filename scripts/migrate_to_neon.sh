#!/bin/bash

# Migrasi dari Supabase ke Neon.tech
# Date: 2026-08-23

set -e

echo "=========================================="
echo "Migrasi Database dari Supabase ke Neon.tech"
echo "=========================================="
echo ""

# Neon Database Configuration
NEON_HOST="ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech"
NEON_DATABASE="neondb"
NEON_USERNAME="neondb_owner"
NEON_PASSWORD="npg_sKYcbX3LPd4I"
NEON_PORT="5432"
NEON_SSLMODE="require"

echo "Step 1: Backup existing .env.staging"
cd ~/My-Dent-Care/DentalERP
if [ -f .env.staging ]; then
    cp .env.staging .env.staging.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ Backup created"
else
    echo "⚠️  No existing .env.staging found"
fi

echo ""
echo "Step 2: Update .env.staging with Neon configuration"

cat > .env.staging << 'EOF'
APP_NAME=DentalERP
APP_ENV=production
APP_KEY=base64:replace-with-your-app-key
APP_DEBUG=false
APP_URL=http://108.136.48.83:8080

APP_MAINTENANCE_DRIVER=file
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database (Neon.tech PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_sKYcbX3LPd4I
DB_SCHEMA=public
DB_SSLMODE=require

# DATABASE_URL format (alternative)
DATABASE_URL=postgresql://neondb_owner:npg_sKYcbX3LPd4I@ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech/neondb?sslmode=require

# Cache / Session / Queue
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=108.136.48.83

# Redis (disabled for now)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=
REDIS_PORT=6379

# Filesystem
FILESYSTEM_DISK=local

# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Sanctum / Auth
SANCTUM_STATEFUL_DOMAINS=
SANCTUM_ACCESS_TOKEN_TTL=60

# Audit / Notification
AUDIT_QUEUE=audit
AUDIT_QUEUE_CONNECTION=sync
AUDIT_RETENTION_DAYS=365
NOTIFICATION_QUEUE=notifications
NOTIFICATION_QUEUE_CONNECTION=sync
NOTIFICATION_RETRY_ATTEMPTS=3

# Payment Gateway (Midtrans)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
EOF

echo "✅ .env.staging updated with Neon configuration"

echo ""
echo "Step 3: Test database connection from host"
echo "Testing connection to Neon database..."

if command -v psql &> /dev/null; then
    PGPASSWORD="$NEON_PASSWORD" psql -h "$NEON_HOST" -U "$NEON_USERNAME" -d "$NEON_DATABASE" -c "SELECT version();" && echo "✅ Database connection successful" || echo "❌ Database connection failed"
else
    echo "⚠️  psql not installed, skipping connection test"
fi

echo ""
echo "Step 4: Restart Docker container"
cd ~/My-Dent-Care/DentalERP
sudo docker compose -f docker/compose.staging.yaml restart app

echo "⏳ Waiting for container to be ready (30 seconds)..."
sleep 30

echo ""
echo "Step 5: Check container status"
sudo docker compose -f docker/compose.staging.yaml ps

echo ""
echo "Step 6: Test database connection from container"
sudo docker exec dentalerp_staging_app php artisan db:show || echo "⚠️  Could not show database info"

echo ""
echo "Step 7: Run database migrations"
sudo docker exec dentalerp_staging_app php artisan migrate --force

echo ""
echo "Step 8: Verify application health"
curl -s http://localhost:8080/up | grep -q "ok" && echo "✅ Application is healthy" || echo "⚠️  Application health check failed"

echo ""
echo "=========================================="
echo "Migration Complete!"
echo "=========================================="
echo ""
echo "Database: Neon.tech PostgreSQL"
echo "Host: $NEON_HOST"
echo "Database: $NEON_DATABASE"
echo "SSL: $NEON_SSLMODE"
echo ""
echo "Next steps:"
echo "1. Verify backend: http://108.136.48.83:8080/api"
echo "2. Deploy frontend with backend URL"
echo "3. Test end-to-end integration"
echo ""
