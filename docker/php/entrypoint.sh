#!/usr/bin/env sh
set -euo pipefail

cd /var/www/html

# Ensure permissions
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Install dependencies if vendor is missing
if [ ! -d "vendor" ]; then
  if [ -f "composer.json" ]; then
    composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader || true
  fi
fi

# Build frontend assets if missing
if [ -f "package.json" ]; then
  if [ ! -d "node_modules" ]; then
    npm ci --no-audit --no-fund --quiet || npm install --no-audit --no-fund --quiet || true
  fi
  # If using Laravel Vite plugin, built assets go to public/build
  if [ ! -d "public/build" ]; then
    npm run build || true
  fi
fi

# App key
if [ ! -f ".env" ]; then
  if [ -f ".env.docker" ]; then
    cp .env.docker .env
  else
    echo "APP_NAME=\"ایلی استور\"" > .env
    echo "APP_ENV=production" >> .env
    echo "APP_KEY=" >> .env
    echo "APP_DEBUG=false" >> .env
    echo "APP_URL=https://iliywstore.ir" >> .env
    echo "" >> .env
    echo "DB_CONNECTION=mysql" >> .env
    echo "DB_HOST=db" >> .env
    echo "DB_PORT=3306" >> .env
    echo "DB_DATABASE=iliywstore_db" >> .env
    echo "DB_USERNAME=iliywstore" >> .env
    echo "DB_PASSWORD=secret" >> .env
  fi
fi

# Generate APP_KEY if not exists or empty
if ! grep -q "^APP_KEY=" .env || [ -z "$(grep '^APP_KEY=' .env | cut -d= -f2- | tr -d ' ')" ]; then
  php artisan key:generate --force --no-interaction
fi

# Create storage symlink if it doesn't exist
if [ ! -L "public/storage" ]; then
  php artisan storage:link || true
fi

# Cache config/routes for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"


