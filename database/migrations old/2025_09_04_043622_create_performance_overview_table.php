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
        Schema::create('performance_overview', function (Blueprint $table) {
            $table->id('perfo_overview_id');
            $table->unsignedInteger('pending_sub_id');
            $table->string('student_id', 20);
            $table->string('action', 20)->default('Pending');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('pending_sub_id')->references('pending_sub_id')->on('pending_submissions')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('academic_information')->onDelete('cascade');

            // Indexes
            $table->index(['pending_sub_id', 'student_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_overview');
    }
};