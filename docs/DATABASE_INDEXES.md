# Database Indexes - Complete Reference

این سند لیست کامل تمام indexes موجود در database را نمایش می‌دهد.

---

## جداول اصلی

### 1. products

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `products_slug_unique` | `slug` | Unique | URL یکتا |
| `idx_products_is_active` | `is_active` | Index | فیلتر محصولات فعال |
| `idx_products_category_active` | `category_id`, `is_active` | Composite | فیلتر دسته‌بندی + فعال |
| `idx_products_created_at` | `created_at` | Index | مرتب‌سازی جدیدترین |
| `idx_products_price_active` | `price`, `is_active` | Composite | فیلتر محدوده قیمت |
| `idx_products_stock_active` | `stock`, `is_active` | Composite | چک موجودی |
| `idx_products_fulltext_search` | `title`, `description` | Fulltext | جستجوی متنی سریع |

**Query Examples:**
```sql
-- Uses idx_products_category_active
SELECT * FROM products WHERE category_id = 5 AND is_active = 1;

-- Uses idx_products_price_active
SELECT * FROM products WHERE price BETWEEN 10000 AND 50000 AND is_active = 1;

-- Uses idx_products_fulltext_search
SELECT * FROM products WHERE MATCH(title, description) AGAINST('لپتاپ' IN BOOLEAN MODE);
```

---

### 2. orders

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `orders_user_id_foreign` | `user_id` | Foreign | رابطه با users |
| `idx_orders_status` | `status` | Index | فیلتر وضعیت |
| `idx_orders_user_date` | `user_id`, `created_at` | Composite | سفارشات کاربر |
| `idx_orders_status_date` | `status`, `created_at` | Composite | فیلتر وضعیت + تاریخ |
| `idx_orders_user_status` | `user_id`, `status` | Composite | سفارشات کاربر با وضعیت |

**Query Examples:**
```sql
-- Uses idx_orders_user_date
SELECT * FROM orders WHERE user_id = 123 ORDER BY created_at DESC;

-- Uses idx_orders_status_date
SELECT * FROM orders WHERE status = 'pending' ORDER BY created_at DESC;

-- Uses idx_orders_user_status
SELECT * FROM orders WHERE user_id = 123 AND status = 'delivered';
```

---

### 3. order_items

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `order_items_order_id_foreign` | `order_id` | Foreign | رابطه با orders |
| `order_items_product_id_foreign` | `product_id` | Foreign | رابطه با products |
| `idx_order_items_product_date` | `product_id`, `created_at` | Composite | آمار فروش محصول |
| `idx_order_items_variant` | `product_variant_id` | Index | فیلتر variant |
| `idx_order_items_campaign_date` | `campaign_id`, `created_at` | Composite | آمار کمپین |

**Query Examples:**
```sql
-- Uses idx_order_items_product_date (for best sellers)
SELECT product_id, SUM(quantity) as sold_qty 
FROM order_items 
WHERE created_at >= '2025-01-01'
GROUP BY product_id;

-- Uses idx_order_items_campaign_date
SELECT campaign_id, COUNT(*) as sales_count 
FROM order_items 
WHERE campaign_id IS NOT NULL 
GROUP BY campaign_id;
```

---

### 4. invoices

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `invoices_invoice_number_unique` | `invoice_number` | Unique | شماره فاکتور یکتا |
| `invoices_order_id_foreign` | `order_id` | Foreign | رابطه با orders |
| `idx_invoices_status` | `status` | Index | فیلتر وضعیت |
| `idx_invoices_status_date` | `status`, `created_at` | Composite | فیلتر وضعیت + تاریخ |
| `idx_invoices_paid_at` | `paid_at` | Index | فاکتورهای پرداخت شده |
| `idx_invoices_due_status` | `due_date`, `status` | Composite | سررسید فاکتورها |

**Query Examples:**
```sql
-- Uses idx_invoices_status_date
SELECT * FROM invoices WHERE status = 'paid' ORDER BY created_at DESC;

-- Uses idx_invoices_due_status
SELECT * FROM invoices WHERE due_date < NOW() AND status = 'unpaid';
```

