<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update notes for A. Campus Based - All subsections
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.campus_government.univ_student_gov',
                'leadership.campus_government.osc',
                'leadership.campus_government.local_councils',
                'leadership.campus_government.student_orgs'
            ])
            ->update([
                'notes' => 'Certification from the Organization/Council: The document must be signed by the adviser designated during their term as officer that year.'
            ]);

        // Update notes for C. Community Based - All subsections
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.community_based.lgu',
                'leadership.community_based.non_lgu'
            ])
            ->update([
                'notes' => 'In cases where two or more positions are being held in a year, the applicant/nominee shall choose whichever is higher.'
            ]);

        // Update notes for D. Leadership Training/Seminars/Conferences Attended - All subsections
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.trainings.international',
                'leadership.trainings.national',
                'leadership.trainings.regional',
                'leadership.trainings.local'
            ])
            ->update([
                'notes' => 'less than a day (half day) cannot be considered as seminar/training.'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert notes to null for all affected subsections
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.campus_government.univ_student_gov',
                'leadership.campus_government.osc',
                'leadership.campus_government.local_councils',
                'leadership.campus_government.student_orgs',
                'leadership.community_based.lgu',
                'leadership.community_based.non_lgu',
                'leadership.trainings.international',
                'leadership.trainings.national',
                'leadership.trainings.regional',
                'leadership.trainings.local'
            ])
            ->update([
                'notes' => null
            ]);
    }
};
