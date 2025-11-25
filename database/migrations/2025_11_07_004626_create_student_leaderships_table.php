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

            // For CCO club orgs
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

            // Term string instead of start/end dates (e.g., "2023-2024")
            $table->string('term', 25)->nullable();

            // Leadership status (Active / Inactive) referencing enum table
            $table->string('leadership_status', 20)->nullable();
            $table->foreign('leadership_status')
                ->references('key')
                ->on('student_leadership_statuses');

            $table->string('issued_by', 255)->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'organization_id', 'position_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_leaderships');
    }
};
