<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /*
         |--------------------------------------------------------------------------
         | SUBMISSION + RUBRIC ENUMS
         |--------------------------------------------------------------------------
         */

        // Status of an individual submitted evidence/record
        Schema::create('submission_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('submission_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'pending',       // newly submitted, not yet opened
            'under_review',  // assessor is/was actively reviewing
            'accepted',      // accepted + scored, counts toward points
            'returned',      // sent back to student for correction/resubmission
            'rejected',      // invalid / will not be counted
            'flagged',       // suspicious or for OSAS/admin attention
        ]));

        // How we aggregate rubric option scores within a category
        Schema::create('rubric_aggregations', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('rubric_aggregations')->insert(array_map(fn($k) => ['key' => $k], [
            'sum',
            'capped_sum',
            'max_only',
            'top_n',
        ]));

        // How a rubric item is scored
        Schema::create('scoring_methods', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('scoring_methods')->insert(array_map(fn($k) => ['key' => $k], [
            'fixed',
            'option',
            'rate',
            'band',
        ]));

        // Source of a score (system vs overridden)
        Schema::create('review_score_sources', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('review_score_sources')->insert(array_map(fn($k) => ['key' => $k], [
            'auto',
            'manual',
        ]));

        /*
         |--------------------------------------------------------------------------
         | STUDENT / USER ENUMS
         |--------------------------------------------------------------------------
         */

        Schema::create('user_roles', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('user_roles')->insert(array_map(fn($k) => ['key' => $k], [
            'student',
            'admin',
            'assessor',
        ]));

        Schema::create('user_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('user_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'pending',
            'approved',
            'rejected',
            'disabled',
        ]));

        Schema::create('student_leadership_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('student_leadership_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'Active',
            'Inactive',
        ]));

        // Academic eligibility for SLEA
        Schema::create('eligibility_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('eligibility_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'eligible',           // can use SLEA
            'under_review',       // OSAS checking docs
            'needs_revalidation', // must re-submit requirements
            'ineligible',         // not allowed for SLEA
        ]));

        // Overall SLEA application status (student_academic.slea_application_status)
        Schema::create('slea_application_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('slea_application_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'incomplete',                      // default for eligible students
            'pending_assessor_evaluation',    // student clicked "ready to be rated"
            'pending_administrative_validation', // passed assessor threshold; waiting for admin
            'qualified',                       // approved for SLEA
            'not_qualified',                  // finished process but did not qualify
        ]));

        /*
         |--------------------------------------------------------------------------
         | ORGANIZATION / SCOPE ENUMS
         |--------------------------------------------------------------------------
         */

        Schema::create('organization_domains', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('organization_domains')->insert(array_map(fn($k) => ['key' => $k], [
            'campus',
            'college',
            'department',
            'community',
            'lgu',
            'ngo',
            'office',
            'other',
        ]));

        Schema::create('scope_levels', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('scope_levels')->insert(array_map(fn($k) => ['key' => $k], [
            'international',
            'national',
            'regional',
            'local',
            'institutional',
        ]));

        /*
         |--------------------------------------------------------------------------
         | CATEGORY / FINAL REVIEW ENUMS
         |--------------------------------------------------------------------------
         */

        // Per-category result (used by submission_reviews / compiled scores)
        Schema::create('category_results', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('category_results')->insert(array_map(fn($k) => ['key' => $k], [
            'qualified',      // meets threshold for that category
            'unqualified',    // below threshold
            'not_applicable', // evidence does not apply / category skipped
        ]));

        // Status of an assessor final-review record
        Schema::create('final_review_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('final_review_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'draft',            // assessor still checking / can edit
            'queued_for_admin', // assessor finished; waiting for admin
            'finalized',        // admin decision done
        ]));

        // Admin’s final decision on the SLEA application
        Schema::create('final_review_decisions', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('final_review_decisions')->insert(array_map(fn($k) => ['key' => $k], [
            'approved',       // admin approves for SLEA
            'not_qualified',  // admin decides student does not qualify
        ]));

        // Generic qualification enum (used anywhere you just need Q / UQ)
        Schema::create('qualifications', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });

        DB::table('qualifications')->insert(array_map(fn($k) => ['key' => $k], [
            'qualified',
            'unqualified',
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('final_review_decisions');
        Schema::dropIfExists('final_review_statuses');
        Schema::dropIfExists('category_results');
        Schema::dropIfExists('slea_application_statuses');
        Schema::dropIfExists('eligibility_statuses');
        Schema::dropIfExists('review_score_sources');
        Schema::dropIfExists('student_leadership_statuses');
        Schema::dropIfExists('user_statuses');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('scope_levels');
        Schema::dropIfExists('organization_domains');
        Schema::dropIfExists('scoring_methods');
        Schema::dropIfExists('rubric_aggregations');
        Schema::dropIfExists('submission_statuses');
    }
};
