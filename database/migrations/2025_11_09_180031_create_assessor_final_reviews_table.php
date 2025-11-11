<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessor_final_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assessor_id')->constrained('users')->onDelete('cascade');

            $table->decimal('total_score', 12, 2)->default(0);
            $table->decimal('max_possible', 12, 2)->default(0);

            $table->string('qualification', 20)->nullable();
            $table->foreign('qualification')->references('key')->on('qualifications');

            $table->text('remarks')->nullable();

            $table->string('status', 20)->default('finalized');
            $table->foreign('status')->references('key')->on('final_review_statuses');

            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['student_id', 'assessor_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('assessor_final_reviews');
    }
};
