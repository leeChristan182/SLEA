<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Check if table exists
        if (!Schema::hasTable('student_leaderships')) {
            // If table doesn't exist, create it with the correct structure
            Schema::create('student_leaderships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('leadership_type_id')->nullable()->constrained('leadership_types')->nullOnDelete();
                $table->foreignId('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
                $table->string('term', 25)->nullable();
                $table->string('leadership_status', 20)->nullable();
                $table->string('issued_by', 255)->nullable();
                $table->json('attachments')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'organization_id', 'position_id']);
            });
            return;
        }

        // Table exists, add missing columns
        Schema::table('student_leaderships', function (Blueprint $table) {
            // Add cluster_id if it doesn't exist
            if (!Schema::hasColumn('student_leaderships', 'cluster_id')) {
                $table->foreignId('cluster_id')
                    ->nullable()
                    ->after('leadership_type_id')
                    ->constrained('clusters')
                    ->nullOnDelete();
            }

            // Add term column if it doesn't exist (rename school_year if it exists)
            if (Schema::hasColumn('student_leaderships', 'school_year') && !Schema::hasColumn('student_leaderships', 'term')) {
                // Rename school_year to term
                $table->renameColumn('school_year', 'term');
            } elseif (!Schema::hasColumn('student_leaderships', 'term')) {
                $table->string('term', 25)->nullable()->after('position_id');
            }

            // Add leadership_status if it doesn't exist (rename status if it exists)
            if (Schema::hasColumn('student_leaderships', 'status') && !Schema::hasColumn('student_leaderships', 'leadership_status')) {
                // Rename status to leadership_status
                $table->renameColumn('status', 'leadership_status');
            } elseif (!Schema::hasColumn('student_leaderships', 'leadership_status')) {
                $table->string('leadership_status', 20)->nullable()->after('term');
            }

            // Remove old date columns if they exist (start_date, end_date)
            if (Schema::hasColumn('student_leaderships', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('student_leaderships', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }

    public function down(): void
    {
        // Don't rollback - this is a fix migration
    }
};



