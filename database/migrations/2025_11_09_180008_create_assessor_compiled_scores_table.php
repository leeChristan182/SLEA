<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessor_compiled_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assessor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rubric_category_id')->constrained('rubric_categories')->onDelete('cascade');

            $table->decimal('total_score', 10, 2)->default(0);
            $table->decimal('max_points', 10, 2)->default(0);
            $table->decimal('min_required_points', 10, 2)->default(0);

            $table->string('category_result', 20)->nullable();
            $table->foreign('category_result')->references('key')->on('category_results');

            $table->unique(
                ['student_id', 'assessor_id', 'rubric_category_id'],
                'acs_student_assessor_category_unique'
            );
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('assessor_compiled_scores');
    }
};
