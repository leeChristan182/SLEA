<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('final_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessor_final_review_id')->constrained('assessor_final_reviews')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');

            $table->string('decision', 20)->nullable();
            $table->foreign('decision')->references('key')->on('final_review_decisions');
            $table->text('remarks')->nullable();

            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['assessor_final_review_id', 'admin_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('final_reviews');
    }
};
