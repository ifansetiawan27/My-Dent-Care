# deploy-staging.ps1 - Deploy My Dent Care to EC2 staging server (Windows)
#
# Usage:
#   .\scripts\deploy-staging.ps1
#
# Prerequisites:
#   - SSH key: C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem
#   - rsync installed (via Git Bash or WSL)
#   - Server: ubuntu@108.136.48.83

$ErrorActionPreference = "Stop"

$Server = "ubuntu@16.79.58.178"
$KeyPath = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
$ProjectRoot = Split-Path (Split-Path $PSScriptRoot -Parent)
$RemoteProject = "/home/ubuntu/My-Dent-Care"

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "  My Dent Care - Staging Deployment" -ForegroundColor Green
Write-Host "  Pre-Production with Demo Data" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""

# Step 1: Test connectivity
Write-Host "[1/8] Testing server connectivity..." -ForegroundColor Blue
try {
    ssh -i $KeyPath -o ConnectTimeout=10 -o StrictHostKeyChecking=no $Server "echo 'ok'" | Out-Null
    Write-Host "  ✓ Connected" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Cannot connect to server" -ForegroundColor Red
    exit 1
}

# Step 2: Create directory
Write-Host "[2/8] Setting up server directory..." -ForegroundColor Blue
ssh -i $KeyPath -o StrictHostKeyChecking=no $Server "mkdir -p $RemoteProject" | Out-Null
Write-Host "  ✓ Directory ready" -ForegroundColor Green

# Step 3: Upload files using Git Bash rsync (or fallback to scp)
Write-Host "[3/8] Uploading application files..." -ForegroundColor Blue

$GitBash = "C:\Program Files\Git\usr\bin\rsync.exe"
if (Test-Path $GitBash) {
    & $GitBash -avz --delete `
        --exclude='node_modules' --exclude='vendor' --exclude='.git' `
        --exclude='storage/logs/*' --exclude='storage/framework/cache/*' `
        --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' `
        --exclude='bootstrap/cache/*' --exclude='.env' --exclude='.env.*' `
        --exclude='*.pem' --exclude='.DS_Store' --exclude='Thumbs.db' `
        -e "ssh -i $(($KeyPath -replace '\\','/')) -o StrictHostKeyChecking=no" `
        "$(Resolve-Path $ProjectRoot)/" `
        "${Server}:${RemoteProject}/"
} else {
    Write-Host "  Using scp (slower than rsync)..." -ForegroundColor Yellow
    scp -i $KeyPath -o StrictHostKeyChecking=no -r "$ProjectRoot\DentalERP" "${Server}:${RemoteProject}/"
}
Write-Host "  ✓ Files uploaded" -ForegroundColor Green

# Step 4: Configure environment
Write-Host "[4/8] Configuring environment..." -ForegroundColor Blue

$EnvContent = @"
APP_NAME=DentalERP
APP_ENV=staging
APP_KEY=base64:0AUqeIwG3ZIkXiD+g4+wAGt1m6dS6C4D7xeP9nWcOpE=
APP_DEBUG=false
APP_URL=http://16.79.58.178:8080
APP_PORT=8080

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=ep-long-field-azkp8lnq.c-3.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_sKYcbX3LPd4I
DB_SCHEMA=public
DB_SSLMODE=require

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=16.79.58.178

SANCTUM_STATEFUL_DOMAINS=16.79.58.178:8080,mydentcare.com,*.mydentcare.com,localhost:5173
SANCTUM_ACCESS_TOKEN_TTL=60
FRONTEND_URL=https://mydentcare.com
CORS_ALLOWED_ORIGINS=https://mydentcare.com,http://localhost:5173,http://127.0.0.1:5173,http://16.79.58.178:8080

