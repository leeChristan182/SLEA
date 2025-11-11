<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('award_reports', function (Blueprint $table) {
            $table->id('award_id'); // PK
            $table->unsignedBigInteger('review_id'); // FK -> final_reviews
            $table->string('admin_id', 15);
            $table->string('action', 20);
            $table->timestamps();

            $table->foreign('review_id')->references('review_id')->on('final_reviews')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('award_reports');
    }
};
