<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add fulltext index for better search performance on products
     */
    public function up(): void
    {
        // MySQL/MariaDB FULLTEXT index for search optimization
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products ADD FULLTEXT idx_products_fulltext_search (title, description)');
        }
        
        // For PostgreSQL, we would use different approach (GIN index with tsvector)
        // For SQLite, fulltext search is done differently
        // This migration focuses on MySQL/MariaDB which is most common for Laravel projects
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_fulltext_search');
            });
        }
    }
};
