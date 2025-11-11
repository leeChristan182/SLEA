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
        Schema::create('student_personal_information', function (Blueprint $table) {
            $table->string('email_address', 50)->primary();
            $table->string('student_id', 20)->unique()->nullable();

            $table->string('last_name', 50);
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->unsignedTinyInteger('age')->nullable(); // INTEGER(3)
            $table->string('contact_number', 15)->nullable(); // Store as string
            $table->dateTime('dateacc_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_personal_information');
    }
};
