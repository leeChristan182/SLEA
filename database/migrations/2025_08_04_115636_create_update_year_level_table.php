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
    Schema::create('update_year_level', function (Blueprint $table) {
        $table->id('update_year_id');
        $table->string('student_id', 20);

        $table->string('old_year_level', 10);
        $table->string('new_year_level', 10);
        $table->dateTime('date_year_level_changed')->nullable();

        $table->foreign('student_id')
              ->references('student_id')
              ->on('academic_information')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('update_year_level');
}

};
