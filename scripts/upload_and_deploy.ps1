# Upload & Deploy to Server
# Usage: .\scripts\upload_and_deploy.ps1
# This will:
#   1. Copy .env.staging.production + deploy.sh to server via SCP
#   2. SSH in and run deploy.sh
# If SSH is blocked from this network, it will generate ready-to-use commands

$ErrorActionPreference = "Stop"

$PEM_FILE = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
$AWS_IP = "108.136.48.83"
$SSH_USER = "ubuntu"
$PEM = "-o StrictHostKeyChecking=no -o ConnectTimeout=10 -i `"$PEM_FILE`""

function Write-Step { param($msg) Write-Host "`n[>] $msg" -ForegroundColor Cyan }
function Write-Ok { param($msg) Write-Host "  OK: $msg" -ForegroundColor Green }
function Write-Err { param($msg) Write-Host "  ERR: $msg" -ForegroundColor Red }
function Write-Warn { param($msg) Write-Host "  WARN: $msg" -ForegroundColor Yellow }

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Upload & Deploy - My Dent Care" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Check PEM
if (-not (Test-Path $PEM_FILE)) {
    Write-Err "PEM file not found: $PEM_FILE"
    exit 1
}

# Try SSH connectivity
Write-Step "Testing SSH connectivity to $AWS_IP..."
$sshTest = Test-NetConnection -ComputerName $AWS_IP -Port 22 -WarningAction SilentlyContinue -InformationLevel Quiet
if (-not $sshTest) {
    Write-Warn "Cannot reach server on port 22 from this network."
    Write-Warn "Please run these commands directly from a terminal that can SSH:"
    Write-Host ""
    Write-Host "# Step 1: Upload the env file" -ForegroundColor Yellow
    Write-Host "scp -i `"$PEM_FILE`" `"c:\Users\ifan.setiawan_klikde\Documents\My Dent Care\DentalERP\.env.staging.production`" ubuntu@$AWS_IP`:~/My-Dent-Care/DentalERP/.env.staging.production" -ForegroundColor White
    Write-Host ""
    Write-Host "# Step 2: Upload deploy script" -ForegroundColor Yellow
    Write-Host "scp -i `"$PEM_FILE`" `"c:\Users\ifan.setiawan_klikde\Documents\My Dent Care\scripts\deploy.sh`" ubuntu@$AWS_IP`:~/My-Dent-Care/scripts/deploy.sh" -ForegroundColor White
    Write-Host ""
    Write-Host "# Step 3: SSH in and run deploy" -ForegroundColor Yellow
    Write-Host "ssh -i `"$PEM_FILE`" ubuntu@$AWS_IP" -ForegroundColor White
    Write-Host "cd ~/My-Dent-Care && bash scripts/deploy.sh" -ForegroundColor White
    Write-Host ""
    exit 0
}

Write-Ok "Server reachable on port 22"

# Upload .env.staging.production
Write-Step "Uploading .env.staging.production..."
$scpEnv = "scp $PEM `"c:\Users\ifan.setiawan_klikde\Documents\My Dent Care\DentalERP\.env.staging.production`" $SSH_USER@$AWS_IP`:~/My-Dent-Care/DentalERP/.env.staging.production"
Invoke-Expression $scpEnv 2>&1 | ForEach-Object { Write-Host "  $_" -ForegroundColor Gray }
Write-Ok ".env.staging.production uploaded"

# Upload deploy.sh
Write-Step "Uploading deploy.sh..."
$scpDeploy = "scp $PEM `"c:\Users\ifan.setiawan_klikde\Documents\My Dent Care\scripts\deploy.sh`" $SSH_USER@$AWS_IP`:~/My-Dent-Care/scripts/deploy.sh"
Invoke-Expression $scpDeploy 2>&1 | ForEach-Object { Write-Host "  $_" -ForegroundColor Gray }
Write-Ok "deploy.sh uploaded"

# Run deploy.sh
Write-Step "Running deploy.sh on server..."
$sshCmd = "ssh $PEM $SSH_USER@$AWS_IP `"cd ~/My-Dent-Care && chmod +x scripts/deploy.sh && bash scripts/deploy.sh`""
Invoke-Expression $sshCmd 2>&1 | ForEach-Object { Write-Host "  $_" -ForegroundColor Gray }

Write-Host "`n==========================================" -ForegroundColor Green
Write-Host "  DEPLOY COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
