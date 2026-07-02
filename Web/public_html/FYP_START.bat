@echo off
title FYP RunTracker Startup
cd /d C:\laragon\www\runtracker-admin

echo ------------------------------------------
echo [1/5] Clearing Laravel System Cache...
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

echo.
echo [2/5] Refreshing Storage Symlink...
php artisan storage:unlink
php artisan storage:link

echo.
echo [3/5] Starting Laravel Server...
echo ------------------------------------------
:: Start serve in a separate window so the script can continue 
start /b php artisan serve --host=127.0.0.1 --port=8000

echo.
echo [4/5] Establishing Ngrok Tunnel...
echo Domain: obdulia-louvered-evolutionarily.ngrok-free.dev
:: This command starts ngrok using your specific permanent domain 
start /b ngrok http --domain=obdulia-louvered-evolutionarily.ngrok-free.dev 8000

echo.
echo [5/5] System Ready!
echo Your site is live at: https://obdulia-louvered-evolutionarily.ngrok-free.dev
echo ------------------------------------------
pause