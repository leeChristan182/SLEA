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
            // Drop unique constraint on name
            $table->dropUnique(['name']);
            // Drop unique constraint on key (since same key can exist for different leadership types)
            $table->dropUnique(['key']);
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
