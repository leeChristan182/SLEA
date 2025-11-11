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
        Schema::table('profile', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['student_id']);
            
            // Add the new foreign key to student_personal_information
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('student_personal_information')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['student_id']);
            
            // Add back the original foreign key to academic_information
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('academic_information')
                  ->onDelete('cascade');
        });
    }
};
