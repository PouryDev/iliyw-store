# ⚡ راهنمای سریع Deploy - iliyw Store

برای کسانی که می‌خواهند سریع پروژه رو روی سرور بیارن بالا.

---

## 🚀 مراحل سریع (5 دقیقه‌ای)

### 1️⃣ آماده‌سازی سرور (یکبار)

```bash
# نصب Docker و Docker Compose
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Firewall
sudo ufw allow 22 && sudo ufw allow 80 && sudo ufw allow 443
sudo ufw enable

# Logout و Login دوباره
exit
```

### 2️⃣ Clone پروژه

```bash
cd ~
git clone YOUR_REPO_URL iliw-store
cd iliw-store
```

### 3️⃣ تنظیمات .env

```bash
cp .env.example .env.docker
nano .env.docker
```

**مهم‌ترین تنظیمات:**

```env
APP_URL=https://iliyw.store
DB_PASSWORD=CHANGE_THIS_PASSWORD
DB_ROOT_PASSWORD=CHANGE_THIS_TOO
```

### 4️⃣ Build و اجرا (بدون SSL)

```bash
docker-compose build
docker-compose up -d

# بررسی
docker-compose ps
docker-compose logs -f
```

### 5️⃣ دیتابیس

```bash
# Migrations
docker-compose exec php php artisan migrate --force

# کاربر ادمین
docker-compose exec php php artisan tinker
```

در tinker:

```php
\App\Models\User::create([
    'name' => 'ادمین',
    'phone' => '09123456789',
    'instagram_id' => '@admin',
    'password' => bcrypt('your-password'),
    'is_admin' => true
]);
// Ctrl+D برای خروج
```

### 6️⃣ نصب SSL

```bash
# توقف nginx
docker-compose stop nginx

# گرفتن SSL
docker-compose --profile ssl-setup up lego

# تعویض به SSL config
sed -i 's|default.conf|default-ssl.conf|g' docker-compose.yml

# Restart
docker-compose down
docker-compose up -d
```

### 7️⃣ تست

```bash
curl -I https://iliyw.store
```

---

## 📝 دستورات مفید

```bash
# مشاهده لاگ‌ها
docker-compose logs -f

# Restart سرویس
docker-compose restart nginx

# ورود به PHP container
docker-compose exec php bash

# Backup دیتابیس
docker-compose exec db mysqldump -u iliw -p iliw_store_db > backup.sql

# Clear cache
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
```

---

## 🆘 مشکلات رایج

### سایت نمایش نمی‌دهد

```bash
docker-compose restart
docker-compose logs nginx
docker-compose logs php
```

### خطای دیتابیس

```bash
docker-compose restart db
docker-compose logs db
```

### Permission error

```bash
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec php chmod -R 775 storage bootstrap/cache
```

---

## 📚 اطلاعات بیشتر

-   راهنمای کامل: [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)
-   راهنمای SSL: [SSL_SETUP_GUIDE.md](./SSL_SETUP_GUIDE.md)

---

✅ **Done! سایت شما در https://iliyw.store آماده است!**
