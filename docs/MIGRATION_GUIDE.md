# Migration Guide - Query Optimization

این راهنما نحوه اعمال بهینه‌سازی‌های query در production را توضیح می‌دهد.

## پیش‌نیازها

- ✅ Backup از database
- ✅ دسترسی به production server
- ✅ زمان maintenance window (اختیاری)

---

## مراحل Migration

### مرحله 1: Backup

```bash
# MySQL/MariaDB backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# یا با Laravel
php artisan backup:run
```

### مرحله 2: بررسی Migrations

```bash
# Check pending migrations
php artisan migrate:status

# Show SQL without running
php artisan migrate --pretend
```

### مرحله 3: اجرای Migrations

```bash
# Run new indexes migration
php artisan migrate

# Output:
# Migrating: 2025_12_29_174558_add_performance_indexes_to_tables
# Migrated:  2025_12_29_174558_add_performance_indexes_to_tables (XX.XXs)
# Migrating: 2025_12_29_174848_add_fulltext_search_to_products
# Migrated:  2025_12_29_174848_add_fulltext_search_to_products (XX.XXs)
```

### مرحله 4: بررسی Indexes

```sql
-- Check indexes on products table
SHOW INDEXES FROM products;

-- Check index usage
EXPLAIN SELECT * FROM products WHERE is_active = 1 AND category_id = 5;

-- Should show 'Using index' in Extra column
```

### مرحله 5: Monitor Performance

```bash
# Enable query log temporarily
php artisan tinker

>>> DB::enableQueryLog();
>>> \App\Models\Product::where('is_active', true)->get();
>>> DB::getQueryLog();
```

---

## زمان تقریبی Migration

حجم داده | زمان تقریبی
-----------|-------------
< 10K records | < 1 دقیقه
10K - 100K | 1-5 دقیقه
100K - 1M | 5-30 دقیقه
> 1M | 30+ دقیقه

**نکته:** ایجاد index در جداول بزرگ ممکن است زمان ببرد و جدول را lock کند.

---

## Rollback در صورت مشکل

```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback specific steps
php artisan migrate:rollback --step=2

# Restore from backup if needed
mysql -u username -p database_name < backup_file.sql
```

---

## بهینه‌سازی بعد از Migration

### 1. آنالیز جداول (MySQL/MariaDB)

```sql
ANALYZE TABLE products;
ANALYZE TABLE orders;
ANALYZE TABLE order_items;
-- ... for all tables
```

### 2. بهینه‌سازی جداول

```sql
OPTIMIZE TABLE products;
OPTIMIZE TABLE orders;
-- ... for all tables
```

### 3. آپدیت آمار

```sql
-- MySQL 8+
ANALYZE TABLE products UPDATE HISTOGRAM ON price, created_at;
```

---

## تست Performance

### قبل از Migration:

```bash
# Time a query
time php artisan tinker --execute="Product::where('is_active', true)->get();"
```

### بعد از Migration:

```bash
# Same query should be faster
time php artisan tinker --execute="Product::where('is_active', true)->get();"
```

### Query Profiling:

```sql
-- Enable profiling
SET profiling = 1;

-- Run query
SELECT * FROM products WHERE is_active = 1 AND category_id = 5;

-- Show profile
SHOW PROFILES;
SHOW PROFILE FOR QUERY 1;
```

---

## Troubleshooting

### مشکل 1: Migration خیلی طول می‌کشد

**راه حل:**
```bash
# Run in screen/tmux session
screen -S migration
php artisan migrate
# Ctrl+A, D to detach
```

### مشکل 2: Lock wait timeout

**راه حل:**
```sql
-- Increase timeout (MySQL)
SET GLOBAL innodb_lock_wait_timeout = 300;

-- Check running transactions
SHOW PROCESSLIST;
```

### مشکل 3: Disk space full

**راه حل:**
```bash
# Check disk space
df -h

# Clean up old logs
php artisan log:clear

# Clean old backups
```

### مشکل 4: Index creation failed

**راه حل:**
```sql
-- Drop problematic index
DROP INDEX index_name ON table_name;

-- Recreate manually
CREATE INDEX index_name ON table_name (column_name);
```

---

## Production Checklist

- [ ] Backup database
- [ ] Test migrations در staging environment
- [ ] بررسی disk space کافی
- [ ] اطلاع‌رسانی به تیم
- [ ] تعیین maintenance window (اختیاری)
- [ ] Monitor error logs
- [ ] آماده rollback plan
- [ ] تست performance بعد از migration
- [ ] Document any issues

---

## Post-Migration Tasks

### 1. Cache Clear

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Restart Services

```bash
# Restart queue workers
php artisan queue:restart

# Restart Horizon (if using)
php artisan horizon:terminate

# Restart web server (Nginx/Apache)
sudo systemctl restart nginx
```

### 3. Monitor Logs

```bash
# Watch Laravel logs
tail -f storage/logs/laravel.log

# Watch MySQL slow query log
tail -f /var/log/mysql/slow-query.log
```

### 4. Performance Testing

```bash
# Run load tests
ab -n 1000 -c 10 https://your-site.com/api/products

# Or use Laravel Dusk/Pest for automated tests
php artisan test --parallel
```

---

## معیارهای موفقیت

✅ Query time کاهش یافت
✅ CPU usage کاهش یافت
✅ Response time بهبود یافت
✅ هیچ error در logs نیست
✅ تمام features کار می‌کنند

---

## منابع مفید

- [Laravel Migrations](https://laravel.com/docs/migrations)
- [MySQL Index Documentation](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [Performance Tuning](https://laravel.com/docs/queries#optimizing-queries)

---

## پشتیبانی

در صورت بروز مشکل:

1. بررسی error logs
2. بررسی slow query log
3. استفاده از `EXPLAIN` برای آنالیز queries
4. Rollback در صورت لزوم
5. تماس با تیم توسعه

