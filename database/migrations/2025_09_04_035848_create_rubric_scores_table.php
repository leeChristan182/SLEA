<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_scores', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 20);
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('sub_items')->nullable();
            $table->unsignedBigInteger('leadership_id')->nullable();
            $table->decimal('score', 5, 2)->default(0.00);
            $table->decimal('max_score', 5, 2);
            $table->text('comments')->nullable();
            $table->string('scored_by', 50)->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->enum('score_type', ['category', 'section', 'subsection', 'leadership'])->default('category');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('student_id')->references('student_id')->on('student_personal_information')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('rubric_categories')->onDelete('cascade');
            $table->foreign('section_id')->references('section_id')->on('rubric_sections')->onDelete('cascade');
            $table->foreign('sub_items')->references('sub_items')->on('rubric_subsections')->onDelete('cascade');
            $table->foreign('leadership_id')->references('id')->on('rubric_subsection_leadership')->onDelete('cascade');

            // Indexes
            $table->index(['student_id', 'category_id']);
            $table->index(['student_id', 'score_type']);
            $table->index('scored_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_scores');
    }
};
