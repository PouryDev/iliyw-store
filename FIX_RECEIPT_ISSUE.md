# 🔧 حل مشکل Receipt در Checkout

## ❌ مشکل قبلی
هنگام انتخاب درگاه آنلاین (غیر از کارت به کارت)، سیستم خطا میداد که باید `receipt` ارسال شود.

## 🔍 علت مشکل
1. **Frontend**: همیشه `receipt` را به FormData اضافه میکرد حتی اگر `null` بود
2. **Backend Validation**: `receipt` همیشه optional بود ولی validation برای card-to-card شرطی نبود

## ✅ تغییرات انجام شده

### 1. Frontend: `resources/js/components/CheckoutPage.jsx`

**قبل:**
```javascript
formData.append('receipt', form.receipt); // همیشه اضافه میشد
```

**بعد:**
```javascript
if (form.receipt) {
    formData.append('receipt', form.receipt); // فقط اگر وجود داشته باشد
}
```

### 2. Backend: `app/Http/Requests/Api/CheckoutRequest.php`

**قبل:**
```php
'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
```

**بعد:**
```php
// Conditional validation based on gateway type
$rules['receipt'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];

if ($this->input('payment_gateway_id')) {
    $gateway = \App\Models\PaymentGateway::find($this->input('payment_gateway_id'));
    if ($gateway && $gateway->type === 'card_to_card') {
        $rules['receipt'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];
    }
}
```

**همچنین پیام‌های خطای فارسی اضافه شد:**
```php
'receipt.required' => 'آپلود فیش واریزی برای پرداخت کارت به کارت الزامی است',
'receipt.file' => 'فیش واریزی باید یک فایل معتبر باشد',
'receipt.mimes' => 'فیش واریزی باید از نوع jpg، jpeg، png یا pdf باشد',
'receipt.max' => 'حجم فیش واریزی نباید بیشتر از 10 مگابایت باشد',
```

## 📋 مراحل اعمال تغییرات

### روی سیستم لوکال:
```bash
cd /home/pk/Projects/e-commerce/iliyw-store

# 1. Build frontend
npm run build

# 2. Commit changes
git add .
git commit -m "Fix: Receipt field should only be required for card-to-card payment"
git push origin main
```

### روی سرور Production:
```bash
cd /path/to/iliyw-store

# 1. Pull changes
git pull origin main

# 2. Build frontend (if Node.js is available)
npm run build

# OR copy built assets from local:
# scp -r public/build/* user@server:/path/to/iliyw-store/public/build/

# 3. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🎯 نتیجه

حالا validation به این صورت کار میکنه:

- ✅ **درگاه آنلاین (Zarinpal, Zibal, ...)**: `receipt` اختیاری - ارسال نمیشه
- ✅ **کارت به کارت**: `receipt` الزامی - باید فیش واریزی آپلود بشه

## 📝 فایل‌های تغییر یافته

1. `resources/js/components/CheckoutPage.jsx`
2. `app/Http/Requests/Api/CheckoutRequest.php`

این دو فایل باید روی production آپلود بشن.

