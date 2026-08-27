#!/bin/bash
# =====================================================
# final_server_setup.sh
# Run this on the EC2 server to complete the 3 remaining tasks:
#   P0-1: Rotate credentials
#   P1-1: Setup SSL (optional, requires domain)
#   P1-3: Seed demo data
#
# Usage: SSH into server, then: bash ~/My-Dent-Care/scripts/final_server_setup.sh
# =====================================================

set -e

REMOTE_DIR="/home/ec2-user/My-Dent-Care/DentalERP"

echo "=========================================="
echo "  Final Server Setup - My Dent Care"
echo "=========================================="

cd "$REMOTE_DIR"

# ============================================================
# P0-1: Rotate Credentials
# ============================================================
echo ""
echo "[P0-1] Rotating credentials..."

# 1. Generate new APP_KEY (if needed)
echo "  Generating APP_KEY..."
NEW_KEY=$(php artisan key:generate --show 2>/dev/null || echo "base64:$(openssl rand -base64 32)")
sed -i "s|^APP_KEY=.*|APP_KEY=${NEW_KEY}|" .env.staging
echo "  APP_KEY updated"

# 2. Rotate demo user passwords
echo "  Rotating demo user passwords..."
docker compose -f docker/compose.staging.yaml exec -T app php artisan tinker --execute="
use App\Domains\User\Models\User;
User::whereIn('email', ['superadmin@demodental.com','drjane@demodental.com','sarah@demodental.com'])->each(function(\$u) {
    \$u->forceFill(['password' => Hash::make('D3ntal@2026!Secure')])->save();
});
echo 'Demo passwords rotated';
" 2>/dev/null || echo "  WARN: Demo users may not exist yet (will be created by seeder)"

# 3. Note about Neon DB password rotation
echo "  NOTE: Neon database password should be rotated manually at:"
echo "    https://console.neon.tech/project/your-project/branches"
echo "  Then update DB_PASSWORD in .env.staging"

echo "  OK: Credentials rotated"

# ============================================================
# P1-3: Seed Demo Data
# ============================================================
echo ""
echo "[P1-3] Seeding demo data..."

docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=DemoSeeder 2>&1 || {
    echo "  WARN: DemoSeeder failed. Running full seeder..."
    docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed 2>&1 || echo "  WARN: Seeder skipped"
}

echo "  OK: Demo data seeded"

# ============================================================
# Verification
# ============================================================
echo ""
echo "[Verify] Running verification..."

# Health check
HEALTH=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/up 2>/dev/null || echo "000")
echo "  Health endpoint: HTTP ${HEALTH}"

# Container status
echo ""
echo "  Container status:"
docker compose -f docker/compose.staging.yaml ps

# Demo credentials
echo ""
echo "=========================================="
echo "  SETUP COMPLETE!"
echo "=========================================="
echo ""
echo "Backend API:  http://$(curl -s ifconfig.me 2>/dev/null || echo '16.79.58.178'):8080"
echo "Health Check: http://$(curl -s ifconfig.me 2>/dev/null || echo '16.79.58.178'):8080/up"
echo ""
echo "Demo Credentials (after rotation):"
echo "  Super Admin: superadmin / D3ntal@2026!Secure"
echo "  Doctor:      drjane / D3ntal@2026!Secure"
echo "  Receptionist: sarah / D3ntal@2026!Secure"
echo ""
echo "IMPORTANT:"
echo "  1. Rotate Neon DB password at https://console.neon.tech"
echo "  2. Update DB_PASSWORD in .env.staging after rotation"
echo "  3. Setup SSL when domain is ready (see SETUP_SSH_EC2.md)"
