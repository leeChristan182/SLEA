<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only add the column if it doesn't exist yet
        if (!Schema::hasColumn('users', 'otp_last_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                // You can choose another reference column here if you want ordering,
                // e.g. ->after('status') or ->after('contact')
                $table->timestamp('otp_last_verified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'otp_last_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('otp_last_verified_at');
            });
        }
    }
};
