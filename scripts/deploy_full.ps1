# Full Auto Deployment Script - My Dent Care
# Usage: .\scripts\deploy_full.ps1
# This script will:
#   1. SSH to server and update .env.staging with CORS + correct config
#   2. Git pull latest code
#   3. Rebuild & restart Docker containers
#   4. Run migrations + DemoSeeder
#   5. Verify health endpoint

$ErrorActionPreference = "Continue"

# Configuration
$PEM_FILE = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
$AWS_IP = "108.136.48.83"
$SSH_USER = "ubuntu"
$REMOTE_DIR = "/home/ubuntu/My-Dent-Care/DentalERP"

# Colors
function Write-Step { param($msg) Write-Host "`n[>] $msg" -ForegroundColor Cyan }
function Write-Ok { param($msg) Write-Host "  OK: $msg" -ForegroundColor Green }
function Write-Err { param($msg) Write-Host "  ERR: $msg" -ForegroundColor Red }
function Write-Warn { param($msg) Write-Host "  WARN: $msg" -ForegroundColor Yellow }

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  My Dent Care - Full Auto Deploy" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Check PEM file
if (-not (Test-Path $PEM_FILE)) {
    Write-Err "PEM file not found: $PEM_FILE"
    exit 1
}
Write-Ok "PEM file verified"

$SSH_OPTS = "-o StrictHostKeyChecking=no -o ConnectTimeout=10 -i `"$PEM_FILE`""

function Run-SSH {
    param([string]$Cmd, [string]$Label = "")
    if ($Label) { Write-Step $Label }
    $sshCmd = "ssh $SSH_OPTS $SSH_USER@$AWS_IP `"$Cmd`" 2>&1"
    Write-Host "  Running: $Cmd" -ForegroundColor DarkGray
    $output = Invoke-Expression $sshCmd
    # $LASTEXITCODE from ssh itself
    $exitCode = $LASTEXITCODE
    if ($output) { $output | ForEach-Object { Write-Host "    $_" -ForegroundColor Gray } }
    return $exitCode, $output
}

# ============================================================
# Step 1: Update .env.staging on server
# ============================================================
Write-Step "Step 1: Updating .env.staging with production config..."

$EnvContent = @"
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:0AUqeIwG3ZIkXiD+g4+wAGt1m6dS6C4D7xeP9nWcOpE=
APP_DEBUG=false
APP_URL=http://108.136.48.83:8080
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

# Cache / Session / Queue (database driver - no Redis needed)
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=108.136.48.83

# Sanctum / Auth
SANCTUM_STATEFUL_DOMAINS=108.136.48.83:8080,localhost:5173,127.0.0.1:5173
SANCTUM_ACCESS_TOKEN_TTL=60

# CORS (comma-separated origins)
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173,http://localhost:4173

# Filesystem (local for now)
FILESYSTEM_DISK=local

# Audit / Notification queues
AUDIT_QUEUE=audit
AUDIT_QUEUE_CONNECTION=database
AUDIT_RETENTION_DAYS=365
NOTIFICATION_QUEUE=notifications
NOTIFICATION_QUEUE_CONNECTION=database
NOTIFICATION_RETRY_ATTEMPTS=3

# Payment Gateway (Midtrans) - sandbox
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# Security
SECURITY_HSTS_ENABLED=false
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_HSTS_INCLUDE_SUBDOMAINS=true
SECURITY_HSTS_PRELOAD=false
"@

# Write .env.staging - use base64 encoding to avoid shell escaping issues
$encodedContent = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($EnvContent))
$heredoc = "cd $REMOTE_DIR && echo '$encodedContent' | base64 -d > .env.staging && echo 'env_written_ok'"

$exitCode, $output = Run-SSH -Cmd $heredoc -Label "Writing .env.staging to server..."
if ($exitCode -ne 0) { Write-Err "Failed to write .env.staging"; exit 1 }
Write-Ok ".env.staging written"

# ============================================================
# Step 2: Git pull latest code
# ============================================================
Write-Step "Step 2: Pulling latest code from git..."

$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && git pull" -Label "Git pull..."
if ($exitCode -ne 0) { Write-Warn "Git pull had issues, continuing..." } else { Write-Ok "Code updated" }

