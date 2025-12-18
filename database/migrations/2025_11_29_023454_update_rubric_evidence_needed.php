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
        // Update evidence_needed for Leadership Excellence subsections

        // A. Campus Based - All subsections under leadership.campus_government
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.campus_government.univ_student_gov',
                'leadership.campus_government.osc',
                'leadership.campus_government.local_councils',
                'leadership.campus_government.student_orgs'
            ])
            ->update([
                'evidence_needed' => "Oath of Office\nCertification from the Organization Adviser/Immediate Supervisor"
            ]);

        // B. Designation in Special Orders/Office Order
        DB::table('rubric_subsections')
            ->where('key', 'leadership.designations.office_order')
            ->update([
                'evidence_needed' => "Certification from Chairperson/Committee\nAccomplishment Report"
            ]);

        // C. Community Based - All subsections under leadership.community_based
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.community_based.lgu',
                'leadership.community_based.non_lgu'
            ])
            ->update([
                'evidence_needed' => "Oath of Office\nCertification from the Organization Adviser/Immediate Supervisor"
            ]);

        // D. Leadership Training/Seminars/Conferences Attended
        DB::table('rubric_subsections')
            ->whereIn('key', [
                'leadership.trainings.international',
                'leadership.trainings.national',
                'leadership.trainings.regional',
                'leadership.trainings.local'
            ])
            ->update([
                'evidence_needed' => "Certificate of Attendance/Appreciation/Participation"
            ]);

        // II. Academic Excellence
        DB::table('rubric_subsections')
            ->where('key', 'academic.gwa.level')
            ->update([
                'evidence_needed' => "Certificate of Grades (Portal Generated) From first year to 1st Sem of this A.Y"
            ]);

        // III. Awards/Recognition Received
        // Get all subsections under awards category
        $awardsSectionIds = DB::table('rubric_sections')
            ->join('rubric_categories', 'rubric_sections.category_id', '=', 'rubric_categories.id')
            ->where('rubric_categories.key', 'awards')
            ->pluck('rubric_sections.section_id');

        if ($awardsSectionIds->isNotEmpty()) {
            DB::table('rubric_subsections')
                ->whereIn('section_id', $awardsSectionIds)
                ->update([
                    'evidence_needed' => "Certificate of Recognition\nSelection Criteria/Guidelines from the award giving body"
                ]);
        }

        // IV. Community Involvement
        $communitySectionIds = DB::table('rubric_sections')
            ->join('rubric_categories', 'rubric_sections.category_id', '=', 'rubric_categories.id')
            ->where('rubric_categories.key', 'community')
            ->pluck('rubric_sections.section_id');

        if ($communitySectionIds->isNotEmpty()) {
            DB::table('rubric_subsections')
                ->whereIn('section_id', $communitySectionIds)
                ->update([
                    'evidence_needed' => "Certificate of Recognition/Appreciation\nActivity Program/Invitation\nPhoto Documentation"
                ]);
        }

        // V. Good Conduct
        $conductSectionIds = DB::table('rubric_sections')
            ->join('rubric_categories', 'rubric_sections.category_id', '=', 'rubric_categories.id')
            ->where('rubric_categories.key', 'conduct')
            ->pluck('rubric_sections.section_id');

        if ($conductSectionIds->isNotEmpty()) {
            DB::table('rubric_subsections')
                ->whereIn('section_id', $conductSectionIds)
                ->update([
                    'evidence_needed' => "OSAS"
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear evidence_needed for all subsections
        DB::table('rubric_subsections')->update(['evidence_needed' => null]);
    }
};
