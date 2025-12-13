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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_method_id')->nullable()->after('id');
            $table->foreign('delivery_method_id')->references('id')->on('delivery_methods')->onDelete('set null');
            $table->integer('delivery_fee')->default(0)->after('total_amount');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('delivery_fee')->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_method_id']);
            $table->dropColumn(['delivery_method_id', 'delivery_fee']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('delivery_fee');
        });
    }
};
