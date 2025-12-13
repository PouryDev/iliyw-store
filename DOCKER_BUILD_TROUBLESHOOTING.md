# Docker Build Troubleshooting Guide

## SSL/Package Fetch Errors During Build

If you encounter SSL errors or "Permission denied" when fetching Alpine packages during `docker compose up -d --build`, follow these solutions in order:

### Error Symptoms

```
error:0A000126:SSL routines::unexpected eof while reading
WARNING: fetching https://dl-cdn.alpinelinux.org: Permission denied
ERROR: unable to select packages
```

## Solutions

### Solution 1: Updated Dockerfile (Applied)

The Dockerfile has been updated to:

-   Update package index explicitly
-   Install and update ca-certificates for SSL
-   Use `--update` flag with apk add

**Try rebuilding:**

```bash
docker compose build --no-cache
docker compose up -d
```

### Solution 2: Network Connectivity Issues

If the error persists, check network connectivity from your server:

```bash
# Test DNS resolution
nslookup dl-cdn.alpinelinux.org

# Test HTTPS connectivity
curl -I https://dl-cdn.alpinelinux.org/alpine/v3.22/main/x86_64/APKINDEX.tar.gz

# Check if ports 80/443 are open for outbound connections
```

**Fix:** Ensure your firewall allows outbound HTTPS (443) connections.

### Solution 3: Use Alternative Alpine Mirror

If the default Alpine CDN is blocked, use an alternative mirror by creating a `.docker-build-env` file or modifying build args:

**Option A: Build with build arg**

Create a build script or modify docker-compose.yml to use build args:

```yaml
php:
    build:
        context: .
        dockerfile: Dockerfile
        args:
            - ALPINE_MIRROR=https://mirror.alpinelinux.org
```

**Option B: Use HTTP instead of HTTPS** (less secure, but works if SSL is blocked)

Modify Dockerfile temporarily to use HTTP mirrors (only for testing):

```dockerfile
# Add before package installation
RUN echo "http://dl-cdn.alpinelinux.org/alpine/v3.22/main" > /etc/apk/repositories && \
    echo "http://dl-cdn.alpinelinux.org/alpine/v3.22/community" >> /etc/apk/repositories
```

### Solution 4: Update Alpine Version

The error might be due to Alpine v3.22 repository issues. Try pinning to a stable version:

**Check current Alpine version:**

```bash
docker run --rm php:8.3-fpm-alpine cat /etc/alpine-release
```

**Or explicitly use a specific Alpine base:**

```dockerfile
FROM php:8.3-fpm-alpine3.19 AS base
```

### Solution 5: Build with Docker BuildKit

Use Docker BuildKit which handles network issues better:

```bash
DOCKER_BUILDKIT=1 docker compose build --no-cache
DOCKER_BUILDKIT=1 docker compose up -d
```

### Solution 6: Build in Stages (Offline Packages)

If network issues persist, you can pre-download packages:

```bash
# On a machine with internet access
docker build -t temp-builder --target base -f Dockerfile .
docker run --rm temp-builder tar czf /tmp/alpine-packages.tar.gz /var/cache/apk

# Copy to production server and extract in Dockerfile
```

### Solution 7: Use Multi-Stage Build with Retry Logic

Add retry logic to handle transient network issues:

```dockerfile
RUN set -eux; \
    for i in 1 2 3; do \
        apk update --no-cache && break || sleep 5; \
    done; \
    apk add --no-cache ca-certificates \
    && update-ca-certificates
```

### Solution 8: Check Docker Daemon Configuration

Ensure Docker daemon can access external repositories:

```bash
# Check Docker daemon logs
sudo journalctl -u docker.service | tail -50

# Check if Docker is behind a proxy
cat /etc/systemd/system/docker.service.d/http-proxy.conf
```

## Verification Steps

After applying a solution, verify the build works:

```bash
# Clean build
docker compose down
docker system prune -f

# Rebuild
docker compose build --no-cache php

# Check if image was created
docker images | grep jemeh-shop-php

# Start services
docker compose up -d
```

## Alternative: Build on Different Machine

If all else fails:

1. **Build on a machine with stable internet:**

    ```bash
    docker build -t jemeh-shop-php:prod .
    docker save jemeh-shop-php:prod > jemeh-shop-php.tar
    ```

2. **Transfer to production server:**
   `scp jemeh-shop-php.tar user@production-server:/tmp/`

3. **Load on production:**
    ```bash
    docker load < /tmp/jemeh-shop-php.tar
    docker tag jemeh-shop-php:prod jemeh-shop-php:prod
    docker compose up -d
    ```

## Production Server Checklist

-   [ ] Outbound HTTPS (443) is allowed in firewall
-   [ ] DNS resolution works (`nslookup dl-cdn.alpinelinux.org`)
-   [ ] Docker has internet access
-   [ ] No proxy blocking Alpine repositories
-   [ ] Sufficient disk space for build
-   [ ] Docker daemon is running and healthy

## Common Fixes Summary

| Issue                  | Solution                        |
| ---------------------- | ------------------------------- |
| SSL certificate errors | Install/update ca-certificates  |
| Network timeout        | Check firewall, use retry logic |
| DNS resolution fails   | Check DNS settings              |
| Permission denied      | Check Docker daemon permissions |
| Package not found      | Update package index explicitly |

## Getting Help

If none of these solutions work, provide:

1. Full error output
2. Docker version: `docker --version`
3. Server OS: `uname -a`
4. Network test: `curl -I https://dl-cdn.alpinelinux.org`
5. DNS test: `nslookup dl-cdn.alpinelinux.org`
