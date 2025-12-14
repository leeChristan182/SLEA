<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add enum table foreign keys AFTER all tables have been created.
 *
 * This migration should run AFTER all table creation migrations.
 * It adds foreign key constraints to enum reference columns that
 * may have been created before the enum tables existed.
 */
return new class extends Migration
{
    /**
     * Helper to check if a foreign key constraint already exists
     */
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $constraintName]);

        return !empty($result);
    }

    public function up(): void
    {
        // 1. users.role -> user_roles
        if (!$this->foreignKeyExists('users', 'users_role_foreign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('role', 'users_role_foreign')
                    ->references('key')
                    ->on('user_roles')
                    ->cascadeOnUpdate();
            });
        }

        // 2. users.status -> user_statuses
        if (!$this->foreignKeyExists('users', 'users_status_foreign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('status', 'users_status_foreign')
                    ->references('key')
                    ->on('user_statuses')
                    ->cascadeOnUpdate();
            });
        }

        // 3. rubric_categories.aggregation -> rubric_aggregations
        if (!$this->foreignKeyExists('rubric_categories', 'rubric_categories_aggregation_foreign')) {
            Schema::table('rubric_categories', function (Blueprint $table) {
                $table->foreign('aggregation', 'rubric_categories_aggregation_foreign')
                    ->references('key')
                    ->on('rubric_aggregations');
            });
        }

        // 4. rubric_sections.aggregation -> rubric_aggregations
        if (!$this->foreignKeyExists('rubric_sections', 'rubric_sections_aggregation_foreign')) {
            Schema::table('rubric_sections', function (Blueprint $table) {
                $table->foreign('aggregation', 'rubric_sections_aggregation_foreign')
                    ->references('key')
                    ->on('rubric_aggregations');
            });
        }

        // 5. rubric_subsections.scoring_method -> scoring_methods
        if (!$this->foreignKeyExists('rubric_subsections', 'rubric_subsections_scoring_method_foreign')) {
            Schema::table('rubric_subsections', function (Blueprint $table) {
                $table->foreign('scoring_method', 'rubric_subsections_scoring_method_foreign')
                    ->references('key')
                    ->on('scoring_methods');
            });
        }

        // 6. submissions.status -> submission_statuses
        if (!$this->foreignKeyExists('submissions', 'submissions_status_foreign')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->foreign('status', 'submissions_status_foreign')
                    ->references('key')
                    ->on('submission_statuses');
            });
        }

        // 7. organizations.domain -> organization_domains
        if (!$this->foreignKeyExists('organizations', 'organizations_domain_foreign')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->foreign('domain', 'organizations_domain_foreign')
                    ->references('key')
                    ->on('organization_domains');
            });
        }

        // 8. organizations.scope_level -> scope_levels
        if (!$this->foreignKeyExists('organizations', 'organizations_scope_level_foreign')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->foreign('scope_level', 'organizations_scope_level_foreign')
                    ->references('key')
                    ->on('scope_levels');
            });
        }

        // 9. student_leaderships.leadership_status -> student_leadership_statuses
        if (!$this->foreignKeyExists('student_leaderships', 'student_leaderships_leadership_status_foreign')) {
            Schema::table('student_leaderships', function (Blueprint $table) {
                $table->foreign('leadership_status', 'student_leaderships_leadership_status_foreign')
                    ->references('key')
                    ->on('student_leadership_statuses');
            });
        }

        // 10. submission_reviews.score_source -> review_score_sources
        if (!$this->foreignKeyExists('submission_reviews', 'submission_reviews_score_source_foreign')) {
            Schema::table('submission_reviews', function (Blueprint $table) {
                $table->foreign('score_source', 'submission_reviews_score_source_foreign')
                    ->references('key')
                    ->on('review_score_sources');
            });
        }

        // 11. submission_reviews.decision -> category_results
        if (!$this->foreignKeyExists('submission_reviews', 'submission_reviews_decision_foreign')) {
            Schema::table('submission_reviews', function (Blueprint $table) {
                $table->foreign('decision', 'submission_reviews_decision_foreign')
                    ->references('key')
                    ->on('category_results');
            });
        }

        // 12. assessor_compiled_scores.category_result -> category_results
        if (!$this->foreignKeyExists('assessor_compiled_scores', 'assessor_compiled_scores_category_result_foreign')) {
            Schema::table('assessor_compiled_scores', function (Blueprint $table) {
                $table->foreign('category_result', 'assessor_compiled_scores_category_result_foreign')
                    ->references('key')
                    ->on('category_results');
            });
        }

        // 13. assessor_final_reviews.qualification -> qualifications
        if (!$this->foreignKeyExists('assessor_final_reviews', 'assessor_final_reviews_qualification_foreign')) {
            Schema::table('assessor_final_reviews', function (Blueprint $table) {
                $table->foreign('qualification', 'assessor_final_reviews_qualification_foreign')
                    ->references('key')
                    ->on('qualifications');
            });
        }

        // 14. assessor_final_reviews.status -> final_review_statuses
        if (!$this->foreignKeyExists('assessor_final_reviews', 'assessor_final_reviews_status_foreign')) {
            Schema::table('assessor_final_reviews', function (Blueprint $table) {
                $table->foreign('status', 'assessor_final_reviews_status_foreign')
                    ->references('key')
                    ->on('final_review_statuses');
            });
        }

        // 15. final_reviews.decision -> final_review_decisions
        if (!$this->foreignKeyExists('final_reviews', 'final_reviews_decision_foreign')) {
            Schema::table('final_reviews', function (Blueprint $table) {
                $table->foreign('decision', 'final_reviews_decision_foreign')
                    ->references('key')
                    ->on('final_review_decisions');
            });
        }
    }

    public function down(): void
    {
        // Drop all enum foreign keys
        $foreignKeys = [
            ['table' => 'users', 'column' => 'role'],
            ['table' => 'users', 'column' => 'status'],
            ['table' => 'rubric_categories', 'column' => 'aggregation'],
            ['table' => 'rubric_sections', 'column' => 'aggregation'],
            ['table' => 'rubric_subsections', 'column' => 'scoring_method'],
            ['table' => 'submissions', 'column' => 'status'],
            ['table' => 'organizations', 'column' => 'domain'],
            ['table' => 'organizations', 'column' => 'scope_level'],
            ['table' => 'student_leaderships', 'column' => 'leadership_status'],
            ['table' => 'submission_reviews', 'column' => 'score_source'],
            ['table' => 'submission_reviews', 'column' => 'decision'],
            ['table' => 'assessor_compiled_scores', 'column' => 'category_result'],
            ['table' => 'assessor_final_reviews', 'column' => 'qualification'],
            ['table' => 'assessor_final_reviews', 'column' => 'status'],
            ['table' => 'final_reviews', 'column' => 'decision'],
        ];

        foreach ($foreignKeys as $fk) {
            if (Schema::hasTable($fk['table']) && Schema::hasColumn($fk['table'], $fk['column'])) {
                Schema::table($fk['table'], function (Blueprint $table) use ($fk) {
                    try {
                        $table->dropForeign([$fk['column']]);
                    } catch (\Exception $e) {
                        // Ignore if FK doesn't exist
                    }
                });
            }
        }
    }
};
