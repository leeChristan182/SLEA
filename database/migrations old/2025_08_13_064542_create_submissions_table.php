<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->increments('submission_id');              // INTEGER (AI) PK

            $table->unsignedInteger('pending_sub_id');        // FK1 -> pending_submissions.pending_sub_id
            $table->string('assessor_id', 15)->nullable();    // FK2 (string id, keep nullable / index only)
            $table->string('action', 20);                     // e.g., Approved / Rejected

            $table->timestamps();

            $table->unique('pending_sub_id');                 // enforce 1:1 with pending
            $table->index('assessor_id');

            $table->foreign('pending_sub_id')
                  ->references('pending_sub_id')
                  ->on('pending_submissions')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
