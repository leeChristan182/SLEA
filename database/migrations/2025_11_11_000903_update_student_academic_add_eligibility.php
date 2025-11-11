<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_academic', function (Blueprint $t) {
            $t->unsignedSmallInteger('expected_grad_year')->nullable()->after('year_level');
            $t->string('eligibility_status', 32)->default('eligible')->after('expected_grad_year');
            // eligible | needs_revalidation | under_review | ineligible
            $t->timestamp('revalidated_at')->nullable()->after('eligibility_status');
        });
    }
    public function down(): void
    {
        Schema::table('student_academic', function (Blueprint $t) {
            $t->dropColumn(['expected_grad_year', 'eligibility_status', 'revalidated_at']);
        });
    }
};
