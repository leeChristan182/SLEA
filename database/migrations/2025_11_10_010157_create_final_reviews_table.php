<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('final_reviews', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();

            $table->foreignId('assessor_final_review_id')
                ->constrained('assessor_final_reviews')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('decision', 20)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['assessor_final_review_id', 'admin_id'], 'uniq_assessor_admin_final_review');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('final_reviews');
    }
};
