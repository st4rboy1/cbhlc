#!/bin/bash

# Local Development Setup Script for CBHLC
# This script sets up the entire development environment from scratch

set -e  # Exit on error

sudo echo ''
echo "🚀 Starting local development environment setup..."
echo ""

# Stop and remove existing containers and volumes
echo "📦 Stopping existing containers and removing volumes..."
./vendor/bin/sail down --volumes

# Start containers in detached mode
echo "🐳 Starting Docker containers..."
./vendor/bin/sail up -d

# Wait for containers to be fully ready
echo "⏳ Waiting for containers to be ready (30 seconds)..."
sleep 30

# Fix permissions on resources/js directory
echo "🔧 Fixing permissions on resources/js directory..."
sudo chown -R $USER:$USER resources/js/

# Install PHP dependencies
echo "📚 Installing Composer dependencies..."
./vendor/bin/sail composer install --ignore-platform-reqs

# Run migrations and seeders
echo "🗄️  Running database migrations and seeders..."
./vendor/bin/sail artisan migrate:fresh --seed

# Install Node dependencies
echo "📦 Installing NPM dependencies..."
./vendor/bin/sail npm install

# Start Vite dev server
echo "⚡ Starting Vite dev server..."
echo ""
echo "✅ Development environment is ready!"
echo "🌐 Application should be available at: http://localhost"
echo "🔥 Vite dev server starting..."
echo ""
./vendor/bin/sail npm run dev
