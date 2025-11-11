<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('final_reviews', function (Blueprint $table) {
            $table->id('review_id'); // PK
            $table->unsignedBigInteger('final_review_id')->nullable(); // self reference FK
            $table->string('admin_id', 15);
            $table->string('remarks', 50);
            $table->dateTime('date_reviewed');
            $table->string('action', 20);
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('final_reviews');
    }
};
