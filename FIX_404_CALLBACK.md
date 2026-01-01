# 🔧 حل مشکل 404 در Payment Callback

## ❌ مشکل
بعد از پرداخت، هنگام برگشت از درگاه به URL زیر، خطای 404 دریافت میشه:
```
https://iliywstore.ir/payment/callback/zibal?success=1&status=2&trackId=4420631585
```

## 🔍 علت مشکل
Route callback در `routes/web.php` تعریف شده ولی روی سرور production **route cache** شده و route جدید رو نمی‌شناسه.

## ✅ راه حل

### مرحله 1: آپلود فایل‌ها (اگر هنوز نکردی)

```bash
# روی سیستم لوکال
cd /home/pk/Projects/e-commerce/iliyw-store
git add .
git commit -m "Fix: Add payment callback route and update checkout validation"
git push origin main
```

```bash
# روی سرور
cd /path/to/iliyw-store
git pull origin main
```

### مرحله 2: پاک کردن Route Cache

**الف) با Docker:**
```bash
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

**ب) بدون Docker:**
```bash
cd /path/to/iliyw-store
php artisan route:clear
php artisan route:cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

### مرحله 3: تست Route

```bash
# لیست تمام routes را ببین
php artisan route:list | grep callback

# باید این route را ببینی:
# GET|HEAD  payment/callback/{gateway}  payment.callback  › App\Http\Controllers\Api\PaymentController@callback
```

## 📋 بررسی Routes

Route callback در `routes/web.php` خط 65-66 تعریف شده:

```php
Route::get('/payment/callback/{gateway}', [\App\Http\Controllers\Api\PaymentController::class, 'callback'])
    ->name('payment.callback');
```

این route باید قبل از catch-all route باشه (که در خط 69-71 هست).

## 🎯 فایل‌های مربوط

1. ✅ `routes/web.php` - callback route تعریف شده
2. ✅ `app/Http/Controllers/Api/PaymentController.php` - callback method وجود داره

## ⚠️ نکات مهم

1. **همیشه بعد از تغییر routes، cache را clear کنید**
2. اگر از **Nginx** استفاده می‌کنید، cache آن را هم clear کنید:
   ```bash
   sudo rm -rf /var/cache/nginx/*
   sudo systemctl reload nginx
   ```
3. اگر از **Cloudflare** استفاده می‌کنید، cache صفحه را purge کنید

## 🧪 تست

بعد از انجام مراحل بالا، این URL را مستقیماً در مرورگر باز کنید:
```
https://iliywstore.ir/payment/callback/zibal?success=1&status=2&trackId=test123
```

نباید 404 بگیرید. باید به صفحه `/payment/error` یا `/thanks/` redirect بشید.

## 🆘 اگر باز هم 404 میگیرید

1. چک کنید که فایل `routes/web.php` روی سرور update شده:
   ```bash
   grep -n "payment/callback" routes/web.php
   ```

2. چک کنید که route cache شده درست باشه:
   ```bash
   cat bootstrap/cache/routes-v7.php | grep callback
   ```

3. Restart کامل PHP-FPM:
   ```bash
   sudo systemctl restart php8.2-fpm
   # یا
   docker-compose restart app
   ```

4. چک کردن لاگ‌ها:
   ```bash
   tail -100 storage/logs/laravel.log
   ```




