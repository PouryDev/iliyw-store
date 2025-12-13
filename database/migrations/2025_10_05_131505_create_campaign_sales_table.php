<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('original_price'); // قیمت اصلی محصول
            $table->unsignedInteger('discount_amount'); // مبلغ تخفیف
            $table->unsignedInteger('final_price'); // قیمت نهایی
            $table->unsignedInteger('quantity'); // تعداد فروخته شده
            $table->unsignedInteger('total_discount'); // مجموع تخفیف (discount_amount * quantity)
            $table->timestamps();
            
            // ایندکس‌ها برای آمارگیری
            $table->index(['campaign_id', 'created_at'], 'campaign_sales_campaign_date');
            $table->index(['product_id', 'created_at'], 'campaign_sales_product_date');
            $table->index('created_at', 'campaign_sales_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sales');
    }
};