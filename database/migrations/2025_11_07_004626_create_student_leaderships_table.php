<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_leaderships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('leadership_type_id')
                ->nullable()
                ->constrained('leadership_types')
                ->nullOnDelete();

            // For CCO / club org groupings
            $table->foreignId('cluster_id')
                ->nullable()
                ->constrained('clusters')
                ->nullOnDelete();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();

            // e.g. "AY 2024–2025", "1st Sem 2023–2024"
            $table->string('term', 100)->nullable();

            // Matches your enum codes: "Active", "Inactive"
            $table->string('leadership_status', 50)->default('Active');

            // e.g. "OSAS", "Department Chair", etc.
            $table->string('issued_by', 191)->nullable();

            // If you attach COR / appointment letter, etc.
            $table->json('attachments')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'organization_id', 'position_id'], 'student_leaderships_main_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_leaderships');
    }
};
