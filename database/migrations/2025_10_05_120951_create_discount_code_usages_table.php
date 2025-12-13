<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('discount_amount'); // actual discount amount applied in Toman
            $table->timestamps();
            
            $table->unique(['discount_code_id', 'user_id']); // user can only use each code once
            $table->index(['discount_code_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_code_usages');
    }
};