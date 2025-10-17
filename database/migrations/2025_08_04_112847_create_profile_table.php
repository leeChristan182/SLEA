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
    Schema::create('profile', function (Blueprint $table) {
        $table->string('profile_id', 20)->primary();
        $table->string('student_id', 20);

        $table->string('profile_picture_path', 255)->nullable();
        $table->dateTime('date_upload_profile')->nullable();

        $table->foreign('student_id')
              ->references('student_id')
              ->on('academic_information')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('profile');
}

};
