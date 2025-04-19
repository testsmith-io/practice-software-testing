#!/bin/sh

echo "🚧 Fixing Laravel storage/cache permissions..."

su root -c "
  mkdir -p storage/framework/{views,cache,sessions} bootstrap/cache &&
  chown -R www-data:www-data storage bootstrap/cache &&
  chmod -R ug+rwX storage bootstrap/cache
"

echo "🚀 Starting PHP-FPM as www-data..."
exec php-fpm
