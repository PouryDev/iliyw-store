# مراحل حل مشکل 500 در Production

## مشکل
خطای `Declaration of App\Actions\Order\ReduceStockAction::execute(...$params): void must be compatible with App\Actions\BaseAction::execute(...$params): mixed`

## علت
فایل‌های قدیمی روی سرور production هستند که return type `void` دارند به جای `mixed`.

## راه حل

### 1️⃣ آپلود فایل‌های به‌روز شده به سرور

فایل‌های زیر را باید به سرور production آپلود کنید:

```
app/Actions/Order/ReduceStockAction.php
app/Actions/Order/RestoreStockAction.php
app/Repositories/Eloquent/BaseRepository.php
app/Repositories/Contracts/RepositoryInterface.php
```

**با Git:**
```bash
# روی سیستم لوکال
git add .
git commit -m "Fix: Update action return types and add missing repository methods"
git push origin main

# روی سرور production
cd /path/to/iliyw-store
git pull origin main
```

**یا با FTP/SFTP:**
فایل‌های بالا را از سیستم لوکال به مسیر مشابه روی سرور کپی کنید.

### 2️⃣ اجرای دستورات روی سرور Production

**الف) اگر از Docker استفاده می‌کنید:**

```bash
cd /path/to/iliyw-store

# 1. Composer autoload
docker-compose exec app composer dump-autoload -o

# 2. Clear Laravel caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# 3. Restart PHP container (برای پاک کردن OPcache)
docker-compose restart app
```

**ب) اگر از PHP-FPM معمولی استفاده می‌کنید:**

```bash
cd /path/to/iliyw-store

# 1. Composer autoload
composer dump-autoload -o

# 2. Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 3. Restart PHP-FPM (برای پاک کردن OPcache)
sudo systemctl restart php8.2-fpm
# یا
sudo systemctl restart php-fpm
# یا اگر از cPanel/Plesk استفاده می‌کنید، از پنل آن PHP-FPM را restart کنید
```

### 3️⃣ تست

بعد از انجام مراحل بالا، این endpoint را تست کنید:

```bash
curl https://your-domain.com/api/payment/gateways
```

باید لیست درگاه‌های پرداخت را برگرداند بدون خطای 500.

---

## نکات مهم

1. **همیشه قبل از تغییرات، backup بگیرید**
2. اگر از **Cloudflare** استفاده می‌کنید، cache آن را هم clear کنید
3. اگر از **Nginx cache** استفاده می‌کنید:
   ```bash
   sudo rm -rf /var/cache/nginx/*
   sudo systemctl reload nginx
   ```
4. اگر باز هم مشکل دارید، لاگ Laravel را چک کنید:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

## فایل‌های تغییر یافته در این commit

- ✅ `app/Actions/Order/ReduceStockAction.php` - return type: void → mixed
- ✅ `app/Actions/Order/RestoreStockAction.php` - return type: void → mixed  
- ✅ `app/Repositories/Eloquent/BaseRepository.php` - added getAllPaginated, newQuery
- ✅ `app/Repositories/Contracts/RepositoryInterface.php` - added method signatures

