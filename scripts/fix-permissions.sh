#!/bin/bash

# Fix permissions for Laravel Sail development environment
# This script should be run after cloning the repository or when
# encountering permission errors with wayfinder:generate

echo "Fixing permissions for Laravel Sail..."

# Fix permissions for resources/js directory (wayfinder generates TypeScript files here)
./vendor/bin/sail exec laravel.test chown -R sail:sail resources/js

# Fix permissions for storage and bootstrap/cache directories
./vendor/bin/sail exec laravel.test chown -R sail:sail storage bootstrap/cache

echo "Permissions fixed successfully!"
echo ""
echo "You can now run: ./vendor/bin/sail npm run dev"
