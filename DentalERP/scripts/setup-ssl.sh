#!/bin/bash
# setup-ssl.sh - Setup Nginx + Let's Encrypt SSL for DentalERP
#
# Usage:
#   chmod +x scripts/setup-ssl.sh
#   ./scripts/setup-ssl.sh
#
# Prerequisites:
#   - Domain pointed to your server IP (DNS A record)
#   - Port 80 and 443 open in security group
#   - Docker & Docker Compose installed

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}====================================${NC}"
echo -e "${GREEN}  DentalERP SSL Setup Script${NC}"
echo -e "${GREEN}====================================${NC}"
echo ""

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
DOCKER_DIR="$PROJECT_ROOT/docker"
ENV_FILE="$PROJECT_ROOT/.env.staging"

# Check if .env.staging exists
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${RED}ERROR: .env.staging not found at $ENV_FILE${NC}"
    echo "Please create it first from .env.staging.example"
    exit 1
fi

echo -e "${YELLOW}Step 1: Reading configuration...${NC}"

# Read domain from .env.staging (fallback to user input)
DOMAIN=$(grep '^APP_URL=' "$ENV_FILE" | cut -d'=' -f2 | sed 's|http[s]*://||' | cut -d':' -f1)

if [ -z "$DOMAIN" ] || [[ "$DOMAIN" == *"108.136.48.83"* ]]; then
    echo -e "${YELLOW}No valid domain found in APP_URL. Enter your domain:${NC}"
    echo -e "(e.g., api.mydentcare.com)${NC}"
    read -p "Domain: " DOMAIN
fi

EMAIL=$(grep '^LETSENCRYPT_EMAIL=' "$ENV_FILE" 2>/dev/null | cut -d'=' -f2 || echo "")

if [ -z "$EMAIL" ]; then
    read -p "Email for Let's Encrypt: " EMAIL
fi

echo ""
echo -e "${GREEN}Configuration:${NC}"
echo "  Domain: $DOMAIN"
echo "  Email:  $EMAIL"
echo ""

# Update .env.staging with SSL config
echo -e "${YELLOW}Step 2: Updating .env.staging...${NC}"

# Add/update SSL config in .env.staging
if grep -q '^LETSENCRYPT_EMAIL=' "$ENV_FILE" 2>/dev/null; then
    sed -i "s|^LETSENCRYPT_EMAIL=.*|LETSENCRYPT_EMAIL=$EMAIL|" "$ENV_FILE"
else
    echo "LETSENCRYPT_EMAIL=$EMAIL" >> "$ENV_FILE"
fi

if grep -q '^LETSENCRYPT_DOMAIN=' "$ENV_FILE" 2>/dev/null; then
    sed -i "s|^LETSENCRYPT_DOMAIN=.*|LETSENCRYPT_DOMAIN=$DOMAIN|" "$ENV_FILE"
else
    echo "LETSENCRYPT_DOMAIN=$DOMAIN" >> "$ENV_FILE"
fi

# Update APP_URL to HTTPS
sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" "$ENV_FILE"

# Update SANCTUM_STATEFUL_DOMAINS
sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=$DOMAIN,localhost:5173,127.0.0.1:5173|" "$ENV_FILE"

# Update CORS_ALLOWED_ORIGINS
sed -i "s|^CORS_ALLOWED_ORIGINS=.*|CORS_ALLOWED_ORIGINS=https://$DOMAIN,http://localhost:5173,http://127.0.0.1:5173|" "$ENV_FILE"

echo -e "${GREEN}  .env.staging updated${NC}"

# Create nginx SSL directory
echo -e "${YELLOW}Step 3: Creating SSL directories...${NC}"
mkdir -p "$DOCKER_DIR/nginx/ssl"
mkdir -p "$DOCKER_DIR/nginx/certbot"

# Generate self-signed cert for initial boot (certbot will replace later)
echo -e "${YELLOW}Step 4: Generating temporary self-signed certificate...${NC}"
openssl req -x509 -nodes -days 7 \
    -newkey rsa:2048 \
    -keyout "$DOCKER_DIR/nginx/ssl/privkey.pem" \
    -out "$DOCKER_DIR/nginx/ssl/fullchain.pem" \
    -subj "/CN=$DOMAIN" 2>/dev/null

echo -e "${GREEN}  Temporary cert generated${NC}"

# Check if DNS is properly configured
echo -e "${YELLOW}Step 5: Checking DNS configuration...${NC}"
SERVER_IP="108.136.48.83"
DNS_IP=$(dig +short "$DOMAIN" 2>/dev/null | head -1 || nslookup "$DOMAIN" 2>/dev/null | grep "Address:" | tail -1 | awk '{print $2}')

if [ "$DNS_IP" != "$SERVER_IP" ]; then
    echo -e "${RED}WARNING: DNS not pointing to server!${NC}"
    echo "  Expected: $SERVER_IP"
    echo "  Got:      ${DNS_IP:-not resolved}"
    echo ""
    echo -e "${YELLOW}Please create/update your DNS A record:${NC}"
    echo "  $DOMAIN → $SERVER_IP"
    echo ""
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo -e "${GREEN}  DNS correctly points to $SERVER_IP${NC}"
fi

# Deploy with SSL
echo -e "${YELLOW}Step 6: Deploying with SSL...${NC}"
cd "$PROJECT_ROOT/DentalERP"

# Stop existing containers
docker compose -f docker/compose.staging.yaml down 2>/dev/null || true

# Start with SSL compose
export LETSENCRYPT_EMAIL="$EMAIL"
export LETSENCRYPT_DOMAIN="$DOMAIN"
export APP_PORT=80
export SSL_PORT=443

docker compose -f "$DOCKER_DIR/compose.staging.ssl.yaml" up -d

echo ""
echo -e "${GREEN}====================================${NC}"
echo -e "${GREEN}  Deployment Complete!${NC}"
echo -e "${GREEN}====================================${NC}"
echo ""
echo -e "API URL: ${GREEN}https://$DOMAIN${NC}"
echo -e "Health:  ${GREEN}https://$DOMAIN/up${NC}"
echo ""
echo -e "${YELLOW}Note: Let's Encrypt cert will be issued automatically.${NC}"
echo -e "Initial cert may take 1-2 minutes. Until then,"
echo -e "the temporary self-signed cert is active."
echo ""
echo -e "To check cert status:"
echo -e "  docker logs dentalerp_staging_certbot"
echo ""
echo -e "To view nginx logs:"
echo -e "  docker logs dentalerp_staging_nginx"
echo ""
