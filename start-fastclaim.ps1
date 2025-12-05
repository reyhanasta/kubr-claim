# FastClaim Auto-Start Script
# Jalankan via Task Scheduler dengan: powershell -ExecutionPolicy Bypass -File "path\to\start-fastclaim.ps1"

# Set environment variables untuk Laragon (tanpa mengubah system-wide)
$env:PATH = "C:\laragon\bin\php\php-8.3.12-nts-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\git\bin;$env:PATH"

# Set working directory
Set-Location "D:\Web Development Reyhan\kubr-claim"

# Build assets terlebih dahulu
Write-Host "Building assets..." -ForegroundColor Cyan
npm run build

# Clear & cache config
Write-Host "Optimizing Laravel..." -ForegroundColor Cyan
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Start server dan queue secara paralel
Write-Host "Starting FastClaim Server..." -ForegroundColor Green
Start-Process -NoNewWindow powershell -ArgumentList "-Command", "php artisan serve --host=192.168.18.9 --port=8088"
Start-Process -NoNewWindow powershell -ArgumentList "-Command", "php artisan queue:listen --tries=3 --timeout=120"

Write-Host "FastClaim is running at http://192.168.18.9:8088" -ForegroundColor Yellow
Write-Host "Press Ctrl+C to stop..." -ForegroundColor Gray

# Keep script alive
while ($true) { Start-Sleep -Seconds 60 }
