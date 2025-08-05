#!/bin/bash
set -e

echo "🚀 Starting deployment..."

cd /var/www/Mygcash

# Pull latest code from GitHub
echo "📥 Pulling latest code..."
git pull origin main

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install and build frontend
echo "🛠 Building frontend..."
npm install
npm run build

# Run migrations (force for production)
echo "🗄 Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart Nginx
echo "🔄 Restarting Nginx..."
sudo systemctl restart nginx

echo "✅ Deployment complete!"