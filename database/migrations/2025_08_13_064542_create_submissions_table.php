<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('leadership_id')->nullable()->constrained('student_leaderships')->nullOnDelete();

            $table->foreignId('rubric_category_id')->constrained('rubric_categories')->onDelete('cascade');

            $table->unsignedBigInteger('rubric_section_id')->nullable();
            $table->foreign('rubric_section_id')->references('section_id')->on('rubric_sections')->nullOnDelete();

            $table->unsignedBigInteger('rubric_subsection_id')->nullable();
            $table->foreign('rubric_subsection_id')->references('sub_section_id')->on('rubric_subsections')->nullOnDelete();

            $table->string('activity_title', 191);
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->json('meta')->nullable();

            $table->string('status', 20)->default('pending');
            $table->foreign('status')->references('key')->on('submission_statuses');

            $table->text('remarks')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index(
                ['rubric_category_id', 'rubric_section_id', 'rubric_subsection_id'],
                'submissions_rubric_combo_idx'
            );
            $table->index(['status', 'submitted_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
