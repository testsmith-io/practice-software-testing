@echo off
REM 🔍 CS423 API Testing Script for Windows
REM This script helps you run API tests locally for the CS423 assignment

setlocal enabledelayedexpansion

echo 🎓 ===== CS423 API Testing Script =====
echo.

:check_docker
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running. Please start Docker and try again.
    pause
    exit /b 1
)

:check_app
echo [INFO] Checking if application is running...
curl -s http://localhost:8091/status >nul 2>&1
if not errorlevel 1 (
    echo [SUCCESS] Application is running on localhost:8091
    goto menu
) else (
    echo [WARNING] Application is not running. Starting it now...
    goto start_app
)

:start_app
echo [INFO] Starting Practice Software Testing application...
set DISABLE_LOGGING=true
docker compose -f docker-compose.yml up -d --force-recreate

echo [INFO] Waiting for services to start (this may take 2-3 minutes)...
timeout /t 90 /nobreak >nul

echo [INFO] Setting up database with seed data...
docker compose exec -T laravel-api php artisan migrate:refresh --seed

echo [INFO] Performing health check...
for /l %%i in (1,1,10) do (
    curl -s http://localhost:8091/status >nul 2>&1
    if not errorlevel 1 (
        echo [SUCCESS] Application is ready!
        goto menu
    )
    echo [INFO] Waiting... (attempt %%i/10)
    timeout /t 10 /nobreak >nul
)

echo [ERROR] Application failed to start properly
pause
exit /b 1

:menu
echo.
echo 🔍 Choose what to test:
echo 1) 🔐 Authentication API
echo 2) 🏷️  Brands Search API (35 test cases)
echo 3) 📂 Categories Search API (35 test cases)
echo 4) 📋 Invoice Status API (40 test cases)
echo 5) 🚀 Run ALL API Tests (110+ test cases)
echo 6) 🐳 Start/Restart Application
echo 7) 🧹 Clean Reports
echo 8) 📊 Open Reports Directory
echo 9) ❌ Exit
echo.
set /p choice=Enter your choice (1-9): 

if "%choice%"=="1" goto test_auth
if "%choice%"=="2" goto test_brands
if "%choice%"=="3" goto test_categories
if "%choice%"=="4" goto test_invoices
if "%choice%"=="5" goto test_all
if "%choice%"=="6" goto start_app
if "%choice%"=="7" goto clean_reports
if "%choice%"=="8" goto open_reports
if "%choice%"=="9" goto exit
echo [ERROR] Invalid choice. Please try again.
goto menu

:test_auth
echo [INFO] Running Authentication tests...
if not exist "reports" mkdir reports
npm run api:test:auth
if not errorlevel 1 (
    echo [SUCCESS] Authentication tests completed successfully
) else (
    echo [WARNING] Authentication tests completed with some failures
)
goto continue

:test_brands
echo [INFO] Running Brands Search tests...
if not exist "reports" mkdir reports
npm run api:test:brands
if not errorlevel 1 (
    echo [SUCCESS] Brands Search tests completed successfully
) else (
    echo [WARNING] Brands Search tests completed with some failures
)
goto continue

:test_categories
echo [INFO] Running Categories Search tests...
if not exist "reports" mkdir reports
npm run api:test:categories
if not errorlevel 1 (
    echo [SUCCESS] Categories Search tests completed successfully
) else (
    echo [WARNING] Categories Search tests completed with some failures
)
goto continue

:test_invoices
echo [INFO] Running Invoice Status tests...
if not exist "reports" mkdir reports
npm run api:test:invoices
if not errorlevel 1 (
    echo [SUCCESS] Invoice Status tests completed successfully
) else (
    echo [WARNING] Invoice Status tests completed with some failures
)
goto continue

:test_all
echo [INFO] Running ALL API tests - this will take several minutes...
if not exist "reports" mkdir reports
npm run api:test:all
echo [SUCCESS] All API tests completed! Check reports/ directory for results.
goto continue

:clean_reports
echo [INFO] Cleaning reports directory...
if exist "reports" rmdir /s /q reports
mkdir reports
echo [SUCCESS] Reports directory cleaned
goto continue

:open_reports
if exist "reports" (
    explorer reports
) else (
    echo [WARNING] No reports directory found. Run some tests first!
)
goto continue

:continue
echo.
pause
goto menu

:exit
echo [SUCCESS] Thanks for using CS423 API Testing Script!
pause
exit /b 0
