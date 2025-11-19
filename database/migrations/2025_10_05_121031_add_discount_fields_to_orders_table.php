<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_code')->nullable()->after('total_amount');
            $table->unsignedInteger('discount_amount')->default(0)->after('discount_code');
            $table->unsignedInteger('final_amount')->default(0)->after('discount_amount'); // total_amount - discount_amount
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_code', 'discount_amount', 'final_amount']);
        });
    }
};