<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cor_submissions', function (Blueprint $table) {
            $table->increments('cor_id');                 // AI PK
            $table->string('student_id', 20)->index();    // string FK (no constraint for SQLite safety)
            $table->string('file_name', 191);
            $table->string('file_type', 10);
            $table->unsignedBigInteger('file_size');
            $table->dateTime('upload_date');
            $table->string('academic_year', 10);          // e.g., 2025-2026
            $table->string('status', 15)->default('Pending');
            $table->string('storage_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cor_submissions');
    }
};
