# ✅ حل مشکل 404 در Payment Callback

## 🔍 مشکل اصلی
Catch-all route در `web.php` با regex `.*` خیلی عمومی بود و همه URL ها را قبل از رسیدن به callback route می‌گرفت.

## ✅ تغییرات انجام شده در `routes/web.php`

### 1. انتقال Payment Callback Route به اول فایل
```php
// Payment callback route (for gateway redirects) - MUST BE FIRST!
Route::get('/payment/callback/{gateway}', [\App\Http\Controllers\Api\PaymentController::class, 'callback'])
    ->name('payment.callback');
```

### 2. اضافه کردن Route برای Payment Error
```php
// Payment error page (React app)
Route::get('/payment/error', function () {
    return view('react-app');
})->name('payment.error');
```

### 3. اضافه کردن Route برای Thanks Page
```php
// Thanks page (React app)
Route::get('/thanks/{invoice}', function () {
    return view('react-app');
})->name('thanks');
```

### 4. محدود کردن Catch-All Route
```php
// React SPA route - catch all for frontend routes (must be last)
// Exclude payment/* and thanks/* routes from catch-all
Route::get('/{any}', function () {
    return view('react-app');
})->where('any', '^(?!(payment|thanks)).*')->name('react-app');
```

**توضیح regex:** `^(?!(payment|thanks)).*` یعنی هر URL که با `payment` یا `thanks` شروع **نمی‌شود**.

## 📋 مراحل اعمال تغییرات روی Production

### مرحله 1: آپلود فایل
```bash
# روی سیستم لوکال
cd /home/pk/Projects/e-commerce/iliyw-store
git add routes/web.php
git commit -m "Fix: Resolve 404 for payment callback by reordering routes"
git push origin main
```

### مرحله 2: Pull و Clear Cache روی سرور
```bash
# روی سرور production
cd /path/to/iliyw-store
git pull origin main

# Clear route cache
php artisan route:clear
php artisan route:cache
php artisan config:clear
php artisan cache:clear

# اگر از Docker استفاده می‌کنید:
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### مرحله 3: تست
```bash
# چک کردن لیست routes
php artisan route:list | grep -E "payment|thanks"

# باید این routes را ببینید:
# GET|HEAD  payment/callback/{gateway}  payment.callback  › Api\PaymentController@callback
# GET|HEAD  payment/error                payment.error     › Closure
# GET|HEAD  thanks/{invoice}             thanks            › Closure
```

## 🧪 تست عملکرد

### تست 1: Payment Callback
```
https://iliywstore.ir/payment/callback/zibal?success=1&status=2&trackId=4420631585
```
**نتیجه مورد انتظار:** باید به `/thanks/{invoice}` یا `/payment/error` redirect شود (نه 404)

### تست 2: Payment Error
```
https://iliywstore.ir/payment/error?message=test
```
**نتیجه مورد انتظار:** صفحه React app نمایش داده شود

### تست 3: Thanks Page
```
https://iliywstore.ir/thanks/INV-ABC12345
```
**نتیجه مورد انتظار:** صفحه React app نمایش داده شود

## 🎯 ترتیب Route ها (از بالا به پایین)

1. ✅ `/payment/callback/{gateway}` - اولویت بالا
2. ✅ `/payment/error` - صفحه خطا
3. ✅ `/thanks/{invoice}` - صفحه تشکر
4. ✅ `/test-session` - تست
5. ✅ `/checkout` - چک‌اوت
6. ✅ `/account` - اکانت
7. ✅ `/admin/*` - ادمین
8. ✅ `/{any}` - Catch-all (آخرین route)

## 🔄 Flow پرداخت

```
[کاربر] 
  → [Checkout Page] 
  → [POST /api/checkout] 
  → [POST /api/payment/initiate] 
  → [Redirect به درگاه] 
  → [کاربر پرداخت می‌کند]
  → [GET /payment/callback/zibal] ← اینجا 404 نمی‌گیره ✅
  → [Verify payment]
  → [Redirect to /thanks/{invoice}] یا [/payment/error]
```

## 📝 فایل‌های تغییر یافته

- ✅ `routes/web.php` - اصلاح ترتیب و regex routes

---

**نکته مهم:** همیشه بعد از تغییر routes، route cache را clear کنید!



