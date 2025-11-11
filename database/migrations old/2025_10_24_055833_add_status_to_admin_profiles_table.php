<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            // ENUM column for status
            $table->enum('status', ['pending', 'approved', 'rejected', 'disabled'])
                ->default('pending')
                ->after('dateacc_created');
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
