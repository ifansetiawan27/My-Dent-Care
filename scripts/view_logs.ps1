# PowerShell wrapper untuk melihat logs
# Usage: .\view_logs.ps1

$PEM_FILE = "C:\Users\ifan.setiawan_klikde\Downloads\Ifansetiawan093600.pem"
$AWS_IP = "108.136.48.83"
$BASH_PEM = "/mnt/c/Users/ifan.setiawan_klikde/Downloads/Ifansetiawan093600.pem"

Write-Host "Connecting to AWS EC2 and streaming logs..." -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

bash.exe -c "cd '/mnt/c/Users/ifan.setiawan_klikde/Documents/My Dent Care' && chmod +x scripts/aws_logs.sh && ./scripts/aws_logs.sh '$BASH_PEM' '$AWS_IP'"
