<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('method')->default('bank_transfer'); // bank_transfer
            $table->unsignedInteger('amount');
            $table->string('reference')->nullable(); // bank ref number or user note
            $table->string('status')->default('pending'); // pending, verified, rejected
            $table->string('receipt_path')->nullable(); // uploaded receipt proof
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};


