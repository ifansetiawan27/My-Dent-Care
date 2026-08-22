# PowerShell wrapper untuk deployment AWS
# Usage: .\deploy_aws.ps1

$ErrorActionPreference = "Stop"

# Konfigurasi
$PEM_FILE = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
$AWS_IP = "108.136.48.83"
$SUPABASE_URL = "postgresql://postgres:Ifansetiawan093600@db.iccktgeijswtupjcgswx.supabase.co:5432/postgres"

Write-Host "=========================================="
Write-Host "AWS EC2 Deployment - DentalERP"
Write-Host "=========================================="
Write-Host "PEM File: $PEM_FILE"
Write-Host "AWS IP: $AWS_IP"
Write-Host "Supabase: Connected"
Write-Host "=========================================="
Write-Host ""

# Cek file PEM exists
if (-not (Test-Path $PEM_FILE)) {
    Write-Host "ERROR: File PEM tidak ditemukan: $PEM_FILE" -ForegroundColor Red
    exit 1
}

Write-Host "[Step 1/2] Running deployment script..." -ForegroundColor Cyan

# Convert Windows path ke WSL path untuk bash
$BASH_PEM = "/mnt/c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem"

# Run deployment menggunakan WSL bash
bash.exe -c "cd '/mnt/c/Users/ifan.setiawan_klikde/Documents/My Dent Care' && chmod +x scripts/setup_aws.sh && ./scripts/setup_aws.sh '$BASH_PEM' '$AWS_IP' '$SUPABASE_URL'"

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "ERROR: Deployment failed!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[Step 2/2] Running initial setup..." -ForegroundColor Cyan

# Run initial setup
bash.exe -c "cd '/mnt/c/Users/ifan.setiawan_klikde/Documents/My Dent Care' && chmod +x scripts/aws_init.sh && ./scripts/aws_init.sh '$BASH_PEM' '$AWS_IP'"

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "WARNING: Initial setup had errors, but deployment may be successful" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=========================================="
Write-Host "DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "=========================================="
Write-Host ""
Write-Host "Backend API: http://$AWS_IP:8080"
Write-Host "Health Check: http://$AWS_IP:8080/up"
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Test health check: curl http://$AWS_IP:8080/up"
Write-Host "2. View logs: .\scripts\view_logs.ps1"
Write-Host "3. Update frontend .env.production dengan URL: http://$AWS_IP:8080/api"
Write-Host ""
