# Docker Setup for iliyw Store

## Quick Start

1. **Setup Environment:**

    ```bash
    ./setup-docker-env.sh
    ```

2. **Build and Run:**

    ```bash
    docker compose up -d --build
    ```

3. **Run Migrations:**

    ```bash
    docker compose exec php php artisan migrate
    ```

4. **Seed Database (Optional):**
    ```bash
    docker compose exec php php artisan db:seed
    ```

## Environment Configuration

The project uses `.env.docker` file for Docker containers. This file contains:

-   **Database settings** for MariaDB container
-   **Application settings** for Laravel
-   **Cache and session drivers** configured for file-based storage
-   **Mail settings** for development

## Services

-   **php**: Laravel application (PHP 8.3-FPM)
-   **nginx**: Web server (port 80)
-   **db**: MariaDB database
-   **backup**: Automated database backups

## Troubleshooting

### Environment Sync Issues

If you get environment sync errors:

1. Make sure `.env.docker` exists:

    ```bash
    ./setup-docker-env.sh
    ```

2. Rebuild containers:
    ```bash
    docker compose down
    docker compose up -d --build
    ```

### Database Connection Issues

If database connection fails:

1. Check if database container is running:

    ```bash
    docker compose ps
    ```

2. Check database logs:

    ```bash
    docker compose logs db
    ```

3. Restart database:
    ```bash
    docker compose restart db
    ```

### Permission Issues

If you get permission errors:

1. Fix storage permissions:
    ```bash
    docker compose exec php chown -R www-data:www-data storage bootstrap/cache
    docker compose exec php chmod -R 775 storage bootstrap/cache
    ```

## Development Commands

```bash
# View logs
docker compose logs -f php

# Access PHP container
docker compose exec php bash

# Run Artisan commands
docker compose exec php php artisan [command]

# Install Composer packages
docker compose exec php composer install

# Install NPM packages
docker compose exec php npm install

# Build frontend assets
docker compose exec php npm run build
```

## Production Deployment

For production deployment:

1. Update `.env.docker` with production values
2. Set `APP_ENV=production`
3. Set `APP_DEBUG=false`
4. Update `APP_URL` to your domain
5. Configure proper database credentials
6. Run migrations and seeders
