# Production PHP-FPM image for Laravel
FROM php:8.3-fpm-alpine AS base

RUN set -eux; \
    apk add --no-cache bash icu-dev oniguruma-dev libzip-dev libpng-dev freetype-dev libjpeg-turbo-dev curl git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql intl mbstring zip gd bcmath \
    && apk add --no-cache libpng freetype libjpeg-turbo icu-libs oniguruma libzip \
    && apk del --no-network freetype-dev libjpeg-turbo-dev libpng-dev icu-dev oniguruma-dev libzip-dev

# Install Node.js and npm for building frontend assets
RUN set -eux; \
    apk add --no-cache nodejs npm

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Copy project (we will bind-mount in compose, but keep a copy for image-only runs)
COPY . /var/www/html

# Install dependencies
RUN set -eux; \
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# Optimize permissions for runtime directories
RUN set -eux; \
    mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Generate APP_KEY if not exists
RUN echo "APP_NAME=\"iliyw Store\"" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_KEY=" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=http://localhost" >> .env && \
    echo "" >> .env && \
    echo "DB_CONNECTION=mysql" >> .env && \
    echo "DB_HOST=db" >> .env && \
    echo "DB_PORT=3306" >> .env && \
    echo "DB_DATABASE=iliw_store" >> .env && \
    echo "DB_USERNAME=root" >> .env && \
    echo "DB_PASSWORD=rootpass" >> .env && \
    echo "LOG_CHANNEL=daily" >> .env && \
    php artisan key:generate --no-interaction || true

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]


