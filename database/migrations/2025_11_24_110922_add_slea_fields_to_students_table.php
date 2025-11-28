<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_academic', function (Blueprint $table) {
            // Whether the student has asked to be rated for SLEA
            $table->boolean('ready_for_rating')
                ->default(false)
                ->after('expected_grad_year');

            // When they marked themselves ready
            $table->timestamp('ready_for_rating_at')
                ->nullable()
                ->after('ready_for_rating');

            // Application flow statuses (NEW scheme)
            $table->string('slea_application_status')
                ->nullable()
                ->after('ready_for_rating_at')
                ->comment('SLEA lifecycle: incomplete, pending_assessor_evaluation, pending_administrative_validation, qualified, not_qualified');
        });
    }

    public function down(): void
    {
        Schema::table('student_academic', function (Blueprint $table) {
            $table->dropColumn([
                'ready_for_rating',
                'ready_for_rating_at',
                'slea_application_status',
            ]);
        });
    }
};
