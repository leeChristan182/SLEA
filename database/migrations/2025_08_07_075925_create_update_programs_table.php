<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('update_programs', function (Blueprint $table) {
            $table->id('updateprog_id'); // Primary Key
            $table->string('student_id', 20); // FK to Academic or Student table

            $table->string('old_program', 50);
            $table->string('old_major', 50)->nullable();

            $table->string('new_program', 50);
            $table->string('new_major', 50)->nullable();

            $table->dateTime('date_prog_changed');

            $table->timestamps();

            // Foreign Key constraint (assumes academic_information has student_id)
            $table->foreign('student_id')->references('student_id')->on('academic_information')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_programs');
    }
};
