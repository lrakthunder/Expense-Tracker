@echo off
REM Thunder Expense Tracker - Start Laravel Backend and Vite Frontend

REM Start Laravel backend on port 8002
start "Laravel Server" cmd /k "php artisan serve --host=127.0.0.1 --port=8002"

REM Wait a moment for backend to start
timeout /t 3 > nul

REM Start Vite frontend on port 8002
start "Vite Dev Server" cmd /k "npm run dev -- --port 8002"

REM Open browser to app
REM Wait a moment for frontend to start
timeout /t 3 > nul
start http://thunderexpensetracker.com:8002

echo Thunder Expense Tracker is running!
pause
