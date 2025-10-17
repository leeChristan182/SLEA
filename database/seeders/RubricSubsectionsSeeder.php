<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricSubsectionsSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks to avoid SQLite foreign key errors
        DB::statement('PRAGMA foreign_keys = OFF;');

        // Clear existing data (optional, only if you want to reset)
        DB::table('rubric_subsections')->truncate();

        // Insert subsections
        DB::table('rubric_subsections')->insert([
            // ======================================================
            // CATEGORY 1: LEADERSHIP EXCELLENCE (LEAD_A - LEAD_D)
            // ======================================================
            ['section_id' => 'LEAD_A', 'sub_section' => 'Student Government (University / Campus)', 'evidence_needed' => 'Oath of Office / Certification from Adviser', 'order_no' => 1],
            ['section_id' => 'LEAD_A', 'sub_section' => 'Campus Student Council', 'evidence_needed' => 'Oath of Office / Certification from Adviser', 'order_no' => 2],
            ['section_id' => 'LEAD_A', 'sub_section' => 'Local Councils (College Level)', 'evidence_needed' => 'Oath of Office / Certification from Adviser', 'order_no' => 3],
            ['section_id' => 'LEAD_A', 'sub_section' => 'Student Clubs and Organizations', 'evidence_needed' => 'Certification from Adviser', 'order_no' => 4],

            ['section_id' => 'LEAD_B', 'sub_section' => 'Designation in Special Orders / Office Order', 'evidence_needed' => 'Certification / Accomplishment Report', 'order_no' => 1],

            ['section_id' => 'LEAD_C', 'sub_section' => 'Local Government Unit (LGU)', 'evidence_needed' => 'Oath of Office / Certification from LGU', 'order_no' => 1],
            ['section_id' => 'LEAD_C', 'sub_section' => 'Other Recognized Organizations (Non-LGU)', 'evidence_needed' => 'Oath of Office / Certification from Adviser', 'order_no' => 2],

            ['section_id' => 'LEAD_D', 'sub_section' => 'Leadership Trainings / Seminars / Conferences', 'evidence_needed' => 'Certificate of Attendance / Participation', 'order_no' => 1],

            // ======================================================
            // CATEGORY 2: ACADEMIC EXCELLENCE (NONE_2)
            // ======================================================
            ['section_id' => 'NONE_2', 'sub_section' => 'Academic Excellence', 'evidence_needed' => 'Certificate of Grades (Portal Generated) – From first year to 1st Sem of this A.Y / Guidelines from award-giving body', 'order_no' => 1],

            // ======================================================
            // CATEGORY 3: AWARDS / RECOGNITION (NONE_3)
            // ======================================================
            ['section_id' => 'NONE_3', 'sub_section' => 'Awards / Recognition', 'evidence_needed' => 'Certificate of Recognition – Guidelines from award-giving body', 'order_no' => 1],

            // ======================================================
            // CATEGORY 4: COMMUNITY INVOLVEMENT & GOOD CONDUCT
            // ======================================================
            ['section_id' => 'NONE_4', 'sub_section' => 'Community Involvement', 'evidence_needed' => 'Certificate of Recognition/Appreciation, Activity Program/Invitation, Photo Documentation', 'order_no' => 1],
            ['section_id' => 'NONE_5', 'sub_section' => 'Good Conduct', 'evidence_needed' => 'Certification from Office of Student Affairs / Guidance Records', 'order_no' => 1],
        ]);

        // Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys = ON;');
    }
}
