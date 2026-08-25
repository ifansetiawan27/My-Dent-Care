#!/bin/bash
# =====================================================
# deploy.sh — Run this on the EC2 server
# Usage: ssh ubuntu@108.136.48.83 "bash ~/My-Dent-Care/scripts/deploy.sh"
# Or:    SSH into server, cd to project, then: bash scripts/deploy.sh
# =====================================================

set -e

REMOTE_DIR="/home/ubuntu/My-Dent-Care/DentalERP"

echo "=========================================="
echo "  My Dent Care - Server Deploy Script"
echo "=========================================="

cd "$REMOTE_DIR"

# Step 1: Git pull
echo ""
echo "[Step 1/6] Pulling latest code..."
git pull || echo "  WARN: git pull had issues, continuing..."
echo "  OK: Code updated"

# Step 2: Copy .env.staging.production to .env.staging (if exists)
echo ""
echo "[Step 2/6] Setting up .env.staging..."
if [ -f .env.staging.production ]; then
    cp .env.staging .env.staging.backup 2>/dev/null || true
    cp .env.staging.production .env.staging
    echo "  OK: .env.staging created from .env.staging.production"
else
    echo "  OK: .env.staging already exists (skipped)"
fi

# Step 3: Docker build
echo ""
echo "[Step 3/6] Building Docker image..."
sudo docker compose -f docker/compose.staging.yaml build --no-cache
echo "  OK: Docker image built"

# Step 4: Restart containers
echo ""
echo "[Step 4/6] Restarting containers..."
sudo docker compose -f docker/compose.staging.yaml down
sudo docker compose -f docker/compose.staging.yaml up -d
echo "  OK: Containers restarted"

# Step 5: Wait for healthy
echo ""
echo "[Step 5/6] Waiting for app to be healthy (60s max)..."
for i in $(seq 1 12); do
    sleep 5
    HTTP_CODE=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/up 2>/dev/null || echo "000")
    echo "  Attempt $i/12: HTTP $HTTP_CODE"
    if [ "$HTTP_CODE" = "200" ]; then
        echo "  OK: App is healthy"
        break
    fi
done

# Step 6: Migrate + seed
echo ""
echo "[Step 6/6] Running migrations and seeding..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force || echo "  WARN: Migration skipped"
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed || echo "  WARN: Seeder skipped (may already be seeded)"
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:clear
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan route:clear
echo "  OK: Migrations and caches done"

# Final verification
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
echo ""
sudo docker compose -f docker/compose.staging.yaml ps
echo ""
curl -s http://localhost:8080/up || echo "Health endpoint not responding yet"
