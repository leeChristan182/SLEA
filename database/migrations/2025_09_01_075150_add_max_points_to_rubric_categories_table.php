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
        Schema::table('rubric_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('rubric_categories', 'max_points')) {
                $table->decimal('max_points', 5, 2)->default(100.00)->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubric_categories', function (Blueprint $table) {
            $table->dropColumn('max_points');
        });
    }
};
