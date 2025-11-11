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
        Schema::create('log_in', function (Blueprint $table) {
            $table->id('log_id'); // Primary key
            $table->string('email_address'); // FK to users/student, depending on your setup
            $table->enum('user_role', ['admin', 'assessor', 'student'])->nullable();
            $table->dateTime('login_datetime')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_in');
    }
};
