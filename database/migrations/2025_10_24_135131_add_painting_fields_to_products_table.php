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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_musical')->default(false)->after('is_active');
            $table->string('artist')->nullable()->after('is_musical');
            $table->string('technique')->nullable()->after('artist');
            $table->year('year')->nullable()->after('technique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_musical', 'artist', 'technique', 'year']);
        });
    }
};
