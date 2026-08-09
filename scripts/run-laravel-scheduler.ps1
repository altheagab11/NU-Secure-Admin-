# Runs Laravel's scheduler every minute (needed for automated daily reports).
# Register in Windows Task Scheduler:
#   Trigger: At startup / At log on — repeat every 1 minute indefinitely
#   Action:  powershell.exe -ExecutionPolicy Bypass -File "C:\Laravel\NU-Secure-Admin-\scripts\run-laravel-scheduler.ps1"
#
# Or keep a terminal open with:
#   php artisan schedule:work

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot

Set-Location $projectRoot
& php artisan schedule:run
