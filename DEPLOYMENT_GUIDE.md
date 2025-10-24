# 🚀 راهنمای کامل Deploy و راه‌اندازی iliyw Store

این راهنما تمام مراحل لازم برای deploy کردن پروژه iliyw Store را شامل می‌شود.

---

## 📋 فهرست مطالب

1. [پیش‌نیازها](#پیش-نیازها)
2. [آماده‌سازی سرور](#آماده-سازی-سرور)
3. [Clone و Setup پروژه](#clone-و-setup-پروژه)
4. [Build و اجرای Docker](#build-و-اجرای-docker)
5. [نصب SSL](#نصب-ssl)
6. [راه‌اندازی دیتابیس](#راه-اندازی-دیتابیس)
7. [تست و بررسی](#تست-و-بررسی)

---

## 🔧 پیش‌نیازها

### سرور

-   **OS**: Ubuntu 20.04+ یا Debian 11+
-   **RAM**: حداقل 2GB (توصیه: 4GB)
-   **Storage**: حداقل 20GB
-   **CPU**: حداقل 2 Core

### نرم‌افزارهای مورد نیاز

```bash
# نصب Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# نصب Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# بررسی نصب
docker --version
docker-compose --version
```

### تنظیمات DNS

قبل از شروع، مطمئن شوید دامنه‌های زیر به IP سرور شما اشاره می‌کنند:

-   `iliyw.store` → IP سرور
-   `www.iliyw.store` → IP سرور

---

## 🖥️ آماده‌سازی سرور

### 1. به‌روزرسانی سیستم

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl wget nano htop ufw
```

### 2. تنظیمات Firewall

```bash
# فعال‌سازی UFW
sudo ufw enable

# باز کردن پورت‌های لازم
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 443/tcp    # HTTPS

# بررسی وضعیت
sudo ufw status
```

### 3. ایجاد دایرکتوری پروژه

```bash
# ایجاد کاربر جدید (اختیاری)
sudo useradd -m -s /bin/bash iliyw
sudo usermod -aG docker iliyw
sudo su - iliyw

# یا استفاده از کاربر فعلی
mkdir -p ~/projects
cd ~/projects
```

---

## 📥 Clone و Setup پروژه

### 1. Clone کردن پروژه

```bash
cd ~/projects
git clone https://github.com/yourusername/iliw-store.git
cd iliw-store
```

### 2. ایجاد فایل .env.docker

```bash
# کپی از نمونه
cp .env.example .env.docker

# ویرایش فایل
nano .env.docker
```

**محتوای .env.docker:**

```env
APP_NAME="iliyw Store"
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_KEY
APP_DEBUG=false
APP_URL=https://iliyw.store

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=iliw_store_db
DB_USERNAME=iliw
DB_PASSWORD=YOUR_SECURE_PASSWORD_HERE

DB_ROOT_PASSWORD=YOUR_ROOT_PASSWORD_HERE

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@iliyw.store
MAIL_FROM_NAME="iliyw Store"
```

### 3. Generate APP_KEY

```bash
# روش 1: با PHP در سرور
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"

# روش 2: با Docker
docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan key:generate --show

# کپی کردن KEY به .env.docker
```

---

## 🐳 Build و اجرای Docker

### 1. Build کردن تصاویر

```bash
cd ~/projects/iliw-store

# Build
docker-compose build

# بررسی تصاویر
docker images | grep iliw
```

### 2. اجرای سرویس‌ها (بدون SSL)

```bash
# اجرای در background
docker-compose up -d

# مشاهده لاگ‌ها
docker-compose logs -f

# بررسی وضعیت
docker-compose ps
```

باید همه سرویس‌ها "Up" باشند:

-   ✅ php
-   ✅ nginx
-   ✅ db
-   ✅ backup

### 3. تست اولیه

```bash
# تست HTTP (موقتاً)
curl -I http://YOUR_SERVER_IP

# باید HTTP 200 را ببینید
```

---

## 🔐 نصب SSL

برای نصب SSL، مراحل کامل را در فایل [SSL_SETUP_GUIDE.md](./SSL_SETUP_GUIDE.md) مطالعه کنید.

### خلاصه مراحل:

```bash
# 1. توقف nginx
docker-compose stop nginx

# 2. دریافت گواهی
docker-compose --profile ssl-setup up lego

# 3. تغییر config nginx به SSL
nano docker-compose.yml
# در بخش nginx volumes، uncomment کنید:
#   - ./docker/nginx/default-ssl.conf:/etc/nginx/conf.d/default.conf:ro

# 4. Restart با SSL
docker-compose down
docker-compose up -d

# 5. تست HTTPS
curl -I https://iliyw.store
```

---

## 🗄️ راه‌اندازی دیتابیس

### 1. اجرای Migrations

```bash
# ورود به container PHP
docker-compose exec php bash

# اجرای migrations
php artisan migrate --force

# خروج
exit
```

### 2. Seeding دیتا (محیط توسعه)

```bash
docker-compose exec php bash

# اجرای seeders (برای تست)
php artisan db:seed --force

# یا فقط production seeder
php artisan db:seed --class=ProductionSeeder --force

exit
```

### 3. ایجاد کاربر ادمین

```bash
docker-compose exec php bash

php artisan tinker

# در tinker:
$admin = \App\Models\User::create([
    'name' => 'ادمین',
    'phone' => '09123456789',
    'instagram_id' => '@admin',
    'password' => bcrypt('YOUR_SECURE_PASSWORD'),
    'is_admin' => true
]);

# خروج: Ctrl+D
exit
```

---

## ✅ تست و بررسی

### 1. بررسی سایت

```bash
# تست صفحه اصلی
curl -I https://iliyw.store

# تست API
curl https://iliyw.store/api/products

# تست admin (از مرورگر)
# https://iliyw.store/admin
```

### 2. بررسی لاگ‌ها

```bash
# لاگ Laravel
docker-compose exec php tail -f storage/logs/laravel.log

# لاگ nginx
docker-compose logs -f nginx

# لاگ database
docker-compose logs -f db
```

### 3. بررسی storage

```bash
# تست آپلود تصویر
docker-compose exec php ls -la storage/app/public

# ایجاد symbolic link اگر لازم باشد
docker-compose exec php php artisan storage:link
```

### 4. بررسی Performance

```bash
# Cache config
docker-compose exec php php artisan config:cache

# Cache routes
docker-compose exec php php artisan route:cache

# Cache views
docker-compose exec php php artisan view:cache

# Optimize
docker-compose exec php php artisan optimize
```

---

## 🔄 دستورات مفید

### مدیریت Container ها

```bash
# Restart همه
docker-compose restart

# Restart فقط nginx
docker-compose restart nginx

# Stop همه
docker-compose stop

# Start همه
docker-compose start

# مشاهده لاگ یک سرویس خاص
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f db

# پاک کردن همه و شروع مجدد
docker-compose down
docker-compose up -d
```

### دیتابیس

```bash
# Backup دستی
docker-compose exec db mysqldump -u iliw -p iliw_store_db > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T db mysql -u iliw -p iliw_store_db < backup_20241024.sql

# اتصال به MySQL CLI
docker-compose exec db mysql -u iliw -p iliw_store_db
```

### Laravel Artisan

```bash
# کلیه دستورات artisan
docker-compose exec php php artisan [command]

# مثال‌ها:
docker-compose exec php php artisan migrate
docker-compose exec php php artisan db:seed
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan queue:work
```

---

## 📊 Monitoring و Maintenance

### 1. تنظیم Cron Jobs

```bash
# Backup خودکار (روزانه 3 صبح)
crontab -e

# اضافه کنید:
0 3 * * * cd ~/projects/iliw-store && docker-compose exec -T db mysqldump -u iliw -p'PASSWORD' iliw_store_db > ~/backups/iliw_$(date +\%Y\%m\%d).sql

# SSL Renewal (هر 60 روز)
0 3 1 */2 * /usr/local/bin/renew-iliw-ssl.sh >> /var/log/iliw-ssl-renewal.log 2>&1
```

### 2. Monitoring Resources

```bash
# مصرف منابع
docker stats

# فضای دیسک
df -h
docker system df

# پاک‌سازی (احتیاط کنید!)
docker system prune -a
```

---

## 🐛 رفع مشکلات

### مشکل: سایت 502 Bad Gateway نشان می‌دهد

```bash
# بررسی PHP container
docker-compose ps
docker-compose logs php

# Restart PHP
docker-compose restart php
```

### مشکل: دیتابیس وصل نمی‌شود

```bash
# بررسی DB container
docker-compose ps db
docker-compose logs db

# تست اتصال
docker-compose exec php php artisan tinker
# در tinker: DB::connection()->getPdo();
```

### مشکل: Permission Denied

```bash
# تنظیم ownership
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache

# تنظیم permissions
docker-compose exec php chmod -R 775 storage bootstrap/cache
```

---

## 📚 منابع اضافی

-   [Laravel Documentation](https://laravel.com/docs)
-   [Docker Documentation](https://docs.docker.com/)
-   [nginx Configuration](https://nginx.org/en/docs/)
-   [Let's Encrypt](https://letsencrypt.org/)

---

## ✅ Checklist نهایی

-   [ ] سرور آماده و به‌روز شده
-   [ ] Docker و Docker Compose نصب شده
-   [ ] پروژه clone شده
-   [ ] .env.docker تنظیم شده
-   [ ] Docker images build شده
-   [ ] سرویس‌ها در حال اجرا هستند
-   [ ] SSL نصب و فعال شده
-   [ ] Migrations اجرا شده
-   [ ] کاربر ادمین ساخته شده
-   [ ] سایت با HTTPS کار می‌کند
-   [ ] Backup خودکار تنظیم شده
-   [ ] Monitoring فعال است

---

🎉 **تبریک! پروژه iliyw Store با موفقیت deploy شد!**

برای سوالات و مشکلات، به داکیومنت‌های مربوطه مراجعه کنید یا issue باز کنید.
