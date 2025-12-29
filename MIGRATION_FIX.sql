-- Fix incomplete migration by dropping any indexes that were created before the error
-- Run this before re-running the migration

-- Check and drop indexes that might have been created
-- (Only drop if they exist, MySQL will ignore if they don't)

-- Products indexes (these might have been created successfully)
ALTER TABLE products DROP INDEX IF EXISTS idx_products_is_active;
ALTER TABLE products DROP INDEX IF EXISTS idx_products_category_active;
ALTER TABLE products DROP INDEX IF EXISTS idx_products_created_at;
ALTER TABLE products DROP INDEX IF EXISTS idx_products_price_active;
ALTER TABLE products DROP INDEX IF EXISTS idx_products_stock_active;

-- Orders indexes
ALTER TABLE orders DROP INDEX IF EXISTS ididx_campaign_targets_campaignx_orders_status;
ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_user_date;
ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_status_date;
ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_user_status;

-- Order items indexes
ALTER TABLE order_items DROP INDEX IF EXISTS idx_order_items_product_date;
ALTER TABLE order_items DROP INDEX IF EXISTS idx_order_items_variant;
ALTER TABLE order_items DROP INDEX IF EXISTS idx_order_items_campaign_date;

-- Invoices indexes
ALTER TABLE invoices DROP INDEX IF EXISTS idx_invoices_status;
ALTER TABLE invoices DROP INDEX IF EXISTS idx_invoices_status_date;
ALTER TABLE invoices DROP INDEX IF EXISTS idx_invoices_paid_at;
ALTER TABLE invoices DROP INDEX IF EXISTS idx_invoices_due_status;
idx_campaign_targets_campaign
-- Transactions indexes
ALTER TABLE transactions DROP INDEX IF EXISTS idx_transactions_invoice_status;
ALTER TABLE transactions DROP INDEX IF EXISTS idx_transactions_status_date;
ALTER TABLE transactions DROP INDEX IF EXISTS idx_transactions_verified_at;
ALTER TABLE transactions DROP INDEX IF EXISTS idx_transactions_gateway_status;

-- Categories indexes
ALTER TABLE categories DROP INDEX IF EXISTS idx_categories_is_active;
ALTER TABLE categories DROP INDEX IF EXISTS idx_categories_created_at;

-- Product images indexes
ALTER TABLE product_images DROP INDEX IF EXISTS idx_product_images_product_order;

-- Campaign targets indexes (failed here)
ALTER TABLE campaign_targets DROP INDEX IF EXISTS idx_campaign_targets_campaign_product;
ALTER TABLE campaign_targets DROP INDEX IF EXISTS idx_campaign_targets_product;

-- After this, you can delete the failed migration record and re-run:
-- DELETE FROM migrations WHERE migration = '2025_12_29_174558_add_performance_indexes_to_tables';

