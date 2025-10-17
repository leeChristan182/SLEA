<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_review_requests', function (Blueprint $table) {
            $table->id('final_review_id'); // PK
            $table->unsignedBigInteger('submission_id'); // FK
            $table->string('action', 20)->nullable();
            $table->dateTime('request_date');
            $table->string('status', 20)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->foreign('submission_id')
                  ->references('submission_id')->on('submissions')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_review_requests');
    }
};
