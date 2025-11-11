<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('name', 255);
            $table->timestamps();

            $table->unique(['program_id', 'name']); // normalized
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
