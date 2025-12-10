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
        // Add 'unassigned' role to user_roles table if it doesn't exist
        if (Schema::hasTable('user_roles')) {
            $exists = DB::table('user_roles')->where('key', 'unassigned')->exists();
            if (!$exists) {
                DB::table('user_roles')->insert(['key' => 'unassigned']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'unassigned' role
        if (Schema::hasTable('user_roles')) {
            DB::table('user_roles')->where('key', 'unassigned')->delete();
        }
    }
};
