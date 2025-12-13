<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->morphs('targetable'); // targetable_id, targetable_type (Product or Category)
            $table->timestamps();
            
            // اطمینان از یکتایی ترکیب کمپین و هدف
            $table->unique(['campaign_id', 'targetable_id', 'targetable_type'], 'campaign_targets_unique');
            
            // ایندکس برای جستجوی سریع
            $table->index(['targetable_id', 'targetable_type'], 'campaign_targets_targetable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_targets');
    }
};