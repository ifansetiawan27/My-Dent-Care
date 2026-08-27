#!/bin/bash
# deploy-staging.sh - Deploy My Dent Care to EC2 staging server with demo data
#
# Usage:
#   chmod +x scripts/deploy-staging.sh
#   ./scripts/deploy-staging.sh
#
# Prerequisites:
#   - SSH key at: C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem
#   - Server: ubuntu@108.136.48.83
#   - Docker & Docker Compose installed on server
#   - rsync installed locally

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
SERVER="ubuntu@16.79.58.178"
KEY_PATH="C:/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem"
REMOTE_PROJECT="/home/ubuntu/My-Dent-Care"

echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║        My Dent Care — Staging Deployment               ║${NC}"
echo -e "${GREEN}║        Pre-Production with Demo Data                   ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Step 1: Verify connectivity
echo -e "${BLUE}[1/8]${NC} Testing server connectivity..."
if ! ssh -i "$KEY_PATH" -o ConnectTimeout=10 -o StrictHostKeyChecking=no "$SERVER" "echo 'connected'" >/dev/null 2>&1; then
    echo -e "${RED}  ✗ Cannot connect to server. Check SSH key and server status.${NC}"
    exit 1
fi
echo -e "${GREEN}  ✓ Connected to ${SERVER}${NC}"

# Step 2: Create project directory
echo -e "${BLUE}[2/8]${NC} Setting up server directory..."
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "mkdir -p ${REMOTE_PROJECT}"
echo -e "${GREEN}  ✓ Directory ready${NC}"

# Step 3: Upload files
echo -e "${BLUE}[3/8]${NC} Uploading application files..."
rsync -avz --delete \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='*.pem' \
    --exclude='.DS_Store' \
    --exclude='Thumbs.db' \
    -e "ssh -i ${KEY_PATH} -o StrictHostKeyChecking=no" \
    "${PROJECT_ROOT}/" \
    "${SERVER}:${REMOTE_PROJECT}/"
echo -e "${GREEN}  ✓ Files uploaded ($(du -sh "${PROJECT_ROOT}" | cut -f1))${NC}"

# Step 4: Configure environment
echo -e "${BLUE}[4/8]${NC} Configuring staging environment..."
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "cat > ${REMOTE_PROJECT}/DentalERP/.env.staging << 'ENVEOF'
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:0AUqeIwG3ZIkXiD+g4+wAGt1m6dS6C4D7xeP9nWcOpE=
APP_DEBUG=false
APP_URL=http://16.79.58.178:8080
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

# Cache / Session / Queue
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=cookie
SESSION_DOMAIN=16.79.58.178

# Sanctum / Auth
SANCTUM_STATEFUL_DOMAINS=16.79.58.178:8080,mydentcare.com,*.mydentcare.com,localhost:5173
SANCTUM_ACCESS_TOKEN_TTL=60

# Frontend URL
FRONTEND_URL=https://mydentcare.com

# CORS
CORS_ALLOWED_ORIGINS=https://mydentcare.com,http://localhost:5173,http://127.0.0.1:5173,http://16.79.58.178:8080

# Filesystem
FILESYSTEM_DISK=local

# Queues
AUDIT_QUEUE=audit
AUDIT_QUEUE_CONNECTION=database
AUDIT_RETENTION_DAYS=365
NOTIFICATION_QUEUE=notifications
NOTIFICATION_QUEUE_CONNECTION=database
NOTIFICATION_RETRY_ATTEMPTS=3

# Payment Gateway (Midtrans sandbox)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# Security
SECURITY_HSTS_ENABLED=false
SECURITY_HSTS_MAX_AGE=31536000
ENVEOF"
echo -e "${GREEN}  ✓ Environment configured${NC}"

# Step 5: Build and start containers
echo -e "${BLUE}[5/8]${NC} Building Docker image..."
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml down 2>/dev/null || true"
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml up -d --build"
echo -e "${GREEN}  ✓ Containers started${NC}"

