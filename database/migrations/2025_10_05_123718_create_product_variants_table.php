<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique(); // کد محصول منحصر به فرد
            $table->integer('stock')->default(0); // موجودی این variant
            $table->integer('price')->nullable(); // قیمت مخصوص این variant (اختیاری)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // اطمینان از یکتایی ترکیب محصول، رنگ و سایز
            $table->unique(['product_id', 'color_id', 'size_id'], 'product_variants_unique');
            
            // ایندکس‌ها برای جستجوی سریع
            $table->index(['product_id', 'is_active'], 'product_variants_active');
            $table->index(['color_id', 'size_id'], 'product_variants_color_size');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};