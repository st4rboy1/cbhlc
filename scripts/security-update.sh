#!/bin/bash
# Security dependency update script
# This script will be run by GitHub Actions to update vulnerable packages

echo "🔒 Running security vulnerability check..."
composer audit

echo "📦 Updating dependencies to latest secure versions..."
composer update --with-dependencies --no-interaction

echo "✅ Dependencies updated. Running tests to verify..."
./vendor/bin/pest --no-coverage

echo "🎉 Security update complete!"
