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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('gateway_id')->nullable()->after('invoice_id')->constrained('payment_gateways')->nullOnDelete();
            $table->string('gateway_transaction_id')->nullable()->after('gateway_id'); // شناسه تراکنش در درگاه
            $table->json('callback_data')->nullable()->after('gateway_transaction_id'); // داده‌های callback از درگاه
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['gateway_id']);
            $table->dropColumn(['gateway_id', 'gateway_transaction_id', 'callback_data']);
        });
    }
};
