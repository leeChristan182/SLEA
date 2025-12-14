<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique('positions_name_unique');
            $table->dropUnique('positions_key_unique');
        });


        // Add composite unique constraint on (leadership_type_id, name)
        // Note: SQLite doesn't support adding unique constraints via alter, so we'll handle it in the seeder
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            // Restore unique constraints (if needed)
            $table->unique('name');
            $table->unique('key');
        });
    }
};
