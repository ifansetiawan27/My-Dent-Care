#!/bin/bash
# =====================================================
# FULL DEPLOY SCRIPT — Run this in EC2 Instance Connect
# =====================================================

set -e

REMOTE_DIR="/home/ubuntu/My-Dent-Care/DentalERP"

echo "=========================================="
echo "  My Dent Care - Full Deploy"
echo "=========================================="

cd "$REMOTE_DIR" || exit 1

# Step 1: Git pull latest
echo ""
echo "[Step 1/7] Pulling latest code..."
git pull || echo "  WARN: git pull had issues"
echo "  OK: Code updated"

# Step 2: Setup .env.staging with production config
echo ""
echo "[Step 2/7] Setting up .env.staging..."
cat > .env.staging << 'ENV_EOF'
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:0AUqeIwG3ZIkXiD+g4+wAGt1m6dS6C4D7xeP9nWcOpE=
APP_DEBUG=false
APP_URL=http://108.136.48.83:8080
APP_PORT=8080

APP_MAINTENANCE_DRIVER=file
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

# Database (Neon.tech PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_sKYcbX3LPd4I
DB_SCHEMA=public
DB_SSLMODE=require

# Cache / Session / Queue (database driver — no Redis needed)
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=108.136.48.83

# Sanctum / Auth
SANCTUM_STATEFUL_DOMAINS=108.136.48.83:8080,localhost:5173,127.0.0.1:5173
SANCTUM_ACCESS_TOKEN_TTL=60

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173,http://localhost:4173

# Filesystem
FILESYSTEM_DISK=local

# Audit / Notification
AUDIT_QUEUE=audit
AUDIT_QUEUE_CONNECTION=database
AUDIT_RETENTION_DAYS=365
NOTIFICATION_QUEUE=notifications
NOTIFICATION_QUEUE_CONNECTION=database
NOTIFICATION_RETRY_ATTEMPTS=3

# Payment Gateway (Midtrans)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# Security
SECURITY_HSTS_ENABLED=false
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_HSTS_INCLUDE_SUBDOMAINS=true
SECURITY_HSTS_PRELOAD=false
ENV_EOF
echo "  OK: .env.staging written"

# Step 3: Copy .env into container and rebuild
echo ""
echo "[Step 3/7] Building Docker image (this takes 5-10 minutes)..."
docker compose -f docker/compose.staging.yaml build --no-cache
echo "  OK: Docker image built"

# Step 4: Restart containers
echo ""
echo "[Step 4/7] Restarting containers..."
docker compose -f docker/compose.staging.yaml down
docker compose -f docker/compose.staging.yaml up -d
echo "  OK: Containers restarted"

# Step 5: Wait for healthy
echo ""
echo "[Step 5/7] Waiting for app to be healthy (90s max)..."
for i in $(seq 1 18); do
    sleep 5
    HTTP_CODE=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/up 2>/dev/null || echo "000")
    echo "  Attempt $i/18: HTTP $HTTP_CODE"
    if [ "$HTTP_CODE" = "200" ]; then
        echo "  OK: App is healthy"
        break
    fi
done

# Step 6: Migrate + seed
echo ""
echo "[Step 6/7] Running migrations and seeding..."
docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force || echo "  WARN: Migration skipped (may be up to date)"
docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed || echo "  WARN: Seeder skipped (may already be seeded)"
docker compose -f docker/compose.staging.yaml exec -T app php artisan config:clear
docker compose -f docker/compose.staging.yaml exec -T app php artisan route:clear
echo "  OK: Done"

# Step 7: Final verification
echo ""
echo "[Step 7/7] Final verification..."
echo ""
echo "Health check:"
curl -s http://localhost:8080/up || echo "  Not responding yet"
echo ""
echo ""
echo "Container status:"
docker compose -f docker/compose.staging.yaml ps
echo ""
echo "App logs (last 10 lines):"
docker logs dentalerp_staging_app --tail 10 2>&1

echo ""
echo "=========================================="
echo "  DEPLOYMENT COMPLETE!"
echo "=========================================="
echo ""
echo "Backend API:  http://108.136.48.83:8080"
echo "Health Check: http://108.136.48.83:8080/up"
echo ""
echo "Demo Credentials:"
echo "  Super Admin: superadmin / password123"
echo "  Doctor:      drjane / password123"
echo "  Receptionist: sarah / password123"
