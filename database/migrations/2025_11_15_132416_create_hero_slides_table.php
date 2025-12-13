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
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->enum('link_type', ['product', 'category', 'campaign', 'custom'])->default('custom');
            $table->string('linkable_type')->nullable(); // Product, Category, Campaign
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->string('custom_url')->nullable(); // برای link_type = custom
            $table->string('button_text')->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order'], 'hero_slides_active_sort');
            $table->index(['linkable_type', 'linkable_id'], 'hero_slides_linkable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
