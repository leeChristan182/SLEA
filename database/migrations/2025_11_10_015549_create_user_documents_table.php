<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('doc_type', 50);           // e.g., 'cor'
            $table->string('storage_path', 512);      // file path
            $table->json('meta')->nullable();         // AY, semester, notes, etc.

            $table->timestamps();

            $table->index(['user_id', 'doc_type']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};
