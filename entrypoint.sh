#!/bin/sh
set -e

# Start SSH service
service ssh start

# Fix storage permissions at runtime
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cache config, routes, and views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run Laravel migrations automatically on startup
php artisan migrate --force || true

# Start Apache in the foreground
apache2-foreground
