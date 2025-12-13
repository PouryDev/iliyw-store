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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('original_amount', 10, 2)->nullable()->after('amount')->comment('Original amount before campaign discounts');
            $table->decimal('campaign_discount_amount', 10, 2)->default(0)->after('original_amount')->comment('Total campaign discount amount');
            $table->decimal('discount_code_amount', 10, 2)->default(0)->after('campaign_discount_amount')->comment('Discount code amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['original_amount', 'campaign_discount_amount', 'discount_code_amount']);
        });
    }
};