# setup-ssl.ps1 - Setup Nginx + Let's Encrypt SSL for DentalERP (Windows)
#
# Usage:
#   .\scripts\setup-ssl.ps1
#
# Prerequisites:
#   - Domain pointed to your server IP (DNS A record)
#   - Port 80 and 443 open in security group
#   - SSH access to server

$ErrorActionPreference = "Stop"

Write-Host "====================================" -ForegroundColor Green
Write-Host "  DentalERP SSL Setup Script" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green
Write-Host ""

# Configuration
$Domain = Read-Host "Enter your API domain (e.g., api.mydentcare.com)"
$Email = Read-Host "Enter email for Let's Encrypt"
$ServerIP = "108.136.48.83"
$KeyPath = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"

Write-Host ""
Write-Host "Configuration:" -ForegroundColor Green
Write-Host "  Domain: $Domain"
Write-Host "  Email:  $Email"
Write-Host ""

# Step 1: SSH to server and setup nginx + SSL
Write-Host "Step 1: Connecting to server..." -ForegroundColor Yellow

$SetupScript = @"
#!/bin/bash
set -e

DOMAIN="$Domain"
EMAIL="$Email"
PROJECT_DIR=/home/ubuntu/My-Dent-Care/DentalERP
DOCKER_DIR=`$PROJECT_DIR/docker

echo 'Creating directories...'
mkdir -p `$DOCKER_DIR/nginx/ssl
mkdir -p `$DOCKER_DIR/nginx/certbot

echo 'Generating temporary self-signed cert...'
openssl req -x509 -nodes -days 7 \
    -newkey rsa:2048 \
    -keyout `$DOCKER_DIR/nginx/ssl/privkey.pem \
    -out `$DOCKER_DIR/nginx/ssl/fullchain.pem \
    -subj "/CN=`$DOMAIN" 2>/dev/null

echo 'Updating .env.staging...'
cd `$PROJECT_DIR

# Add SSL config if not present
grep -q '^LETSENCRYPT_EMAIL=' .env.staging 2>/dev/null || echo 'LETSENCRYPT_EMAIL=$EMAIL' >> .env.staging
grep -q '^LETSENCRYPT_DOMAIN=' .env.staging 2>/dev/null || echo 'LETSENCRYPT_DOMAIN=$DOMAIN' >> .env.staging

# Update APP_URL
sed -i "s|^APP_URL=.*|APP_URL=https://`$DOMAIN|" .env.staging

# Update SANCTUM_STATEFUL_DOMAINS
sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=`$DOMAIN,localhost:5173,127.0.0.1:5173|" .env.staging

# Update CORS
sed -i "s|^CORS_ALLOWED_ORIGINS=.*|CORS_ALLOWED_ORIGINS=https://`$DOMAIN,http://localhost:5173,http://127.0.0.1:5173|" .env.staging

echo 'Stopping old containers...'
docker compose -f docker/compose.staging.yaml down 2>/dev/null || true

echo 'Starting with SSL...'
export LETSENCRYPT_EMAIL=$EMAIL
export LETSENCRYPT_DOMAIN=$DOMAIN
export APP_PORT=80
export SSL_PORT=443

docker compose -f docker/compose.staging.ssl.yaml up -d

echo ''
echo '===================================='
echo '  Deployment Complete!'
echo '===================================='
echo "API URL: https://$DOMAIN"
echo "Health:  https://$DOMAIN/up"
echo ''
echo 'Note: Let's Encrypt cert will be issued automatically.'
echo 'Check status: docker logs dentalerp_staging_certbot'
"@

$TempFile = [System.IO.Path]::GetTempFileName() + ".sh"
[System.IO.File]::WriteAllText($TempFile, $SetupScript)

ssh -i "$KeyPath" ubuntu@$ServerIP "bash -s" < $TempFile

Remove-Item $TempFile -Force

Write-Host ""
Write-Host "Done! SSL setup completed." -ForegroundColor Green
