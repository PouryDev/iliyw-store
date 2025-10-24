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
        Schema::create('product_music_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('title'); // نام آهنگ
            $table->string('artist')->nullable(); // هنرمند
            $table->string('file_path'); // مسیر فایل صوتی
            $table->integer('duration')->nullable(); // مدت زمان به ثانیه
            $table->integer('sort_order')->default(0); // ترتیب نمایش
            $table->timestamps();
            
            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_music_tracks');
    }
};
