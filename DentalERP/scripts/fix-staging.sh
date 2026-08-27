# fix-staging.sh - Fix 503 error on EC2 staging server
# Run this via SSH on the server:
#   ssh -i "Ifansetiawan093600.pem" ubuntu@16.79.58.178
#   Then paste this script content or run: bash <(curl -s ...)

set -e

echo "========================================"
echo "  My Dent Care - Staging Fix Script"
echo "========================================"
echo ""

PROJECT_DIR="/home/ubuntu/My-Dent-Care/DentalERP"

# Step 1: Check if nginx is blocking port 8080
echo "[1/5] Checking for port conflicts..."
NGINX_ACTIVE=$(sudo systemctl is-active nginx 2>/dev/null || echo "inactive")
echo "  Host nginx status: $NGINX_ACTIVE"

if [ "$NGINX_ACTIVE" = "active" ]; then
    echo "  ⚠️  Host nginx is running. This may conflict with our app."
    echo "  Stopping host nginx temporarily..."
    sudo systemctl stop nginx
    echo "  ✓ Host nginx stopped"
fi

# Step 2: Check Docker
echo "[2/5] Checking Docker..."
if ! docker info >/dev/null 2>&1; then
    echo "  ❌ Docker is not running!"
    echo "  Starting Docker..."
    sudo systemctl start docker
    sudo systemctl enable docker
    echo "  ✓ Docker started"
else
    echo "  ✓ Docker is running"
fi

# Step 3: Check if our containers exist
echo "[3/5] Checking containers..."
if [ ! -f "$PROJECT_DIR/docker/compose.staging.yaml" ]; then
    echo "  ❌ compose.staging.yaml not found!"
    echo "  Please upload the project files first."
    exit 1
fi

cd "$PROJECT_DIR"

CONTAINERS=$(docker compose -f docker/compose.staging.yaml ps -q 2>/dev/null | wc -l)
echo "  Found $CONTAINERS containers"

if [ "$CONTAINERS" -eq 0 ]; then
    echo "  No containers found. Building..."
    
    # Create .env.staging if not exists
    if [ ! -f ".env.staging" ]; then
        echo "  Creating .env.staging..."
        cat > .env.staging << 'EOF'
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:0AUqeIwG3ZIkXiD+g4+wAGt1m6dS6C4D7xeP9nWcOpE=
APP_DEBUG=false
APP_URL=http://16.79.58.178:8080
APP_PORT=8080

LOG_CHANNEL=stack
LOG_LEVEL=info

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
SESSION_LIFETIME=120
SESSION_DOMAIN=16.79.58.178

SANCTUM_STATEFUL_DOMAINS=16.79.58.178:8080,mydentcare.com,*.mydentcare.com
CORS_ALLOWED_ORIGINS=https://mydentcare.com,http://localhost:5173,http://16.79.58.178:8080

FILESYSTEM_DISK=local
AUDIT_QUEUE=audit
AUDIT_QUEUE_CONNECTION=database
AUDIT_RETENTION_DAYS=365
NOTIFICATION_QUEUE=notifications
NOTIFICATION_QUEUE_CONNECTION=database
NOTIFICATION_RETRY_ATTEMPTS=3

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
SECURITY_HSTS_ENABLED=false
EOF
        echo "  ✓ .env.staging created"
    fi
    
    echo "  Building and starting containers..."
    docker compose -f docker/compose.staging.yaml up -d --build
    echo "  ✓ Containers started"
else
    echo "  Restarting containers..."
    docker compose -f docker/compose.staging.yaml down
    docker compose -f docker/compose.staging.yaml up -d
    echo "  ✓ Containers restarted"
fi

# Step 4: Wait for app to be healthy
echo "[4/5] Waiting for application to be ready..."
for i in $(seq 1 30); do
    if curl -sf http://127.0.0.1:8080/up >/dev/null 2>&1; then
        echo "  ✓ Application healthy after ${i}s"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "  ⚠️  App not healthy yet. Check logs: docker compose -f docker/compose.staging.yaml logs app"
    fi
    sleep 2
done

# Step 5: Run migrations if needed
echo "[5/5] Checking database status..."
MIGRATION_CHECK=$(docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate:status 2>/dev/null | grep -c "No" || true)
if [ "$MIGRATION_CHECK" -gt 0 ]; then
    echo "  Running pending migrations..."
    docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force
    echo "  ✓ Migrations done"
else
    echo "  ✓ Database already migrated"
fi

# Check if demo data exists
USER_COUNT=$(docker compose -f docker/compose.staging.yaml exec -T app php artisan db:show 2>/dev/null | grep "users" | awk '{print $3}' || echo "0")
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "  Seeding demo data..."
    docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=DemoSeeder 2>/dev/null || true
    docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=ExtendedDemoSeeder 2>/dev/null || true
    echo "  ✓ Demo data seeded"
fi

# Final status
echo ""
echo "========================================"
echo "  ✅ Fix Complete!"
echo "========================================"
echo ""
echo "Testing API..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8080/up)
if [ "$HTTP_CODE" = "200" ]; then
    echo "  ✓ Health check: PASS (HTTP 200)"
else
    echo "  ❌ Health check: FAIL (HTTP $HTTP_CODE)"
    echo "  Check logs: docker compose -f docker/compose.staging.yaml logs app"
fi

echo ""
echo "API URL: http://16.79.58.178:8080"
echo "Health:  http://16.79.58.178:8080/up"
echo ""
echo "Demo Login:"
echo "  Email: superadmin@demodental.com"
echo "  Password: password123"
echo ""
echo "Useful commands:"
echo "  Logs:     docker compose -f docker/compose.staging.yaml logs -f app"
echo "  Shell:    docker compose -f docker/compose.staging.yaml exec app bash"
echo "  Restart:  docker compose -f docker/compose.staging.yaml restart"
echo "  DB Check: docker compose -f docker/compose.staging.yaml exec app php artisan db:show"
echo ""
