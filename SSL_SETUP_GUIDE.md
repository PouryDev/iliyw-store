# 🔐 راهنمای نصب SSL برای iliyw Store

این راهنما مراحل کامل نصب گواهی SSL رایگان از Let's Encrypt را توضیح می‌دهد.

---

## 📋 پیش‌نیازها

قبل از شروع، مطمئن شوید:

1. ✅ دامنه `iliyw.store` و `www.iliyw.store` به IP سرور شما اشاره می‌کنند
2. ✅ پورت 80 و 443 روی فایروال سرور باز هستند
3. ✅ Docker و Docker Compose نصب شده‌اند
4. ✅ پروژه بر روی سرور کلون شده است

---

## 🚀 مراحل نصب SSL (بار اول)

### مرحله 1: Build و آماده‌سازی پروژه

```bash
# وارد دایرکتوری پروژه شوید
cd /path/to/iliw-store

# Build کردن تصاویر Docker
docker-compose build

# ایجاد فایل .env.docker (اگر وجود ندارد)
cp .env.example .env.docker

# ویرایش .env.docker و تنظیم APP_URL
nano .env.docker
# تنظیم کنید: APP_URL=https://iliyw.store
```

### مرحله 2: اجرای سرویس‌ها (بدون SSL)

```bash
# اجرای سرویس‌ها
docker-compose up -d

# بررسی وضعیت
docker-compose ps

# مشاهده لاگ‌ها
docker-compose logs -f
```

### مرحله 3: گرفتن گواهی SSL

```bash
# 1. توقف nginx برای آزاد کردن پورت 80
docker-compose stop nginx

# 2. اجرای lego برای دریافت گواهی
docker-compose --profile ssl-setup up lego

# منتظر بمانید تا پیغام موفقیت نمایش داده شود
# خروجی باید چیزی شبیه این باشد:
# [INFO] [iliyw.store] Server responded with a certificate

# 3. توقف lego
docker-compose --profile ssl-setup down lego
```

### مرحله 4: فعال‌سازی SSL در nginx

```bash
# 1. تعویض فایل nginx config
docker exec iliw-store-nginx-1 mv /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf.bak
docker cp docker/nginx/default-ssl.conf $(docker-compose ps -q nginx):/etc/nginx/conf.d/default.conf

# یا اینکه مستقیم فایل رو در سرور تغییر بدید:
# volumes section در docker-compose.yml:
#   - ./docker/nginx/default-ssl.conf:/etc/nginx/conf.d/default.conf:ro

# 2. Restart nginx با SSL config
docker-compose restart nginx

# 3. بررسی لاگ‌های nginx
docker-compose logs nginx
```

### مرحله 5: تست SSL

```bash
# تست از طریق curl
curl -I https://iliyw.store

# باید خروجی HTTP/2 200 را ببینید

# یا از مرورگر وارد شوید:
# https://iliyw.store
```

---

## 🔄 تمدید گواهی SSL (هر 60 روز)

گواهی‌های Let's Encrypt 90 روز اعتبار دارند. توصیه می‌شود هر 60 روز یکبار تمدید کنید.

### روش دستی:

```bash
# 1. توقف nginx
docker-compose stop nginx

# 2. تمدید گواهی
docker-compose run --rm lego \
  --email="pk74ever@gmail.com" \
  --domains="iliyw.store" \
  --domains="www.iliyw.store" \
  --path="/etc/letsencrypt" \
  --http \
  renew

# 3. شروع مجدد nginx
docker-compose start nginx
```

### روش خودکار (با Cron):

```bash
# ایجاد اسکریپت تمدید
cat > /usr/local/bin/renew-iliw-ssl.sh << 'EOF'
#!/bin/bash
cd /path/to/iliw-store
docker-compose stop nginx
docker-compose run --rm lego \
  --email="pk74ever@gmail.com" \
  --domains="iliyw.store" \
  --domains="www.iliyw.store" \
  --path="/etc/letsencrypt" \
  --http \
  renew
docker-compose start nginx
EOF

# قابل اجرا کردن
chmod +x /usr/local/bin/renew-iliw-ssl.sh

# اضافه کردن به crontab (هر 60 روز یکبار)
crontab -e
# اضافه کنید:
0 3 1 */2 * /usr/local/bin/renew-iliw-ssl.sh >> /var/log/iliw-ssl-renewal.log 2>&1
```

---

## 🛠️ رفع مشکلات رایج

### مشکل 1: خطای "Port 80 already in use"

**راه حل:**

```bash
# بررسی سرویسی که پورت 80 را اشغال کرده
sudo lsof -i :80
sudo netstat -tulpn | grep :80

# توقف nginx
docker-compose stop nginx

# سپس دوباره lego را اجرا کنید
```

### مشکل 2: خطای "DNS resolution failed"

**راه حل:**

-   مطمئن شوید DNS record های دامنه به IP سرور شما اشاره می‌کنند
-   منتظر بمانید تا DNS propagate شود (تا 24 ساعت)
-   تست کنید: `nslookup iliyw.store`

### مشکل 3: گواهی منقضی شده

**راه حل:**

```bash
# حذف گواهی قدیمی و دریافت جدید
docker volume rm iliw-store_certs
docker-compose --profile ssl-setup up lego
```

### مشکل 4: خطای Permission Denied

**راه حل:**

```bash
# اجرا با sudo
sudo docker-compose --profile ssl-setup up lego
```

---

## 📊 بررسی وضعیت گواهی

```bash
# مشاهده اطلاعات گواهی
docker run --rm -v iliw-store_certs:/certs alpine \
  ls -lah /certs/certificates/

# تاریخ انقضا
docker run --rm -v iliw-store_certs:/certs alpine \
  cat /certs/certificates/iliyw.store.json

# یا از openssl:
echo | openssl s_client -servername iliyw.store -connect iliyw.store:443 2>/dev/null | openssl x509 -noout -dates
```

---

## 📝 نکات امنیتی

1. ✅ همیشه از HTTPS استفاده کنید
2. ✅ HTTP را به HTTPS redirect کنید (در config موجود است)
3. ✅ گواهی را قبل از انقضا تمدید کنید
4. ✅ backup منظم از volume `certs` داشته باشید
5. ✅ لاگ‌های renewal را بررسی کنید

---

## 🔗 منابع مفید

-   [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
-   [Lego ACME Client](https://go-acme.github.io/lego/)
-   [SSL Labs Test](https://www.ssllabs.com/ssltest/)

---

## ✅ Checklist نصب

-   [ ] DNS records تنظیم شده
-   [ ] پورت 80 و 443 باز است
-   [ ] Docker و Docker Compose نصب شده
-   [ ] پروژه build شده
-   [ ] گواهی SSL دریافت شده
-   [ ] nginx config به SSL تغییر کرده
-   [ ] سایت با HTTPS کار می‌کند
-   [ ] cron job برای renewal تنظیم شده
-   [ ] backup از گواهی‌ها گرفته شده

---

🎉 **تبریک! SSL شما با موفقیت نصب شد!**