FILESYSTEM_DISK=local
AUDIT_QUEUE=audit
AUDIT_QUEUE_CONNECTION=database
AUDIT_RETENTION_DAYS=365
NOTIFICATION_QUEUE=notifications
NOTIFICATION_QUEUE_CONNECTION=database
NOTIFICATION_RETRY_ATTEMPTS=3

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
SECURITY_HSTS_ENABLED=false
"@

# Write env file locally then scp
$TempEnv = [System.IO.Path]::GetTempFileName() + ".env.staging"
[System.IO.File]::WriteAllText($TempEnv, $EnvContent)

scp -i $KeyPath -o StrictHostKeyChecking=no $TempEnv "${Server}:${RemoteProject}/DentalERP/.env.staging" | Out-Null
Remove-Item $TempEnv -Force
Write-Host "  ✓ Environment configured" -ForegroundColor Green

# Step 5: Build and start containers
Write-Host "[5/8] Building Docker containers..." -ForegroundColor Blue
ssh -i $KeyPath -o StrictHostKeyChecking=no $Server "cd $RemoteProject/DentalERP && docker compose -f docker/compose.staging.yaml down 2>/dev/null || true" | Out-Null
ssh -i $KeyPath -o StrictHostKeyChecking=no $Server "cd $RemoteProject/DentalERP && docker compose -f docker/compose.staging.yaml up -d --build"
Write-Host "  ✓ Containers started" -ForegroundColor Green

# Step 6: Wait for health
Write-Host "[6/8] Waiting for application to be healthy..." -ForegroundColor Blue
Start-Sleep -Seconds 15
$HealthCode = (Invoke-WebRequest -Uri "http://16.79.58.178:8080/up" -UseBasicParsing -Method Head -TimeoutSec 10).StatusCode
if ($HealthCode -eq 200) {
    Write-Host "  ✓ Application healthy" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Health check returned $HealthCode" -ForegroundColor Yellow
}

# Step 7: Run migrations
Write-Host "[7/8] Running database migrations..." -ForegroundColor Blue
ssh -i $KeyPath -o StrictHostKeyChecking=no $Server "cd $RemoteProject/DentalERP && docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate:fresh --force"
Write-Host "  ✓ Migrations completed" -ForegroundColor Green

# Step 8: Seed demo data
Write-Host "[8/8] Seeding demo data..." -ForegroundColor Blue
ssh -i $KeyPath -o StrictHostKeyChecking=no $Server "cd $RemoteProject/DentalERP && docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=DemoSeeder"
ssh -i $KeyPath -o StrictHostKeyChecking=no $Server "cd $RemoteProject/DentalERP && docker compose -f docker/compose.staging.yaml exec -T app php artisan db:seed --class=ExtendedDemoSeeder"
Write-Host "  ✓ Demo data seeded" -ForegroundColor Green

# Summary
Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "  🚀 Deployment Complete!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "📍 Backend API:     http://16.79.58.178:8080" -ForegroundColor Cyan
Write-Host "   Health Check:    http://16.79.58.178:8080/up" -ForegroundColor Cyan
Write-Host ""
Write-Host "🔑 Demo Credentials:" -ForegroundColor Cyan
Write-Host "   Super Admin:  superadmin@demodental.com / password123" -ForegroundColor White
Write-Host "   Doctor:       drjane@demodental.com / password123" -ForegroundColor White
Write-Host "   Receptionist: sarah@demodental.com / password123" -ForegroundColor White
Write-Host ""
Write-Host "📊 Demo Data: 3 users, 3 patients, 8 appointments," -ForegroundColor Cyan
Write-Host "   5 treatments, 5 invoices, 8 inventory, 6 pharmacy," -ForegroundColor Cyan
Write-Host "   3 lab orders, 3 EMR, 15 odontograms, 3 radiology," -ForegroundColor Cyan
Write-Host "   3 CRM, 10 COA + 2 journal entries" -ForegroundColor Cyan
Write-Host ""
Write-Host "🔧 SSH: ssh -i `"$KeyPath`" $Server" -ForegroundColor Yellow
Write-Host ""