---

### 5. transactions

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `transactions_invoice_id_foreign` | `invoice_id` | Foreign | رابطه با invoices |
| `idx_transactions_invoice_status` | `invoice_id`, `status` | Composite | تراکنش‌های فاکتور |
| `idx_transactions_status_date` | `status`, `created_at` | Composite | فیلتر وضعیت + تاریخ |
| `idx_transactions_verified_at` | `verified_at` | Index | تراکنش‌های تایید شده |
| `idx_transactions_gateway_status` | `gateway_id`, `status` | Composite | آمار درگاه |

**Query Examples:**
```sql
-- Uses idx_transactions_invoice_status
SELECT * FROM transactions WHERE invoice_id = 456 AND status = 'verified';

-- Uses idx_transactions_gateway_status
SELECT gateway_id, COUNT(*) as tx_count 
FROM transactions 
WHERE status = 'verified' 
GROUP BY gateway_id;
```

---

## جداول محصولات

### 6. product_variants

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `product_variants_sku_unique` | `sku` | Unique | کد محصول یکتا |
| `product_variants_unique` | `product_id`, `color_id`, `size_id` | Unique | یکتایی ترکیب |
| `product_variants_active` | `product_id`, `is_active` | Composite | variants فعال |
| `product_variants_color_size` | `color_id`, `size_id` | Composite | فیلتر رنگ و سایز |

**Query Examples:**
```sql
-- Uses product_variants_active
SELECT * FROM product_variants WHERE product_id = 10 AND is_active = 1;

-- Uses product_variants_color_size
SELECT * FROM product_variants WHERE color_id = 3 AND size_id = 5;
```

---

### 7. product_images

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `product_images_product_id_foreign` | `product_id` | Foreign | رابطه با products |
| `idx_product_images_product_order` | `product_id`, `sort_order` | Composite | ترتیب تصاویر |

**Query Examples:**
```sql
-- Uses idx_product_images_product_order
SELECT * FROM product_images WHERE product_id = 10 ORDER BY sort_order;
```

---

### 8. categories

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `categories_slug_unique` | `slug` | Unique | URL یکتا |
| `idx_categories_is_active` | `is_active` | Index | دسته‌های فعال |
| `idx_categories_created_at` | `created_at` | Index | مرتب‌سازی |

**Query Examples:**
```sql
-- Uses idx_categories_is_active
SELECT * FROM categories WHERE is_active = 1;
```

---

## جداول کمپین و تخفیف

### 9. campaigns

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `campaigns_active_dates` | `is_active`, `starts_at`, `ends_at` | Composite | کمپین‌های فعال |
| `campaigns_priority_active` | `priority`, `is_active` | Composite | اولویت کمپین |

**Query Examples:**
```sql
-- Uses campaigns_active_dates
SELECT * FROM campaigns 
WHERE is_active = 1 
AND starts_at <= NOW() 
AND ends_at >= NOW();
```

---

### 10. campaign_targets

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `campaign_targets_campaign_id_foreign` | `campaign_id` | Foreign | رابطه با campaigns |
| `campaign_targets_product_id_foreign` | `product_id` | Foreign | رابطه با products |
| `idx_campaign_targets_campaign_product` | `campaign_id`, `product_id` | Composite | محصولات کمپین |
| `idx_campaign_targets_product` | `product_id` | Index | کمپین‌های محصول |

**Query Examples:**
```sql
-- Uses idx_campaign_targets_campaign_product
SELECT * FROM campaign_targets WHERE campaign_id = 5;
```

---

