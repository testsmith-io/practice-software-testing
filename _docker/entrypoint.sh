#!/bin/sh

echo "🔧 Fixing permissions..."
mkdir -p storage/framework/{views,sessions,cache} bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

echo "👤 Dropping to www-data..."
exec su-exec www-data php-fpm
