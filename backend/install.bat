@echo off
echo ==========================================
echo Hayya Al Salah Backend Installation
echo ==========================================

echo Checking PHP version...
php -v > nul 2>&1
if %errorlevel% neq 0 (
    echo X PHP is not installed or not in PATH
    echo Please install PHP 8.0 or higher
    pause
    exit /b 1
)

for /f "tokens=2 delims= " %%a in ('php -v ^| findstr /r "^PHP"') do set php_version=%%a
echo + PHP %php_version% detected

echo Checking Composer...
composer --version > nul 2>&1
if %errorlevel% neq 0 (
    echo X Composer is not installed or not in PATH
    echo Please install Composer first: https://getcomposer.org/download/
    pause
    exit /b 1
)
echo + Composer is installed

echo Installing PHP dependencies...
composer install
if %errorlevel% neq 0 (
    echo X Failed to install dependencies
    pause
    exit /b 1
)
echo + Dependencies installed successfully

if not exist .env (
    echo Creating .env file...
    copy .env.example .env
    echo + .env file created from .env.example
    echo ! Please configure your database settings in .env file
) else (
    echo + .env file already exists
)

echo Creating required directories...
mkdir storage\uploads 2>nul
mkdir storage\logs 2>nul
echo + Storage directories created

echo.
echo ==========================================
echo Installation completed!
echo ==========================================
echo.
echo Next steps:
echo 1. Configure your database settings in .env file
echo 2. Run the migration script: php scripts/migrate.php
echo 3. Start the development server: php -S localhost:8000 -t public
echo.
echo Default admin credentials:
echo Email: admin@hayyaalsalah.com
echo Password: admin123
echo.
echo API will be available at: http://localhost:8000/api
echo Documentation: See API_DOCUMENTATION.md
echo.
pause
