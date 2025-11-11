<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submission_records', function (Blueprint $table) {
            $table->increments('subrec_id'); // PK
            $table->string('student_id', 20); // FK to academic_information.student_id

            // === Rubric Structure Integration ===
            $table->unsignedInteger('category_id')->nullable(); // FK to rubric_categories
            $table->unsignedInteger('section_id')->nullable();  // FK to rubric_sections
            $table->unsignedInteger('sub_items')->nullable();   // FK to rubric_subsections
            $table->unsignedBigInteger('leadership_id')->nullable(); // FK to rubric_subsection_leadership.id

            // === Submission Info ===
            $table->string('activity_title', 255);
            $table->string('activity_type', 255);
            $table->string('activity_role', 255);
            $table->date('activity_date');

            $table->string('organizing_body', 255);
            $table->string('term', 50)->nullable();
            $table->string('issued_by', 50)->nullable();
            $table->string('note', 100)->nullable();

            // === Document Info ===
            $table->string('document_type', 255)->nullable();
            $table->string('document_title', 255)->nullable();
            $table->string('document_title_path', 255)->nullable();

            $table->dateTime('datedocu_submitted')->nullable();

            // === Rubric Points ===
            $table->decimal('points_awarded', 5, 2)->nullable();
            $table->decimal('max_points', 5, 2)->nullable();

            $table->timestamps();

            // === Indexes and Foreign Keys ===
            $table->index('student_id');

            $table->foreign('student_id')
                ->references('student_id')
                ->on('student_personal_information')
                ->onDelete('cascade');

            // Rubric references (safe optional FKs)
            $table->foreign('category_id')
                ->references('category_id')
                ->on('rubric_categories')
                ->nullOnDelete();

            $table->foreign('section_id')
                ->references('section_id')
                ->on('rubric_sections')
                ->nullOnDelete();

            $table->foreign('sub_items')
                ->references('sub_items')
                ->on('rubric_subsections')
                ->nullOnDelete();

            $table->foreign('leadership_id')
                ->references('id')
                ->on('rubric_subsection_leadership')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_records');
    }
};
