<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submission_records', function (Blueprint $table) {
            $table->increments('subrec_id');              // PK 
            $table->string('student_id', 20);             // FK to academic_information.student_id
            $table->unsignedInteger('leadership_id')->nullable(); // optional link to leadership table

            $table->string('activity_title', 255);
            $table->string('activity_type', 255);
            $table->string('activity_role', 255);
            $table->dateTime('activity_date');

            $table->string('organizing_body', 255);
            $table->string('term', 50)->nullable();       // e.g., 1st Sem, 2nd Sem, Summer
            $table->string('issued_by', 50)->nullable();
            $table->string('note', 50)->nullable();       // optional short note

            $table->string('document_type', 255)->nullable();
            $table->string('slea_category', 255)->nullable();
            $table->string('subsection', 255)->nullable();

            $table->string('document_title', 255)->nullable();
            $table->string('document_title_path', 255)->nullable(); // stored file path (public disk)

            $table->dateTime('datedocu_submitted')->nullable(); // when the doc was submitted
            $table->timestamps();

            $table->index('student_id');

            // Safe FK to existing student_personal_information (string PK)
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('student_personal_information')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_records');
    }
};
