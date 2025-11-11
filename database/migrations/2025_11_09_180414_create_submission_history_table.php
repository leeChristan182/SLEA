<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submission_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');

            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->index(['submission_id', 'new_status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('submission_history');
    }
};
