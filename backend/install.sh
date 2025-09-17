#!/bin/bash

# Hayya Al Salah Backend Installation Script

echo "=========================================="
echo "Hayya Al Salah Backend Installation"
echo "=========================================="

# Check PHP version
echo "Checking PHP version..."
php_version=$(php -v | head -1 | cut -d' ' -f2 | cut -d'.' -f1,2)
required_version="8.0"

if [ "$(printf '%s\n' "$required_version" "$php_version" | sort -V | head -n1)" = "$required_version" ]; then
    echo "✓ PHP $php_version detected (>= 8.0 required)"
else
    echo "✗ PHP 8.0 or higher is required. Current version: $php_version"
    exit 1
fi

# Check if Composer is installed
echo "Checking Composer..."
if command -v composer &> /dev/null; then
    echo "✓ Composer is installed"
else
    echo "✗ Composer is not installed. Please install Composer first."
    echo "Visit: https://getcomposer.org/download/"
    exit 1
fi

# Install dependencies
echo "Installing PHP dependencies..."
composer install

if [ $? -eq 0 ]; then
    echo "✓ Dependencies installed successfully"
else
    echo "✗ Failed to install dependencies"
    exit 1
fi

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    echo "✓ .env file created from .env.example"
    echo "⚠️  Please configure your database settings in .env file"
else
    echo "✓ .env file already exists"
fi

# Create directories
echo "Creating required directories..."
mkdir -p storage/uploads
mkdir -p storage/logs
chmod 755 storage
chmod 755 storage/uploads
chmod 755 storage/logs
echo "✓ Storage directories created"

echo ""
echo "=========================================="
echo "Installation completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Configure your database settings in .env file"
echo "2. Run the migration script: php scripts/migrate.php"
echo "3. Start the development server: php -S localhost:8000 -t public"
echo ""
echo "Default admin credentials:"
echo "Email: admin@hayyaalsalah.com"
echo "Password: admin123"
echo ""
echo "API will be available at: http://localhost:8000/api"
echo "Documentation: See API_DOCUMENTATION.md"
echo ""
