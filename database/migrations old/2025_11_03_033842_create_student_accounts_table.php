<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('email_address')->unique();
            $table->string('contact');
            $table->string('password');
            $table->enum('status', ['pending', 'approved', 'rejected', 'disabled'])
                ->default('pending'); // <-- Add status here
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_accounts'); // Drop the table entirely on rollback
    }
};
