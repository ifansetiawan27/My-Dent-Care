# Build Frontend for Production
# Usage: .\scripts\build_frontend.ps1

$ErrorActionPreference = "Stop"

$FRONTEND_DIR = "c:\Users\ifan.setiawan_klikde\Documents\My Dent Care\frontend"

function Write-Step { param($msg) Write-Host "`n[>] $msg" -ForegroundColor Cyan }
function Write-Ok { param($msg) Write-Host "  OK: $msg" -ForegroundColor Green }
function Write-Err { param($msg) Write-Host "  ERR: $msg" -ForegroundColor Red }

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Frontend Build - My Dent Care" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Check .env.production exists
$envFile = Join-Path $FRONTEND_DIR ".env.production"
if (-not (Test-Path $envFile)) {
    Write-Err ".env.production not found!"
    exit 1
}
Write-Ok ".env.production found"

# Install dependencies
Write-Step "Installing dependencies..."
Set-Location $FRONTEND_DIR
$installOutput = npm install 2>&1
if ($LASTEXITCODE -ne 0) { Write-Err "npm install failed"; exit 1 }
Write-Ok "Dependencies installed"

# Build
Write-Step "Building frontend..."
$buildOutput = npm run build 2>&1
if ($LASTEXITCODE -ne 0) { Write-Err "Build failed"; Write-Host $buildOutput; exit 1 }
Write-Ok "Build complete"

Write-Host "`n==========================================" -ForegroundColor Green
Write-Host "  FRONTEND BUILD COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Output: frontend/dist/" -ForegroundColor White
Write-Host ""
Write-Host "To serve locally for testing:" -ForegroundColor Yellow
Write-Host "  npx serve frontend/dist" -ForegroundColor Gray
Write-Host ""
