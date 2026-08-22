#!/bin/bash

# Script untuk otomatisasi deployment My Dent Care ke AWS EC2
# Usage: ./setup_aws.sh <PEM_FILE> <AWS_IP> <SUPABASE_URL>

set -e

# Validasi argumen
if [ "$#" -ne 3 ]; then
    echo "Error: Script memerlukan 3 argumen"
    echo "Usage: $0 <PEM_FILE> <AWS_IP> <SUPABASE_URL>"
    echo ""
    echo "Contoh:"
    echo "  $0 ~/my-key.pem 54.123.45.67 postgresql://user:pass@host:5432/db"
    exit 1
fi

PEM_FILE="$1"
AWS_IP="$2"
SUPABASE_URL="$3"

# Validasi file PEM exists
if [ ! -f "$PEM_FILE" ]; then
    echo "Error: File PEM tidak ditemukan: $PEM_FILE"
    exit 1
fi

echo "=========================================="
echo "AWS EC2 Deployment Automation"
echo "=========================================="
echo "PEM File: $PEM_FILE"
echo "AWS IP: $AWS_IP"
echo "Supabase URL: ${SUPABASE_URL:0:30}..."
echo "=========================================="
echo ""

# Eksekusi deployment via SSH menggunakan heredoc
ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" << 'ENDSSH'
set -e

echo "[1/7] Updating apt dan installing dependencies..."
sudo apt-get update -y
sudo apt-get install -y git curl

echo "[2/7] Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker ubuntu
    rm get-docker.sh
    echo "Docker installed successfully"
else
    echo "Docker already installed"
fi

echo "[3/7] Installing docker-compose-plugin..."
sudo apt-get install -y docker-compose-plugin

echo "[4/7] Cloning repository..."
if [ -d "My-Dent-Care" ]; then
    echo "Removing existing My-Dent-Care directory..."
    rm -rf My-Dent-Care
fi
git clone https://github.com/ifansetiawan27/My-Dent-Care.git
echo "Repository cloned successfully"

echo "[5/7] Entering project directory..."
cd My-Dent-Care/DentalERP

echo "[6/7] Creating .env.staging file..."
cat > .env.staging << 'ENDENV'
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:replace-with-your-app-key
APP_DEBUG=false
APP_URL=https://api.mydentcare.com
FRONTEND_URL=https://mydentcare.com

DB_CONNECTION=pgsql
DATABASE_URL=SUPABASE_URL_PLACEHOLDER

SANCTUM_STATEFUL_DOMAINS=mydentcare.com
SESSION_DOMAIN=mydentcare.com

QUEUE_CONNECTION=database
CACHE_DRIVER=database
SESSION_DRIVER=database

FILESYSTEM_DISK=local

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
ENDENV

echo ".env file created successfully"

echo "[7/7] Starting Docker containers..."
sudo docker compose -f docker/compose.staging.yaml --env-file .env.staging up -d

echo ""
echo "=========================================="
echo "Deployment completed successfully!"
echo "=========================================="
echo "Backend API: https://api.mydentcare.com"
echo "Frontend: https://mydentcare.com"
echo ""
echo "Next steps:"
echo "1. Generate APP_KEY: sudo docker compose -f docker/compose.staging.yaml exec app php artisan key:generate"
echo "2. Run migrations: sudo docker compose -f docker/compose.staging.yaml exec app php artisan migrate --force"
echo "3. Seed data (optional): sudo docker compose -f docker/compose.staging.yaml exec app php artisan db:seed --force"
echo "4. Configure DNS untuk domain Anda"
echo "5. Setup SSL certificate (Let's Encrypt)"
echo "=========================================="
ENDSSH

# Setelah SSH heredoc selesai, replace SUPABASE_URL di server
echo ""
echo "Updating Supabase URL in .env.staging file..."
ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" << ENDSSH2
sed -i "s|DATABASE_URL=SUPABASE_URL_PLACEHOLDER|DATABASE_URL=$SUPABASE_URL|g" My-Dent-Care/DentalERP/.env.staging
echo "Supabase URL updated successfully"
ENDSSH2

echo ""
echo "=========================================="
echo "DEPLOYMENT SELESAI!"
echo "=========================================="
echo ""
echo "Server siap digunakan di:"
echo "- Backend API: https://api.mydentcare.com (atau http://$AWS_IP:8000)"
echo "- Health Check: http://$AWS_IP:8000/up"
echo ""
echo "Untuk melihat logs:"
echo "  ssh -i $PEM_FILE ubuntu@$AWS_IP 'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml logs -f'"
echo ""
echo "Untuk restart containers:"
echo "  ssh -i $PEM_FILE ubuntu@$AWS_IP 'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml restart'"
echo ""
