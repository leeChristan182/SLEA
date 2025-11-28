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
        Schema::table('award_reports', function (Blueprint $table) {
            $table->string('award_type', 191)->after('action');
            $table->timestamp('award_date')->nullable()->after('award_type');
            $table->text('remarks')->nullable()->after('award_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('award_reports', function (Blueprint $table) {
            $table->dropColumn(['award_type', 'award_date', 'remarks']);
        });
    }
};
