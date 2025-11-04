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
        Schema::create('order_item_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->string('purpose', 32)->default('customer_upload'); // customer_upload | receipt
            $table->string('type', 16)->nullable(); // image | audio
            $table->string('disk', 32)->default('private');
            $table->string('path');
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime', 128)->nullable();
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index(['order_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_uploads');
    }
};


