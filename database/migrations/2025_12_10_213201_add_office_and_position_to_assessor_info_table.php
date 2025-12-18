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
        Schema::table('assessor_info', function (Blueprint $table) {
            $table->string('office_unit', 150)->nullable()->after('user_id');
            $table->string('position', 100)->nullable()->after('office_unit');
            $table->string('assessor_code', 50)->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessor_info', function (Blueprint $table) {
            $table->dropColumn(['office_unit', 'position', 'assessor_code']);
        });
    }
};
