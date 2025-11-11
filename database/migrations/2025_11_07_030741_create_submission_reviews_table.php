<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submission_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('assessor_id')->constrained('users')->onDelete('cascade');

            $table->unsignedBigInteger('sub_section_id')->nullable();
            $table->foreign('sub_section_id')->references('sub_section_id')->on('rubric_subsections')->nullOnDelete();

            $table->foreignId('rubric_option_id')->nullable()->constrained('rubric_options')->nullOnDelete();

            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('score', 10, 2)->default(0);
            $table->decimal('computed_max', 10, 2)->nullable();

            $table->string('score_source', 10)->default('auto');
            $table->foreign('score_source')->references('key')->on('review_score_sources');
            $table->text('override_reason')->nullable();

            $table->string('decision', 20)->nullable();
            $table->foreign('decision')->references('key')->on('category_results'); // or its own table if you want reuse
            $table->text('comments')->nullable();

            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['submission_id', 'assessor_id']);
            $table->index(['decision', 'reviewed_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('submission_reviews');
    }
};
