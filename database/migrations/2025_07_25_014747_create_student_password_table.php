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
        Schema::create('student_passwords', function (Blueprint $table) {
            $table->increments('password_id'); // Custom auto-increment primary key
            $table->string('email_address', 50); // You can make this a foreign key if needed
            $table->string('password_hashed', 50);
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_passwords');
    }
};
