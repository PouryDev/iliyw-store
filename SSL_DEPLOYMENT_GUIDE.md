# SSL Deployment Guide - jemeshop.ir

This guide explains how to set up SSL certificates using lego (Let's Encrypt) and deploy your application with HTTPS on your server.

## Prerequisites

-   Docker and Docker Compose installed on your server
-   Domain `jemeshop.ir` and `www.jemeshop.ir` pointing to your server's IP address
-   Ports 80 and 443 open in your server firewall
-   Email address for Let's Encrypt notifications

## Overview

The setup uses **lego** (Let's Encrypt client) to automatically generate SSL certificates. However, there's a port conflict during initial certificate generation:

-   **nginx** needs port 80 for HTTP traffic
-   **lego** needs port 80 for HTTP challenge validation

The solution is to generate certificates first, then run the full stack.

## Step-by-Step Deployment

### 1. Environment Configuration

First, ensure your `.env.docker` file is properly configured. You can run the setup script:

```bash
./setup-docker-env.sh
```

Add the Let's Encrypt email to your environment (optional, defaults to `admin@jemeshop.ir`):

```bash
echo "LETSENCRYPT_EMAIL=your-email@example.com" >> .env.docker
```

### 2. Initial Certificate Generation

During the first deployment, you need to generate SSL certificates. This requires temporarily stopping nginx so lego can use port 80.

#### Option A: Generate Certificates First (Recommended)

```bash
# Stop all containers
docker compose down

# Start only lego and required services (db if needed)
docker compose up -d db

# Wait for database to be ready, then start lego
docker compose up -d lego

# Monitor lego logs to see certificate generation
docker compose logs -f lego

# Wait until you see success message (certificates generated)
# Then stop lego
docker compose stop lego

# Now start the full stack (nginx will handle port 80 now)
docker compose up -d
```

#### Option B: Generate Certificates with nginx Stopped

If nginx is already running:

```bash
# Stop nginx
docker compose stop nginx

# Start lego (it will use port 80)
docker compose up -d lego

# Monitor certificate generation
docker compose logs -f lego

# After certificates are generated, stop lego
docker compose stop lego

# Restart nginx
docker compose start nginx

# Start all other services
docker compose up -d
```

### 3. Verify Certificate Generation

Check that certificates were created:

```bash
# Inspect the certs volume
docker compose exec lego ls -la /etc/letsencrypt/certificates/

# You should see:
# - jemeshop.ir.crt
# - jemeshop.ir.key
# - jemeshop.ir.issuer.crt
```

Or verify from host:

```bash
docker volume inspect jemeh-shop_certs
```

### 4. Start Full Stack

Once certificates are generated, start all services:

```bash
docker compose up -d
```

### 5. Verify HTTPS is Working

Test your domain:

```bash
# Check HTTP redirect
curl -I http://jemeshop.ir

# Should return: HTTP/1.1 301 Moved Permanently
# And redirect to HTTPS

# Check HTTPS connection
curl -I https://jemeshop.ir

# Should return: HTTP/2 200
```

### 6. Stop Lego Service (Optional but Recommended)

After initial certificate generation, you can remove lego from auto-start since certificates are valid for 90 days. For renewal, you can run lego manually when needed.

**Option 1: Comment out in docker-compose.yml**

```yaml
# lego:
#   image: docker.arvancloud.ir/goacme/lego:latest
#   ...
```

**Option 2: Keep but don't auto-start**

The lego service has `restart: unless-stopped`, but since it runs once and exits, it won't restart automatically.

## Certificate Renewal

Let's Encrypt certificates expire after 90 days. Renew them before expiration:

### Manual Renewal Process

1. **Stop nginx temporarily:**

    ```bash
    docker compose stop nginx
    ```

2. **Start lego with renew command:**

    ```bash
    # Edit docker-compose.yml temporarily to change lego command
    # Or run lego manually:
    docker compose run --rm lego sh -c 'lego --email="your-email@example.com" --domains="jemeshop.ir" --domains="www.jemeshop.ir" --path="/etc/letsencrypt" --http renew'
    ```

3. **Or simply restart lego container:**
    ```bash
    docker compose stop nginx
    docker compose up -d lego
    docker compose logs -f lego
    # After renewal completes:
    docker compose stop lego
    docker compose start nginx
    ```

### Automated Renewal (Advanced)

You can set up a cron job on your host to renew certificates automatically:

```bash
# Add to crontab (crontab -e)
# Renew certificates every 60 days at 3 AM
0 3 */60 * * cd /path/to/jemeh-shop && docker compose stop nginx && docker compose up -d lego && sleep 60 && docker compose stop lego && docker compose start nginx && docker compose restart nginx
```

Or create a renewal script:

```bash
#!/bin/bash
cd /path/to/jemeh-shop
docker compose stop nginx
docker compose up -d lego
sleep 120  # Wait for renewal
docker compose stop lego
docker compose start nginx
docker compose restart nginx  # Reload nginx to pick up new certificates
```

## Production Deployment Checklist

-   [ ] Domain DNS records point to server IP
-   [ ] Ports 80 and 443 are open in firewall
-   [ ] `.env.docker` is configured with production values
-   [ ] Database credentials are set correctly
-   [ ] SSL certificates are generated
-   [ ] HTTPS is working correctly
-   [ ] HTTP redirects to HTTPS
-   [ ] All services are running: `docker compose ps`
-   [ ] Application is accessible via HTTPS
-   [ ] Nginx SSL configuration is correct

## Troubleshooting

### Port 80 Already in Use

If you get a port conflict error:

```bash
# Check what's using port 80
sudo lsof -i :80
# or
sudo netstat -tulpn | grep :80

# Stop the conflicting service or adjust docker-compose.yml
```

### Certificate Generation Fails

Common issues:

1. **Domain not pointing to server:**

    ```bash
    # Verify DNS
    dig jemeshop.ir
    nslookup jemeshop.ir
    ```

2. **Firewall blocking port 80:**

    ```bash
    # Allow port 80
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    ```

3. **Check lego logs:**
    ```bash
    docker compose logs lego
    ```

### Nginx Can't Find Certificates

If nginx fails to start because certificates are missing:

1. Verify certificates exist in volume:

    ```bash
    docker compose exec nginx ls -la /etc/letsencrypt/certificates/
    ```

2. Check volume mounting in docker-compose.yml (should be mounted as `certs:/etc/letsencrypt:ro`)

3. Restart nginx:
    ```bash
    docker compose restart nginx
    ```

### Certificate Expired

If certificates have expired:

1. Follow the renewal process above
2. Restart nginx after renewal:
    ```bash
    docker compose restart nginx
    ```

### SSL Configuration Issues

Check nginx SSL configuration:

```bash
# Test nginx configuration
docker compose exec nginx nginx -t

# View nginx error logs
docker compose logs nginx
```

## Docker Compose Services

-   **php**: Laravel application (PHP-FPM)
-   **nginx**: Web server with SSL support
-   **db**: MariaDB database
-   **backup**: Automated database backups
-   **lego**: SSL certificate generation (run on-demand)

## Environment Variables

Required environment variables (in `.env.docker`):

```bash
# Database
DB_DATABASE=jemeh_shop_db
DB_USERNAME=jemeh
DB_PASSWORD=your_secure_password
DB_ROOT_PASSWORD=your_root_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jemeshop.ir
APP_KEY=your_generated_key

# SSL Certificate Generation (Optional)
LETSENCRYPT_EMAIL=your-email@example.com
```

## Monitoring

Check service status:

```bash
# View all services
docker compose ps

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f nginx
docker compose logs -f php
```

## Security Notes

1. **Never expose `.env.docker` in version control**
2. **Use strong database passwords**
3. **Keep Docker and images updated**
4. **Monitor certificate expiration** (set reminders)
5. **Use firewall rules** to restrict access
6. **Regular backups** are handled by the backup service

## Support

If you encounter issues:

1. Check service logs: `docker compose logs`
2. Verify DNS configuration
3. Check firewall settings
4. Ensure ports 80 and 443 are accessible
5. Review nginx error logs

---

**Last Updated**: After SSL/lego integration
**Domain**: jemeshop.ir
**SSL Provider**: Let's Encrypt (via lego)
