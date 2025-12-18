<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    protected function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($rows);
    }

    public function up(): void
    {
        if (!Schema::hasTable('student_leaderships')) {
            // If missing, create the FULL correct table (copy your create migration exactly)
            Schema::create('student_leaderships', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();

                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('leadership_type_id')->nullable()->constrained('leadership_types')->nullOnDelete();
                $table->foreignId('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
                $table->string('term', 25)->nullable();

                $table->string('leadership_status', 20)->nullable();
                $table->string('issued_by', 191)->nullable();
                $table->json('attachments')->nullable();
                $table->timestamps();

                // Helpful indexes
                $table->index('user_id');
                $table->index('leadership_type_id');
                $table->index('cluster_id');
                $table->index('organization_id');
                $table->index('position_id');
            });

            // add FK for leadership_status AFTER create
            Schema::table('student_leaderships', function (Blueprint $table) {
                $table->foreign('leadership_status')
                    ->references('key')
                    ->on('student_leadership_statuses');
            });

            return;
        }

        /**
         * 1) Make sure the columns exist / rename old columns
         */
        Schema::table('student_leaderships', function (Blueprint $table) {

            if (!Schema::hasColumn('student_leaderships', 'cluster_id')) {
                $table->foreignId('cluster_id')->nullable()->after('leadership_type_id')
                    ->constrained('clusters')->nullOnDelete();
            }

            if (!Schema::hasColumn('student_leaderships', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('cluster_id')
                    ->constrained('organizations')->nullOnDelete();
            }

            if (!Schema::hasColumn('student_leaderships', 'position_id')) {
                $table->foreignId('position_id')->nullable()->after('organization_id')
                    ->constrained('positions')->nullOnDelete();
            }

            if (Schema::hasColumn('student_leaderships', 'school_year') && !Schema::hasColumn('student_leaderships', 'term')) {
                $table->renameColumn('school_year', 'term');
            } elseif (!Schema::hasColumn('student_leaderships', 'term')) {
                $table->string('term', 25)->nullable()->after('position_id');
            }

            if (Schema::hasColumn('student_leaderships', 'status') && !Schema::hasColumn('student_leaderships', 'leadership_status')) {
                $table->renameColumn('status', 'leadership_status');
            } elseif (!Schema::hasColumn('student_leaderships', 'leadership_status')) {
                $table->string('leadership_status', 20)->nullable()->after('term');
            }

            if (!Schema::hasColumn('student_leaderships', 'issued_by')) {
                $table->string('issued_by', 191)->nullable();
            }

            if (!Schema::hasColumn('student_leaderships', 'attachments')) {
                $table->json('attachments')->nullable();
            }

            if (Schema::hasColumn('student_leaderships', 'start_date')) $table->dropColumn('start_date');
            if (Schema::hasColumn('student_leaderships', 'end_date')) $table->dropColumn('end_date');
        });

        /**
         * 2) CRITICAL FIX:
         *    Add standalone indexes FIRST so MySQL stops relying on the UNIQUE index
         *    as the “supporting index” for your foreign keys.
         */
        Schema::table('student_leaderships', function (Blueprint $table) {
            // Use deterministic names to avoid duplicates
            if (!$this->indexExists('student_leaderships', 'sl_user_id_idx')) {
                $table->index('user_id', 'sl_user_id_idx');
            }
            if (Schema::hasColumn('student_leaderships', 'organization_id') && !$this->indexExists('student_leaderships', 'sl_org_id_idx')) {
                $table->index('organization_id', 'sl_org_id_idx');
            }
            if (Schema::hasColumn('student_leaderships', 'position_id') && !$this->indexExists('student_leaderships', 'sl_position_id_idx')) {
                $table->index('position_id', 'sl_position_id_idx');
            }
            if (Schema::hasColumn('student_leaderships', 'leadership_type_id') && !$this->indexExists('student_leaderships', 'sl_leadership_type_id_idx')) {
                $table->index('leadership_type_id', 'sl_leadership_type_id_idx');
            }
            if (Schema::hasColumn('student_leaderships', 'cluster_id') && !$this->indexExists('student_leaderships', 'sl_cluster_id_idx')) {
                $table->index('cluster_id', 'sl_cluster_id_idx');
            }
        });

        /**
         * 3) Now it is safe to drop/recreate the UNIQUE index
         */
        if ($this->indexExists('student_leaderships', 'uniq_org_position_per_term')) {
            Schema::table('student_leaderships', function (Blueprint $table) {
                $table->dropUnique('uniq_org_position_per_term');
            });
        }

        // Create your NEW unique (adjust columns if your intended rule is different)
        // This version includes user_id to prevent duplicates per student.
        Schema::table('student_leaderships', function (Blueprint $table) {
            // Only create if not existing already
            // (MySQL unique name must match)
            $table->unique(
                ['user_id', 'organization_id', 'position_id', 'term'],
                'uniq_org_position_per_term'
            );
        });

        /**
         * 4) Add FK constraint for leadership_status if missing (try/catch)
         */
        try {
            Schema::table('student_leaderships', function (Blueprint $table) {
                $table->foreign('leadership_status')
                    ->references('key')
                    ->on('student_leadership_statuses');
            });
        } catch (\Throwable $e) {
            // ignore if already exists
        }
    }

    public function down(): void
    {
        // Optional: leave empty or reverse if you want
    }
};
