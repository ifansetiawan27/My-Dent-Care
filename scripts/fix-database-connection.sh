#!/bin/bash

# Fix Database Connection Script
# Updates DATABASE_URL to use Supabase Session Pooler (IPv4 support)
# Date: 2026-08-23

set -e

echo "=================================================="
echo "Fix Database Connection - IPv4 Support"
echo "=================================================="
echo ""

# Configuration
PROJECT_DIR="$HOME/My-Dent-Care/DentalERP"
ENV_FILE="$PROJECT_DIR/.env.staging"
DOCKER_COMPOSE="$PROJECT_DIR/docker/compose.staging.yaml"

# New connection string with Session Pooler
NEW_DATABASE_URL="postgresql://postgres.iccktgeijswtupjcgswx:Ifansetiawan093600@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres"

echo "Step 1: Backup existing .env.staging"
if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✅ Backup created: $ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"
else
    echo "❌ .env.staging not found at $ENV_FILE"
    exit 1
fi

echo ""
echo "Step 2: Update DATABASE_URL in .env.staging"
if grep -q "DATABASE_URL=" "$ENV_FILE"; then
    sed -i "s|DATABASE_URL=.*|DATABASE_URL=\"$NEW_DATABASE_URL\"|" "$ENV_FILE"
    echo "✅ DATABASE_URL updated"
else
    echo "DATABASE_URL=\"$NEW_DATABASE_URL\"" >> "$ENV_FILE"
    echo "✅ DATABASE_URL added"
fi

echo ""
echo "Step 3: Verify new DATABASE_URL"
grep "DATABASE_URL=" "$ENV_FILE"

echo ""
echo "Step 4: Test connection with psql"
echo "Installing postgresql-client if needed..."
if ! command -v psql &> /dev/null; then
    sudo apt update -qq
    sudo apt install -y postgresql-client
    echo "✅ postgresql-client installed"
fi

echo ""
echo "Testing database connection..."
if PGPASSWORD=Ifansetiawan093600 psql -h aws-0-ap-southeast-1.pooler.supabase.com -p 5432 -U postgres.iccktgeijswtupjcgswx -d postgres -c "SELECT version();" &> /dev/null; then
    echo "✅ Database connection successful!"
    PGPASSWORD=Ifansetiawan093600 psql -h aws-0-ap-southeast-1.pooler.supabase.com -p 5432 -U postgres.iccktgeijswtupjcgswx -d postgres -c "SELECT version();"
else
    echo "⚠️  Direct psql connection failed, but Docker container might still work"
    echo "   (This can happen due to SSL requirements or firewall rules)"
fi

echo ""
echo "Step 5: Restart Docker containers"
cd "$PROJECT_DIR"
if [ -f "$DOCKER_COMPOSE" ]; then
    echo "Stopping containers..."
    sudo docker compose -f "$DOCKER_COMPOSE" down
    
    echo "Starting containers..."
    sudo docker compose -f "$DOCKER_COMPOSE" up -d
    
    echo "Waiting 10 seconds for container to be ready..."
    sleep 10
    
    echo "✅ Containers restarted"
else
    echo "❌ Docker compose file not found: $DOCKER_COMPOSE"
    exit 1
fi

echo ""
echo "Step 6: Verify database connection from container"
echo "Testing Laravel database connection..."
if sudo docker compose -f "$DOCKER_COMPOSE" exec -T dental-erp-staging php artisan db:show 2>&1 | grep -q "Connection:"; then
    echo "✅ Laravel can connect to database!"
    echo ""
    sudo docker compose -f "$DOCKER_COMPOSE" exec -T dental-erp-staging php artisan db:show
else
    echo "❌ Laravel cannot connect to database"
    echo "Container logs:"
    sudo docker compose -f "$DOCKER_COMPOSE" logs --tail=50 dental-erp-staging
    exit 1
fi

echo ""
echo "Step 7: Check migration status"
sudo docker compose -f "$DOCKER_COMPOSE" exec -T dental-erp-staging php artisan migrate:status

echo ""
echo "=================================================="
echo "✅ Database Connection Fixed!"
echo "=================================================="
echo ""
echo "Connection details:"
echo "  Host: aws-0-ap-southeast-1.pooler.supabase.com"
echo "  Port: 5432 (Session Pooler - IPv4 supported)"
echo "  User: postgres.iccktgeijswtupjcgswx"
echo "  Database: postgres"
echo ""
echo "Next steps:"
echo "  1. Run migrations: sudo docker compose -f $DOCKER_COMPOSE exec dental-erp-staging php artisan migrate --force"
echo "  2. Test API endpoint: curl http://localhost:8080/api/v1/"
echo "  3. Check container logs: sudo docker compose -f $DOCKER_COMPOSE logs -f dental-erp-staging"
echo ""
echo "For more info, see: SOLUSI_DATABASE_IPv4.md"
echo "=================================================="
