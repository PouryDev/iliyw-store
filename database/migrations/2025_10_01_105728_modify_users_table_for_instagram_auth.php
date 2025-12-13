<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add instagram_id column (nullable at first)
        Schema::table('users', function (Blueprint $table) {
            $table->string('instagram_id')->nullable()->after('name');
        });

        // Step 2: Fill instagram_id and phone with default values for existing users
        DB::table(table: 'users')->whereNull('instagram_id')->update([
            'instagram_id' => DB::raw("CONCAT('@user_', id)"),
        ]);

        DB::table('users')->where('phone', '')->orWhereNull('phone')->update([
            'phone' => DB::raw("CONCAT('0912', LPAD(id, 7, '0'))"),
        ]);

        // Step 3: Make email nullable and drop unique constraint
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('email')->nullable()->change();
        });

        // Step 4: Now make instagram_id and phone unique and required
        Schema::table('users', function (Blueprint $table) {
            $table->string('instagram_id')->unique()->change();
            $table->string('phone')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverse the changes
            $table->dropColumn('instagram_id');
            $table->string('phone')->nullable()->change();
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
