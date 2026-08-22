#!/bin/bash

# Deployment Completion Script
# Run this on the server to complete the deployment after config fixes

set -e

echo "=========================================="
echo "My Dent Care - Deployment Completion"
echo "=========================================="
echo ""

# Navigate to project directory
cd ~/My-Dent-Care/DentalERP

echo "Step 1: Pulling latest changes..."
git pull origin main

echo ""
echo "Step 2: Rebuilding Docker image with fixed DATABASE_URL config..."
sudo docker compose -f docker/compose.staging.yaml build --no-cache

echo ""
echo "Step 3: Stopping old containers..."
sudo docker compose -f docker/compose.staging.yaml down

echo ""
echo "Step 4: Starting new containers..."
sudo docker compose -f docker/compose.staging.yaml up -d

echo ""
echo "Step 5: Waiting for container to be healthy (60 seconds)..."
sleep 60

echo ""
echo "Step 6: Checking container health..."
sudo docker compose -f docker/compose.staging.yaml ps

echo ""
echo "Step 7: Running database migrations..."
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force

echo ""
echo "Step 8: Creating cache and session tables..."
sudo docker compose -f docker/compose.staging.yaml exec app php artisan cache:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan session:table
sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force

echo ""
echo "Step 9: Clearing and optimizing cache..."
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:clear
sudo docker compose -f docker/compose.staging.yaml exec app php artisan cache:clear
sudo docker compose -f docker/compose.staging.yaml exec app php artisan route:cache
sudo docker compose -f docker/compose.staging.yaml exec app php artisan config:cache

echo ""
echo "=========================================="
echo "Deployment completed successfully!"
echo "=========================================="
echo ""
echo "Testing endpoints..."
echo ""
echo "Health check:"
curl -s http://localhost:8080/health | jq '.'
echo ""
echo ""
echo "API endpoint:"
curl -s http://localhost:8080/api/ | jq '.'
echo ""
echo ""
echo "Next steps:"
echo "1. Verify the API is working: curl http://108.136.48.83:8080/api/"
echo "2. Update Vercel environment: VITE_API_BASE_URL=http://108.136.48.83:8080/api"
echo "3. Redeploy frontend on Vercel"
echo ""
echo "=========================================="
