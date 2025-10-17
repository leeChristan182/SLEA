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
        Schema::table('rubric_subsections', function (Blueprint $table) {
            if (Schema::hasColumn('rubric_subsections', 'max_points')) {
                $table->dropColumn('max_points');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubric_subsections', function (Blueprint $table) {
            $table->decimal('max_points', 4, 2)->after('sub_section');
        });
    }
};
