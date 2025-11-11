<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pending_submissions', function (Blueprint $table) {
            $table->increments('pending_sub_id');          // INTEGER (AI) PK

            $table->unsignedInteger('subrec_id');          // FK1 -> submission_records.subrec_id
            $table->string('assessor_id', 15)->nullable(); // FK2 (string id, keep nullable)

            $table->string('action', 20)->default('Queued');
            $table->decimal('score_points', 4, 2)->nullable();
            $table->string('remarks', 255)->nullable();

            $table->dateTime('pending_queued_date')->nullable();
            $table->dateTime('assessed_date')->nullable();

            $table->timestamps();

            $table->index('subrec_id');
            $table->index('assessor_id');

            // Safe FK to submission_records (make sure that table exists first)
            $table->foreign('subrec_id')
                  ->references('subrec_id')
                  ->on('submission_records')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_submissions');
    }
};
