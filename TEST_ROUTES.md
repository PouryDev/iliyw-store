# 🧪 تست Routes - تشخیص مشکل 404

## تغییرات انجام شده

1. ✅ یک test route ساده اضافه شد: `/test-payment`
2. ✅ CSRF middleware از callback route حذف شد با `->withoutMiddleware()`

## 🔍 مراحل تست

### مرحله 1: آپلود و Clear Cache

```bash
# روی سرور production
cd /path/to/iliyw-store

# آپلود routes/web.php جدید (از طریق git یا FTP)
git pull origin main

# Clear کامل cache
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Re-cache
php artisan route:cache
```

### مرحله 2: تست Route ساده

باز کن در مرورگر:
```
https://iliywstore.ir/test-payment
```

**نتیجه مورد انتظار:**
- ✅ اگر `TEST WORKS! web.php is loaded` رو دیدی = `web.php` load میشه، مشکل از CSRF بوده
- ❌ اگر 404 دیدی = مشکل جدی‌تره، `web.php` load نمیشه

### مرحله 3: تست Payment Callback

باز کن در مرورگر:
```
https://iliywstore.ir/payment/callback/zibal?success=1&status=2&trackId=test
```

**نتیجه مورد انتظار:**
- ✅ اگر به `/payment/error` redirect شد = Route کار میکنه!
- ❌ اگر 404 دید = مشکل همچنان هست

### مرحله 4: بررسی Routes

```bash
php artisan route:list | grep -E "test-payment|payment/callback"

# باید ببینی:
# GET|HEAD  test-payment                                       › Closure
# GET|HEAD  payment/callback/{gateway}  payment.callback      › Api\PaymentController@callback
```

## 🎯 تشخیص مشکل

### سناریو 1: test-payment کار میکنه ✅
✅ یعنی `web.php` load میشه  
✅ مشکل از CSRF بود که حل شد با `withoutMiddleware()`

### سناریو 2: test-payment هم 404 میده ❌
مشکلات احتمالی:
1. فایل `routes/web.php` روی سرور update نشده
2. Route cache کامل پاک نشده
3. مشکل در Nginx/Apache config
4. مشکل در Laravel bootstrap

**راه حل:**
```bash
# چک کن فایل update شده
head -30 routes/web.php | grep test-payment

# اگر نیست، دوباره آپلود کن
scp routes/web.php user@server:/path/to/iliyw-store/routes/

# یا با vim/nano مستقیماً ویرایش کن
vim routes/web.php

# پاک کردن کامل cache
rm -rf bootstrap/cache/*.php
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
# یا
docker-compose restart app
```

## 🔧 اگر test-payment کار کرد ولی callback نه

یعنی:
- `web.php` load میشه ✅
- Route تعریف شده ✅
- ولی callback route match نمیشه ❌

**احتمالات:**
1. Regex در catch-all route هنوز مشکل داره
2. Order routes اشتباهه
3. Middleware دیگه‌ای مشکل ساز هست

**راه حل:**
```bash
# ببین کدوم route match میشه
php artisan route:list --path=payment/callback

# Debug کن
php debug-routes.php
```

## 📋 Checklist نهایی

- [ ] `routes/web.php` جدید روی سرور هست
- [ ] `php artisan route:clear` اجرا شد
- [ ] `php artisan route:cache` اجرا شد
- [ ] `/test-payment` تست شد
- [ ] `/payment/callback/zibal?success=1` تست شد
- [ ] نتیجه به من گفته شد 😊

## 📞 نتیجه رو بگو

بعد از تست، این 2 تا URL رو باز کن و نتیجه رو بهم بگو:

1. `https://iliywstore.ir/test-payment`
2. `https://iliywstore.ir/payment/callback/zibal?success=1`

چی دیدی؟

