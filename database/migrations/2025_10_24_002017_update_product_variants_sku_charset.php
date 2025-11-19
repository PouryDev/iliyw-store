<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the sku column to use UTF-8 charset
        DB::statement('ALTER TABLE product_variants MODIFY COLUMN sku VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to default charset
        DB::statement('ALTER TABLE product_variants MODIFY COLUMN sku VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci');
    }
};