# Step 6: Wait for health
echo -e "${BLUE}[6/8]${NC} Waiting for application to be healthy..."
for i in {1..30}; do
    if ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "curl -sf http://16.79.58.178:8080/up >/dev/null 2>&1"; then
        echo -e "${GREEN}  ✓ Application healthy (${i}s)${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${YELLOW}  ⚠ Health check timeout, continuing anyway...${NC}"
    fi
    sleep 2
done

# Step 7: Run migrations
echo -e "${BLUE}[7/8]${NC} Running database migrations..."
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate:fresh --force"
echo -e "${GREEN}  ✓ Migrations completed${NC}"

# Step 8: Seed demo data
echo -e "${BLUE}[8/8]${NC} Seeding demo data..."
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=DemoSeeder"
ssh -i "$KEY_PATH" -o StrictHostKeyChecking=no "$SERVER" "cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=ExtendedDemoSeeder"
echo -e "${GREEN}  ✓ Demo data seeded${NC}"

# Summary
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║              🚀 Deployment Complete!                   ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📍 URLs:${NC}"
echo -e "   Backend API:     ${GREEN}http://16.79.58.178:8080${NC}"
echo -e "   Health Check:    ${GREEN}http://16.79.58.178:8080/up${NC}"
echo -e "   API Base:        ${GREEN}http://16.79.58.178:8080/api/v1/${NC}"
echo ""
echo -e "${BLUE}🔑 Demo Login Credentials:${NC}"
echo -e "   ┌──────────────┬──────────────────────────────┬───────────────┐${NC}"
echo -e "   │ Role         │ Email                        │ Password      │${NC}"
echo -e "   ├──────────────┼──────────────────────────────┼───────────────┤${NC}"
echo -e "   │ Super Admin  │ superadmin@demodental.com    │ password123   │${NC}"
echo -e "   │ Doctor       │ drjane@demodental.com        │ password123   │${NC}"
echo -e "   │ Receptionist │ sarah@demodental.com         │ password123   │${NC}"
echo -e "   └──────────────┴──────────────────────────────┴───────────────┘${NC}"
echo ""
echo -e "${BLUE}📊 Demo Data Summary:${NC}"
echo -e "   🏢 Organization:   Demo Dental Clinic Group"
echo -e "   📍 Branch:         Demo Dental Jakarta Pusat"
echo -e "   👥 Users:          3 (Super Admin, Doctor, Receptionist)"
echo -e "   🧑‍⚕️  Patients:        3 (John Doe, Maria Garcia, Robert Chen)"
echo -e "   📅 Appointments:   8 (3 completed, 4 scheduled, 1 cancelled)"
echo -e "   🦷 Treatments:     5 (scaling, filling, crown, RCT, extraction)"
echo -e "   💰 Invoices:       5 (2 paid, 2 sent, 1 draft)"
echo -e "   📦 Inventory:      8 items (supplies & equipment)"
echo -e "   💊 Pharmacy:       6 items (medications)"
echo -e "   🔬 Lab Orders:     3 (crown, temporary, denture)"
echo -e "   🏥 EMR Records:    3 full encounters with vitals"
echo -e "   🦷 Odontograms:    15 tooth records across 3 patients"
echo -e "   📸 Radiology:      3 orders, 2 images, 2 reports"
echo -e "   📞 CRM:            3 contacts (complaint, inquiry, reminder)"
echo -e "   💼 Finance:        10 COA accounts + 2 journal entries"
echo ""
echo -e "${BLUE}🔧 Useful Commands:${NC}"
echo -e "   SSH:     ssh -i \"${KEY_PATH}\" ${SERVER}"
echo -e "   Logs:    ssh -i \"${KEY_PATH}\" ${SERVER} 'cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml logs -f app'"
echo -e "   Shell:   ssh -i \"${KEY_PATH}\" ${SERVER} 'cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml exec app bash'"
echo -e "   DB Info: ssh -i \"${KEY_PATH}\" ${SERVER} 'cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml exec app php artisan db:show'"
echo -e "   Restart: ssh -i \"${KEY_PATH}\" ${SERVER} 'cd ${REMOTE_PROJECT}/DentalERP && docker compose -f docker/compose.staging.yaml restart'"
echo ""
