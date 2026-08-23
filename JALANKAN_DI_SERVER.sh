#!/bin/bash

# ============================================
# My Dent Care - Deployment Completion Script
# Jalankan ini di server AWS setelah SSH
# ============================================

set -e

echo "================================================"
echo "My Dent Care - Deployment Completion"
echo "Server: 108.136.48.83"
echo "================================================"
echo ""

# Step 1: Pull latest changes
echo "[1/9] Pulling latest changes from GitHub..."
cd ~/My-Dent-Care
git pull origin main

echo ""
echo "[2/9] Rebuilding Docker image with fixed config..."
cd DentalERP
sudo docker compose -f docker/compose.staging.yaml build --no-cache

echo ""
echo "[3/9] Stopping old containers..."
sudo docker compose -f docker/compose.staging.yaml down

echo ""
echo "[4/9] Starting new containers..."
sudo docker compose -f docker/compose.staging.yaml up -d

echo ""
echo "[5/9] Waiting for container to be healthy (60s)..."
sleep 60

echo ""
echo "[6/9] Checking container status..."
sudo docker compose -f docker/compose.staging.yaml ps

echo ""
echo "[7/9] Running database migrations..."
echo "Creating cache table..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan cache:table

echo "Creating session table..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan session:table

echo "Running all migrations..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force

echo ""
echo "[8/9] Optimizing Laravel..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:clear
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan cache:clear
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan route:cache
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:cache

echo ""
echo "[9/9] Testing endpoints..."
echo ""
echo "Health Check:"
curl -s http://localhost:8080/health | jq '.' || curl -s http://localhost:8080/health

echo ""
echo ""
echo "API Endpoint:"
curl -s http://localhost:8080/api/ | jq '.' || curl -s http://localhost:8080/api/

echo ""
echo ""
echo "================================================"
echo "✅ Deployment Completed Successfully!"
echo "================================================"
echo ""
echo "Next Steps:"
echo "1. Test from outside: curl http://108.136.48.83:8080/api/"
echo "2. Update Vercel env: VITE_API_BASE_URL=http://108.136.48.83:8080/api"
echo "3. Redeploy frontend"
echo ""
echo "================================================"