### 11. campaign_sales

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_campaign_sales_campaign_date` | `campaign_id`, `created_at` | Composite | آمار فروش کمپین |

**Query Examples:**
```sql
-- Uses idx_campaign_sales_campaign_date
SELECT campaign_id, SUM(quantity) as total_sales 
FROM campaign_sales 
WHERE campaign_id = 5 
GROUP BY campaign_id;
```

---

### 12. discount_codes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `discount_codes_code_unique` | `code` | Unique | کد یکتا |
| `discount_codes_code_is_active_index` | `code`, `is_active` | Composite | validation کد |
| `discount_codes_expires_at_is_active_index` | `expires_at`, `is_active` | Composite | انقضا کد |

**Query Examples:**
```sql
-- Uses discount_codes_code_is_active_index
SELECT * FROM discount_codes WHERE code = 'SUMMER2025' AND is_active = 1;
```

---

### 13. discount_code_usages

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_discount_usages_code_user` | `discount_code_id`, `user_id` | Composite | استفاده کاربر از کد |
| `idx_discount_usages_created_at` | `created_at` | Index | آمار استفاده |

**Query Examples:**
```sql
-- Uses idx_discount_usages_code_user
SELECT COUNT(*) FROM discount_code_usages 
WHERE discount_code_id = 10 AND user_id = 123;
```

---

## جداول پشتیبانی

### 14. colors

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_colors_is_active` | `is_active` | Index | رنگ‌های فعال |

---

### 15. sizes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_sizes_active_order` | `is_active`, `sort_order` | Composite | سایزهای فعال |

---

### 16. delivery_methods

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_delivery_methods_active_order` | `is_active`, `sort_order` | Composite | روش‌های ارسال فعال |

---

### 17. addresses

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `addresses_user_id_foreign` | `user_id` | Foreign | رابطه با users |
| `idx_addresses_user_default` | `user_id`, `is_default` | Composite | آدرس پیش‌فرض |

---

### 18. hero_slides

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_hero_slides_active_order` | `is_active`, `sort_order` | Composite | اسلایدهای فعال |

---

### 19. payment_gateways

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `PRIMARY` | `id` | Primary | شناسه یکتا |
| `idx_payment_gateways_active_order` | `is_active`, `sort_order` | Composite | درگاه‌های فعال |
| `idx_payment_gateways_type` | `type` | Index | نوع درگاه |

---

## خلاصه آمار

| نوع Index | تعداد |
|-----------|-------|
| Primary Keys | 19 |
| Unique Indexes | 6 |
| Foreign Keys | ~15 |
| Single Column Indexes | 8 |
| Composite Indexes | 25+ |
| Fulltext Indexes | 1 |
| **جمع کل** | **~75** |

---

## نکات مهم

### 1. Index Maintenance

```sql
-- Rebuild indexes (MySQL)
ANALYZE TABLE products;
OPTIMIZE TABLE products;

-- Check index fragmentation
SHOW TABLE STATUS WHERE Name = 'products';
```

### 2. Index Size

```sql
-- Check index sizes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    ROUND(SUM(stat_value * @@innodb_page_size) / 1024 / 1024, 2) AS size_mb
FROM mysql.innodb_index_stats
WHERE database_name = 'your_database'
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY size_mb DESC;
```

### 3. Unused Indexes

```sql
-- Find unused indexes (requires Performance Schema)
SELECT * FROM sys.schema_unused_indexes;
```

---

## Best Practices

✅ **همیشه از indexes برای:**
- Foreign keys
- WHERE clauses
- ORDER BY columns
- JOIN conditions
- GROUP BY columns

❌ **از indexes استفاده نکنید برای:**
- جداول کوچک (< 1000 rows)
- ستون‌هایی که کمتر query می‌شوند
- ستون‌هایی با cardinality پایین

---

## مانیتورینگ

### Query Analysis

```sql
-- Show slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;

-- Explain query
EXPLAIN SELECT * FROM products WHERE is_active = 1;
```

### Index Usage

```sql
-- Check index cardinality
SHOW INDEX FROM products;

-- Index statistics
SELECT * FROM mysql.innodb_index_stats WHERE table_name = 'products';
```

---

برای اطلاعات بیشتر مراجعه کنید به:
- [QUERY_OPTIMIZATION.md](./QUERY_OPTIMIZATION.md)
- [MIGRATION_GUIDE.md](./MIGRATION_GUIDE.md)