# ============================================================
# Step 3: Build Docker image
# ============================================================
Write-Step "Step 3: Building Docker image..."

$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml build --no-cache" -Label "Docker build..."
if ($exitCode -ne 0) { Write-Err "Docker build failed"; exit 1 }
Write-Ok "Docker image built"

# ============================================================
# Step 4: Stop old containers and start new ones
# ============================================================
Write-Step "Step 4: Restarting containers..."

$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml down" -Label "Stopping old containers..."
$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml up -d" -Label "Starting new containers..."
if ($exitCode -ne 0) { Write-Err "Container start failed"; exit 1 }
Write-Ok "Containers started"

# ============================================================
# Step 5: Wait for app to be healthy
# ============================================================
Write-Step "Step 5: Waiting for app to be healthy (up to 60s)..."

$healthy = $false
for ($i = 1; $i -le 12; $i++) {
    Start-Sleep -Seconds 5
    Write-Host "  Attempt $i/12..." -ForegroundColor DarkGray
    $exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml ps --format json" -Label ""
    if ($exitCode -eq 0) {
        $exitCode2, $output2 = Run-SSH -Cmd "curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/up" -Label ""
        if ($output2 -eq "200") { $healthy = $true; break }
    }
}

if ($healthy) { Write-Ok "App is healthy (HTTP 200 on /up)" }
else { Write-Warn "App may not be fully healthy yet, continuing..." }

# ============================================================
# Step 6: Run migrations
# ============================================================
Write-Step "Step 6: Running database migrations..."

$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force" -Label "Migrating database..."
if ($exitCode -ne 0) { Write-Warn "Migration had issues (may already be up to date)" }
else { Write-Ok "Migrations complete" }

# ============================================================
# Step 7: Run DemoSeeder
# ============================================================
Write-Step "Step 7: Seeding demo data..."

$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed" -Label "Running DemoSeeder..."
if ($exitCode -ne 0) { Write-Warn "DemoSeeder may have already run or had errors" }
else { Write-Ok "Demo data seeded" }

# ============================================================
# Step 8: Clear caches
# ============================================================
Write-Step "Step 8: Clearing Laravel caches..."

$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:clear && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan route:clear && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan view:clear" -Label "Clearing caches..."
if ($exitCode -ne 0) { Write-Warn "Cache clear had issues" }
else { Write-Ok "Caches cleared" }

# ============================================================
# Step 9: Final verification
# ============================================================
Write-Step "Step 9: Final verification..."

# Health check
$exitCode, $output = Run-SSH -Cmd "curl -s http://localhost:8080/up" -Label "Health endpoint..."
if ($exitCode -eq 0) { Write-Ok "Health endpoint responding" }
else { Write-Warn "Health endpoint not responding" }

# API version check
$exitCode, $output = Run-SSH -Cmd "curl -s http://localhost:8080/api/v1/ 2>&1 | head -5" -Label "API version check..."

# Container status
$exitCode, $output = Run-SSH -Cmd "cd $REMOTE_DIR && sudo docker compose -f docker/compose.staging.yaml ps" -Label "Container status..."

# ============================================================
# Summary
# ============================================================
Write-Host "`n==========================================" -ForegroundColor Green
Write-Host "  DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Backend API:  http://$AWS_IP`:8080" -ForegroundColor White
Write-Host "Health Check: http://$AWS_IP`:8080/up" -ForegroundColor White
Write-Host ""
Write-Host "Demo Credentials:" -ForegroundColor Yellow
Write-Host "  Super Admin: superadmin / password123" -ForegroundColor Gray
Write-Host "  Doctor:      drjane / password123" -ForegroundColor Gray
Write-Host "  Receptionist: sarah / password123" -ForegroundColor Gray
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Test frontend at http://localhost:5173 (npm run dev in frontend/)" -ForegroundColor Gray
Write-Host "  2. Update frontend .env with VITE_API_URL=http://$AWS_IP`:8080/api" -ForegroundColor Gray
Write-Host "  3. Point domain to $AWS_IP and setup SSL when ready" -ForegroundColor Gray
Write-Host ""
