# Iliywstore

A minimal, modern e‑commerce (Laravel 12 + Tailwind) showcasing products, cart, checkout, and a lightweight admin panel. Persian (fa) localization is enabled by default. Specialized in accessories and paintings/tables.

## Features

-   Modern UI with RTL layout, Persian validation messages
-   Product catalog, product images, slug routing
-   Cart (session-based), checkout with receipt upload
-   Orders, invoices, transactions data model
-   User account area (orders, settings, password)
-   Admin panel: products CRUD, orders management, status updates, payment verification
-   Reusable Blade UI components: inputs, textarea, checkbox, select, file (with preview), button
-   Mobile-first responsive tables and layouts

## Tech Stack

-   Backend: Laravel 12 (PHP 8.3)
-   Frontend: Blade, Tailwind (via Vite; CDN fallback on prod)
-   Database: MariaDB 11
-   Web: Nginx + PHP-FPM
-   Docker: Multi-service setup (php, nginx, db, backup)

## Quick Start (Docker)

1. Prepare environment

```bash
cp .env.example .env
# set DB_*, APP_URL, etc.
```

2. Build & run

```bash
docker compose up -d --build
```

Services:

-   php: php-fpm running Laravel
-   nginx: serves `public/`
-   db: MariaDB (persistent)
-   backup: daily dumps at 03:00 (persistent)

3. First-run tasks (entrypoint does most automatically)

-   Generates APP_KEY if missing
-   Caches config/routes/views

4. Access

-   App: http://localhost

## Volumes & Persistence

-   `db_data`: MariaDB data files (do not lose on container restart)
-   `storage_data`: Laravel storage (uploads, cache, logs)
-   `db_backups`: Compressed SQL dumps

## Backups

A lightweight cron-based backup container creates a full dump daily at 03:00.

-   Script: `docker/backup/backup.sh`
-   Output: `/backups/backup-YYYYmmdd-HHMMSS.sql.gz` and `/backups/latest.sql.gz`

Restore example (DANGER: overwrites data):

```bash
docker exec -i $(docker compose ps -q db) \
  sh -c 'gunzip -c /backups/latest.sql.gz | mysql -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'
```

## Production Notes

-   Ensure `.env` contains secure secrets and correct `APP_URL`
-   Put real domain behind reverse proxy with HTTPS (or extend Nginx for TLS)
-   Run `docker compose pull && docker compose up -d --build` to deploy updates
-   Database and storage are persisted; backups rotate by timestamp and `latest.sql.gz` symlink always points to the newest

## Local Development (without Docker)

```bash
php -v # 8.2/8.3
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run dev
php artisan serve
```

## Scripts

-   `composer test`: run tests
-   `composer dev`: run local server + queue + logs + vite (via concurrently)

## License

MIT
