#!/bin/bash
# Complete deployment script - run on server

set -e

echo "=========================================="
echo "Completing Backend Deployment"
echo "=========================================="

cd ~/My-Dent-Care/DentalERP

echo "[1/7] Generating APP_KEY..."
APP_KEY=$(openssl rand -base64 32)
sudo sed -i "s|APP_KEY=base64:replace-with-your-app-key|APP_KEY=base64:$APP_KEY|g" .env.staging
echo "✓ APP_KEY generated"

echo "[2/7] Updating CORS and Sanctum domains..."
sudo sed -i "s|FRONTEND_URL=https://mydentcare.com|FRONTEND_URL=https://my-dent-care-q11342jnv-blackid.vercel.app|g" .env.staging
sudo sed -i "s|SANCTUM_STATEFUL_DOMAINS=mydentcare.com|SANCTUM_STATEFUL_DOMAINS=my-dent-care-q11342jnv-blackid.vercel.app|g" .env.staging
sudo sed -i "s|SESSION_DOMAIN=mydentcare.com|SESSION_DOMAIN=.vercel.app|g" .env.staging
echo "✓ Frontend domains updated"

echo "[3/7] Restarting containers..."
sudo docker compose -f docker/compose.staging.yaml restart
sleep 15
echo "✓ Containers restarted"

echo "[4/7] Running migrations..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force
echo "✓ Migrations completed"

echo "[5/7] Creating cache/queue/session tables..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan queue:table
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan cache:table
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan session:table
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force
echo "✓ Tables created"

echo "[6/7] Caching configuration..."
sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:cache
echo "✓ Config cached"

echo "[7/7] Testing health check..."
curl -s http://localhost:8080/up
echo ""
echo ""

echo "=========================================="
echo "✓ Deployment Complete!"
echo "=========================================="
echo ""
echo "Backend API: http://108.136.48.83:8080"
echo "Health: http://108.136.48.83:8080/up"
echo "Frontend: https://my-dent-care-q11342jnv-blackid.vercel.app"
echo ""
echo "Container status:"
sudo docker compose -f docker/compose.staging.yaml ps
