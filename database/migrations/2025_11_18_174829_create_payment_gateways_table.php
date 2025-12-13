<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام درگاه (مثل زرین‌پال، کارت به کارت)
            $table->string('type')->unique(); // نوع درگاه (zarinpal, card_to_card)
            $table->string('display_name'); // نام نمایشی برای کاربر
            $table->text('description')->nullable(); // توضیحات
            $table->json('config')->nullable(); // تنظیمات درگاه (مثل Merchant ID)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // ترتیب نمایش
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
