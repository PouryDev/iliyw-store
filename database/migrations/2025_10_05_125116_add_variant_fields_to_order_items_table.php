<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('color_id')->nullable()->after('product_variant_id')->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->after('color_id')->constrained()->nullOnDelete();
            $table->string('variant_display_name')->nullable()->after('size_id'); // برای نمایش راحت‌تر
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_variant_id', 'color_id', 'size_id', 'variant_display_name']);
        });
    }
};