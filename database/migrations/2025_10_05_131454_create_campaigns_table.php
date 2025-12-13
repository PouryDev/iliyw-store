<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام کمپین
            $table->text('description')->nullable(); // توضیحات کمپین
            $table->enum('type', ['percentage', 'fixed'])->default('percentage'); // نوع تخفیف
            $table->unsignedInteger('discount_value'); // مقدار تخفیف (درصد یا مبلغ ثابت)
            $table->unsignedInteger('max_discount_amount')->nullable(); // حداکثر مبلغ تخفیف برای درصدی
            $table->timestamp('starts_at'); // تاریخ و ساعت شروع
            $table->timestamp('ends_at'); // تاریخ و ساعت پایان
            $table->boolean('is_active')->default(true); // وضعیت فعال/غیرفعال
            $table->unsignedInteger('priority')->default(0); // اولویت (بالاتر = مهم‌تر)
            $table->string('banner_image')->nullable(); // تصویر بنر کمپین
            $table->string('badge_text')->nullable(); // متن نشان فروش فوق‌العاده
            $table->timestamps();
            
            // ایندکس‌ها برای جستجوی سریع
            $table->index(['is_active', 'starts_at', 'ends_at'], 'campaigns_active_dates');
            $table->index(['priority', 'is_active'], 'campaigns_priority_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};