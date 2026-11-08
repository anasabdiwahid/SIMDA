@echo off
title SIMDA WhatsApp Gateway
cd /d "%~dp0"
set "PATH=C:\xampp\nodejs;%PATH%"
if not exist node_modules (
    echo Installing packages...
    call npm install --no-fund --no-audit
)
echo.
echo  SIMDA WhatsApp Gateway
echo  Open http://127.0.0.1:3001 to scan the QR code. Keep this window open.
echo.
start "" http://127.0.0.1:3001
node server.js
pause
