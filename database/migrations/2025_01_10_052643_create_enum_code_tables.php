<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // All code tables use string PK "key"
        Schema::create('submission_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('submission_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'pending',
            'under_review',
            'resubmit',
            'flagged',
            'qualified',
            'unqualified',
            'approved',
            'rejected'
        ]));

        Schema::create('rubric_aggregations', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('rubric_aggregations')->insert(array_map(fn($k) => ['key' => $k], [
            'sum',
            'capped_sum',
            'max_only',
            'top_n'
        ]));

        Schema::create('scoring_methods', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('scoring_methods')->insert(array_map(fn($k) => ['key' => $k], [
            'fixed',
            'option',
            'rate',
            'band'
        ]));

        Schema::create('final_review_decisions', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('final_review_decisions')->insert(array_map(fn($k) => ['key' => $k], [
            'approved',
            'rejected'
        ]));

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
            'other'
        ]));

        Schema::create('scope_levels', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('scope_levels')->insert(array_map(fn($k) => ['key' => $k], [
            'international',
            'national',
            'regional',
            'local',
            'institutional'
        ]));

        Schema::create('user_roles', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('user_roles')->insert(array_map(fn($k) => ['key' => $k], [
            'student',
            'admin',
            'assessor'
        ]));

        Schema::create('user_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('user_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'pending',
            'approved',
            'rejected',
            'disabled'
        ]));

        Schema::create('student_leadership_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('student_leadership_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'Active',
            'Inactive',
        ]));

        Schema::create('review_score_sources', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('review_score_sources')->insert(array_map(fn($k) => ['key' => $k], [
            'auto',
            'manual'
        ]));

        Schema::create('category_results', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('category_results')->insert(array_map(fn($k) => ['key' => $k], [
            'qualified',
            'unqualified'
        ]));

        Schema::create('final_review_statuses', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('final_review_statuses')->insert(array_map(fn($k) => ['key' => $k], [
            'finalized',
            'tracking'
        ]));

        Schema::create('qualifications', function (Blueprint $t) {
            $t->string('key', 32)->primary();
        });
        DB::table('qualifications')->insert(array_map(fn($k) => ['key' => $k], [
            'qualified',
            'unqualified'
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('final_review_statuses');
        Schema::dropIfExists('category_results');
        Schema::dropIfExists('review_score_sources');
        Schema::dropIfExists('student_leadership_statuses');
        Schema::dropIfExists('user_statuses');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('scope_levels');
        Schema::dropIfExists('organization_domains');
        Schema::dropIfExists('final_review_decisions');
        Schema::dropIfExists('scoring_methods');
        Schema::dropIfExists('rubric_aggregations');
        Schema::dropIfExists('submission_statuses');
    }
};
