# PowerShell wrapper untuk restart containers
# Usage: .\restart_aws.ps1

$PEM_FILE = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
$AWS_IP = "108.136.48.83"
$BASH_PEM = "/mnt/c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem"

Write-Host "Restarting containers on AWS EC2..." -ForegroundColor Cyan

bash.exe -c "cd '/mnt/c/Users/ifan.setiawan_klikde/Documents/My Dent Care' && chmod +x scripts/aws_restart.sh && ./scripts/aws_restart.sh '$BASH_PEM' '$AWS_IP'"

Write-Host ""
Write-Host "Containers restarted successfully!" -ForegroundColor Green
