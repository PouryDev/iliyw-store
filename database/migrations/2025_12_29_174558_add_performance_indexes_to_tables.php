<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add performance indexes to improve query speed across all tables
     */
    public function up(): void
    {
        // ===================================================================
        // PRODUCTS TABLE INDEXES
        // ===================================================================
        Schema::table('products', function (Blueprint $table) {
            // Index for filtering active products
            $table->index('is_active', 'idx_products_is_active');
            
            // Composite index for category filtering with active status
            $table->index(['category_id', 'is_active'], 'idx_products_category_active');
            
            // Index for sorting by newest
            $table->index('created_at', 'idx_products_created_at');
            
            // Composite index for price range filtering
            $table->index(['price', 'is_active'], 'idx_products_price_active');
            
            // Index for stock availability checks
            $table->index(['stock', 'is_active'], 'idx_products_stock_active');
        });

        // ===================================================================
        // ORDERS TABLE INDEXES
        // ===================================================================
        Schema::table('orders', function (Blueprint $table) {
            // Composite index for user's orders list (sorted by date)
            $table->index(['user_id', 'created_at'], 'idx_orders_user_date');
            
            // Composite index for filtering by status and date
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
            
            // Composite index for user's orders filtered by status
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            
            // Index for order status lookups
            $table->index('status', 'idx_orders_status');
        });

        // ===================================================================
        // ORDER_ITEMS TABLE INDEXES
        // ===================================================================
        Schema::table('order_items', function (Blueprint $table) {
            // Index for calculating sold quantities per product
            $table->index(['product_id', 'created_at'], 'idx_order_items_product_date');
            
            // Index for variant-specific order items
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->index('product_variant_id', 'idx_order_items_variant');
            }
            
            // Index for campaign sales tracking
            if (Schema::hasColumn('order_items', 'campaign_id')) {
                $table->index(['campaign_id', 'created_at'], 'idx_order_items_campaign_date');
            }
        });

        // ===================================================================
        // INVOICES TABLE INDEXES
        // ===================================================================
        Schema::table('invoices', function (Blueprint $table) {
            // Composite index for status and date filtering
            $table->index(['status', 'created_at'], 'idx_invoices_status_date');
            
            // Index for payment status lookups
            $table->index('status', 'idx_invoices_status');
            
            // Index for paid invoices
            $table->index('paid_at', 'idx_invoices_paid_at');
            
            // Index for due date checks
            if (Schema::hasColumn('invoices', 'due_date')) {
                $table->index(['due_date', 'status'], 'idx_invoices_due_status');
            }
        });

        // ===================================================================
        // TRANSACTIONS TABLE INDEXES
        // ===================================================================
        Schema::table('transactions', function (Blueprint $table) {
            // Composite index for invoice transactions by status
            $table->index(['invoice_id', 'status'], 'idx_transactions_invoice_status');
            
            // Composite index for filtering by status and date
            $table->index(['status', 'created_at'], 'idx_transactions_status_date');
            
            // Index for verification tracking
            $table->index('verified_at', 'idx_transactions_verified_at');
            
            // Index for gateway-specific queries
            if (Schema::hasColumn('transactions', 'gateway_id')) {
                $table->index(['gateway_id', 'status'], 'idx_transactions_gateway_status');
            }
        });

        // ===================================================================
        // CATEGORIES TABLE INDEXES
        // ===================================================================
        Schema::table('categories', function (Blueprint $table) {
            // Index for filtering active categories
            $table->index('is_active', 'idx_categories_is_active');
            
            // Index for sorting
            $table->index('created_at', 'idx_categories_created_at');
        });

        // ===================================================================
        // PRODUCT_IMAGES TABLE INDEXES
        // ===================================================================
        Schema::table('product_images', function (Blueprint $table) {
            // Composite index for product images ordering
            $table->index(['product_id', 'sort_order'], 'idx_product_images_product_order');
        });

        // ===================================================================
        // CAMPAIGN_TARGETS TABLE INDEXES
        // ===================================================================
        if (Schema::hasTable('campaign_targets')) {
            Schema::table('campaign_targets', function (Blueprint $table) {
                // Composite index for campaign targets lookup
                $table->index(['campaign_id', 'targetable_id', 'targetable_type'], 'idx_campaign_targets_campaign');
            });
        }

        // ===================================================================
        // CAMPAIGN_SALES TABLE INDEXES
        // ===================================================================
        // campaign_sales already has index 'campaign_sales_campaign_date' from original migration
        // No need to add duplicate index

        // ===================================================================
        // DISCOUNT_CODE_USAGES TABLE INDEXES
        // ===================================================================
        if (Schema::hasTable('discount_code_usages')) {
            Schema::table('discount_code_usages', function (Blueprint $table) {
                // Composite index for user usage tracking
                $table->index(['discount_code_id', 'user_id'], 'idx_discount_usages_code_user');
                
                // Index for usage date analytics
                $table->index('created_at', 'idx_discount_usages_created_at');
            });
        }

        // ===================================================================
        // COLORS & SIZES TABLES INDEXES
        // ===================================================================
        Schema::table('colors', function (Blueprint $table) {
            $table->index('is_active', 'idx_colors_is_active');
        });

        Schema::table('sizes', function (Blueprint $table) {
            // Sizes table doesn't have sort_order column
            $table->index('is_active', 'idx_sizes_is_active');
        });

        // ===================================================================
        // DELIVERY_METHODS TABLE INDEXES
        // ===================================================================
        if (Schema::hasTable('delivery_methods')) {
            Schema::table('delivery_methods', function (Blueprint $table) {
                $table->index(['is_active', 'sort_order'], 'idx_delivery_methods_active_order');
            });
        }

        // ===================================================================
        // ADDRESSES TABLE INDEXES
        // ===================================================================
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                // Composite index for user's addresses
                $table->index(['user_id', 'is_default'], 'idx_addresses_user_default');
            });
        }

        // ===================================================================
        // HERO_SLIDES TABLE INDEXES
        // ===================================================================
        // hero_slides already has index 'hero_slides_active_sort' from original migration
        // No need to add duplicate index

        // ===================================================================
        // PAYMENT_GATEWAYS TABLE INDEXES
        // ===================================================================
        if (Schema::hasTable('payment_gateways')) {
            Schema::table('payment_gateways', function (Blueprint $table) {
                // Composite index for active gateways ordering
                $table->index(['is_active', 'sort_order'], 'idx_payment_gateways_active_order');
                
                // Index for gateway type lookups
                if (Schema::hasColumn('payment_gateways', 'type')) {
                    $table->index('type', 'idx_payment_gateways_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all indexes in reverse order
        
        if (Schema::hasTable('payment_gateways')) {
            Schema::table('payment_gateways', function (Blueprint $table) {
                $table->dropIndex('idx_payment_gateways_active_order');
                if (Schema::hasColumn('payment_gateways', 'type')) {
                    $table->dropIndex('idx_payment_gateways_type');
                }
            });
        }

        // hero_slides: No index to drop (already exists from original migration)

        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropIndex('idx_addresses_user_default');
            });
        }

        if (Schema::hasTable('delivery_methods')) {
            Schema::table('delivery_methods', function (Blueprint $table) {
                $table->dropIndex('idx_delivery_methods_active_order');
            });
        }

        Schema::table('sizes', function (Blueprint $table) {
            $table->dropIndex('idx_sizes_is_active');
        });

        Schema::table('colors', function (Blueprint $table) {
            $table->dropIndex('idx_colors_is_active');
        });

        if (Schema::hasTable('discount_code_usages')) {
            Schema::table('discount_code_usages', function (Blueprint $table) {
                $table->dropIndex('idx_discount_usages_code_user');
                $table->dropIndex('idx_discount_usages_created_at');
            });
        }

        // campaign_sales: No index to drop (already exists from original migration)

        if (Schema::hasTable('campaign_targets')) {
            Schema::table('campaign_targets', function (Blueprint $table) {
                $table->dropIndex('idx_campaign_targets_campaign');
            });
        }

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('idx_product_images_product_order');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_is_active');
            $table->dropIndex('idx_categories_created_at');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_invoice_status');
            $table->dropIndex('idx_transactions_status_date');
            $table->dropIndex('idx_transactions_verified_at');
            if (Schema::hasColumn('transactions', 'gateway_id')) {
                $table->dropIndex('idx_transactions_gateway_status');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_status_date');
            $table->dropIndex('idx_invoices_status');
            $table->dropIndex('idx_invoices_paid_at');
            if (Schema::hasColumn('invoices', 'due_date')) {
                $table->dropIndex('idx_invoices_due_status');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_product_date');
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->dropIndex('idx_order_items_variant');
            }
            if (Schema::hasColumn('order_items', 'campaign_id')) {
                $table->dropIndex('idx_order_items_campaign_date');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_date');
            $table->dropIndex('idx_orders_status_date');
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_is_active');
            $table->dropIndex('idx_products_category_active');
            $table->dropIndex('idx_products_created_at');
            $table->dropIndex('idx_products_price_active');
            $table->dropIndex('idx_products_stock_active');
        });
    }
};
