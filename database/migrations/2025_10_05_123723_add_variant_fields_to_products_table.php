<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('stock'); // آیا محصول variant دارد؟
            $table->boolean('has_colors')->default(false)->after('has_variants'); // آیا محصول رنگ دارد؟
            $table->boolean('has_sizes')->default(false)->after('has_colors'); // آیا محصول سایز دارد؟
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_variants', 'has_colors', 'has_sizes']);
        });
    }
};