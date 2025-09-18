#!/bin/bash
set -e

echo "=== Manual Deployment Script for CBHLC ==="
echo "Starting deployment at $(date)"

# 1. Put the application in maintenance mode
echo "Step 1: Putting application in maintenance mode..."
ssh cbhlc "cd /home/forge/default && php artisan down --retry=60"

# 2. Pull the latest code
echo "Step 2: Pulling latest code from GitHub..."
ssh cbhlc "cd /home/forge/default && git pull origin main"

# 3. Install/update composer dependencies
echo "Step 3: Installing composer dependencies..."
ssh cbhlc "cd /home/forge/default && composer install --optimize-autoloader --no-dev"

# 4. Install/update npm dependencies and build assets
echo "Step 4: Installing npm dependencies and building assets..."
ssh cbhlc "cd /home/forge/default && npm ci && NODE_ENV=production npx vite build"

# 5. Run database migrations
echo "Step 5: Running database migrations..."
ssh cbhlc "cd /home/forge/default && php artisan migrate --force"

# 6. Clear and rebuild caches
echo "Step 6: Clearing and rebuilding caches..."
ssh cbhlc "cd /home/forge/default && php artisan config:cache && php artisan route:cache && php artisan view:cache"

# 7. Restart queue workers (if any)
echo "Step 7: Restarting queue workers..."
ssh cbhlc "cd /home/forge/default && php artisan queue:restart || true"

# 8. Take the application out of maintenance mode
echo "Step 8: Taking application out of maintenance mode..."
ssh cbhlc "cd /home/forge/default && php artisan up"

echo "Deployment completed successfully at $(date)"
