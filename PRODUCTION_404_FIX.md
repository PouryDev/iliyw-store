# 🔴 حل فوری مشکل 404 در Production

## مشکل
URL زیر روی سرور production خطای 404 لاراول میده:
```
https://iliywstore.ir/payment/callback/zibal?success=1&status=2&trackId=4420631585
```

## ✅ راه حل سریع (3 دقیقه)

### روش 1: اجرای Script خودکار

```bash
# 1. آپلود فایل‌ها به سرور
# فایل‌های زیر را به root پروژه آپلود کنید:
# - routes/web.php (جدید)
# - fix-production.sh
# - debug-routes.php

# 2. اجرای script
cd /path/to/iliyw-store
chmod +x fix-production.sh
./fix-production.sh
```

### روش 2: دستی

```bash
cd /path/to/iliyw-store

# مرحله 1: بررسی فایل routes/web.php
head -30 routes/web.php

# باید این خط را ببینید:
# Route::get('/payment/callback/{gateway}', [\App\Http\Controllers\Api\PaymentController::class, 'callback'])

# مرحله 2: پاک کردن تمام cache ها
php artisan route:clear
php artisan config:clear  
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear

# مرحله 3: ساخت cache جدید
php artisan route:cache
php artisan config:cache

# مرحله 4: بررسی routes
php artisan route:list | grep payment

# باید ببینید:
# GET|HEAD  payment/callback/{gateway} ... payment.callback › Api\PaymentController@callback
```

### روش 3: با Docker

```bash
cd /path/to/iliyw-store

docker-compose exec app php artisan route:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan optimize:clear

docker-compose exec app php artisan route:cache
docker-compose exec app php artisan config:cache

docker-compose exec app php artisan route:list | grep payment
```

## 🔍 Debug

اگر بعد از مراحل بالا هنوز 404 میگیرید، این script را اجرا کنید:

```bash
php debug-routes.php
```

این script دقیقاً به شما میگه مشکل کجاست.

## ⚠️ نکات بسیار مهم

### 1. چک کردن فایل routes/web.php روی سرور

```bash
# مطمئن شوید فایل جدید آپلود شده:
grep -n "payment/callback" routes/web.php

# باید چیزی شبیه این ببینید:
# 22:// Payment callback route (for gateway redirects) - MUST BE FIRST!
# 23:Route::get('/payment/callback/{gateway}', [\App\Http\Controllers\Api\PaymentController::class, 'callback'])
```

اگر این خط وجود نداره یا در خط 60+ هست، فایل رو دوباره آپلود کن!

### 2. Nginx Cache

اگر از Nginx استفاده می‌کنید:

```bash
# پاک کردن Nginx cache
sudo rm -rf /var/cache/nginx/*
sudo systemctl reload nginx
```

### 3. Cloudflare

اگر از Cloudflare استفاده می‌کنید:
- برو به Cloudflare Dashboard
- Caching > Configuration > Purge Everything

### 4. PHP-FPM Restart

بعد از تمام مراحل، PHP-FPM رو restart کن:

```bash
# برای PHP 8.2
sudo systemctl restart php8.2-fpm

# یا برای PHP 8.1
sudo systemctl restart php8.1-fpm

# یا با Docker
docker-compose restart app
```

## 🧪 تست نهایی

```bash
# تست 1: بررسی route
curl -I https://iliywstore.ir/payment/callback/zibal?success=1

# نباید 404 بگیرید. باید 302 (redirect) بگیرید

# تست 2: لاگ لاراول
tail -50 storage/logs/laravel.log
```

## 📋 Checklist

- [ ] فایل `routes/web.php` جدید روی سرور آپلود شده
- [ ] `php artisan route:clear` اجرا شد
- [ ] `php artisan route:cache` اجرا شد
- [ ] `php artisan route:list | grep payment` نتیجه درست داد
- [ ] Nginx cache پاک شد (اگر وجود دارد)
- [ ] Cloudflare cache پاک شد (اگر استفاده می‌کنید)
- [ ] PHP-FPM restart شد
- [ ] تست با curl موفق بود

## 🆘 اگر باز هم کار نکرد

1. **چک کن که controller وجود داره:**
```bash
ls -la app/Http/Controllers/Api/PaymentController.php
grep "public function callback" app/Http/Controllers/Api/PaymentController.php
```

2. **چک کن namespace درسته:**
```bash
head -5 app/Http/Controllers/Api/PaymentController.php
# باید ببینی: namespace App\Http\Controllers\Api;
```

3. **Composer autoload:**
```bash
composer dump-autoload -o
```

4. **لاگ error:**
```bash
tail -100 storage/logs/laravel.log
```

5. **لاگ Nginx/Apache:**
```bash
# Nginx
tail -100 /var/log/nginx/error.log

# Apache
tail -100 /var/log/apache2/error.log
```

## 📞 نتیجه رو بده

بعد از اجرای دستورات، نتیجه این دستور رو بفرست:

```bash
php artisan route:list | grep -i payment
```

و همچنین نتیجه:

```bash
curl -I https://iliywstore.ir/payment/callback/zibal?success=1
```

