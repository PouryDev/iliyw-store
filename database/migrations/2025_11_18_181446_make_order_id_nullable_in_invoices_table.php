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
            // Drop the foreign key constraint first
            $table->dropForeign(['order_id']);
            // Make order_id nullable
            $table->foreignId('order_id')->nullable()->change();
            // Re-add the foreign key constraint with nullOnDelete
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['order_id']);
            // Make order_id not nullable again
            $table->foreignId('order_id')->nullable(false)->change();
            // Re-add the foreign key constraint with cascadeOnDelete
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }
};
