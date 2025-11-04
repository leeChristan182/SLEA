<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricSubsectionsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('rubric_subsections')->truncate();
        DB::statement('PRAGMA foreign_keys = ON;');

        $sections = [];

        // ---------------- Leadership ----------------
        $leadSections = DB::table('rubric_sections')
            ->where('category_id', 1)
            ->pluck('section_id', 'title');

        foreach ($leadSections as $title => $sectionId) {
            switch ($title) {
                case 'A. Campus-Based Student Government':
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Student Government (University / Campus)',
                        'evidence_needed' => " Oath of Office\n Certification from the Organization Adviser/ Immediate Supervisor",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 1
                    ];
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Campus Student Council',
                        'evidence_needed' => " Oath of Office\n Certification from the Organization Adviser/ Immediate Supervisor",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 2
                    ];
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Local Councils (College Level)',
                        'evidence_needed' => " Oath of Office\n Certification from the Organization Adviser/ Immediate Supervisor",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 3
                    ];
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Student Clubs and Organizations',
                        'evidence_needed' => " Oath of Office\n Certification from the Organization Adviser/ Immediate Supervisor",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 4
                    ];
                    break;

                case 'B. Designation in Special Orders / Office Orders':
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Designation in Special Orders / Office Order',
                        'evidence_needed' => " Certification from Chairperson/Committee\n Accomplishment Report",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 1
                    ];
                    break;

                case 'C. Community-Based':
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Local Government Unit (LGU)',
                        'evidence_needed' => " Oath of Office\n Certification from the Organization Adviser/Immediate Supervisor",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 1
                    ];
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Other Recognized Organizations (Non-LGU)',
                        'evidence_needed' => " Oath of Office\n Certification from the Organization Adviser/Immediate Supervisor",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 2
                    ];
                    break;

                case 'D. Leadership Training / Seminars / Conferences Attended (max 5 points)':
                    $sections[] = [
                        'section_id' => $sectionId,
                        'sub_section' => 'Leadership Trainings / Seminars / Conferences',
                        'evidence_needed' => " Certificate of Attendance / Appreciation / Participation",
                        'max_points' => null,
                        'notes' => null,
                        'order_no' => 1
                    ];
                    break;
            }
        }

        // ---------------- Academic ----------------
        $acadSections = DB::table('rubric_sections')
            ->where('category_id', 2)
            ->pluck('section_id');

        foreach ($acadSections as $sectionId) {
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'No Failing Grade / INC',
                'evidence_needed' => " Certificate of Grades (Portal Generated) from first year to 1st Semester",
                'max_points' => 20,
                'notes' => null,
                'order_no' => 1
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'With INC',
                'evidence_needed' => " Certificate of Grades (Portal Generated) showing INC",
                'max_points' => 17,
                'notes' => null,
                'order_no' => 2
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'With Failing Grade',
                'evidence_needed' => " Certificate of Grades (Portal Generated) showing failing grade",
                'max_points' => 15,
                'notes' => null,
                'order_no' => 3
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'With Failing Grade & INC',
                'evidence_needed' => " Certificate of Grades (Portal Generated) showing failing grade and INC",
                'max_points' => 10,
                'notes' => null,
                'order_no' => 4
            ];
        }

        // ---------------- Awards ----------------
        $awardSections = DB::table('rubric_sections')
            ->where('category_id', 3)
            ->pluck('section_id');

        foreach ($awardSections as $sectionId) {
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'International',
                'evidence_needed' => " Certificate of Recognition / Selection Criteria",
                'max_points' => 20,
                'notes' => null,
                'order_no' => 1
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'National',
                'evidence_needed' => " Certificate of Recognition / Selection Criteria",
                'max_points' => 15,
                'notes' => null,
                'order_no' => 2
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'Regional / Mindanao-wide',
                'evidence_needed' => " Certificate of Recognition / Selection Criteria",
                'max_points' => 10,
                'notes' => null,
                'order_no' => 3
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'Local (Institutional, City/College/Campus)',
                'evidence_needed' => " Certificate of Recognition / Selection Criteria",
                'max_points' => 5,
                'notes' => null,
                'order_no' => 4
            ];
        }

        // ---------------- Community Involvement ----------------
        $commSections = DB::table('rubric_sections')
            ->where('category_id', 4)
            ->pluck('section_id');

        foreach ($commSections as $sectionId) {
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'International Activity',
                'evidence_needed' => " Certificate / Program / Photos",
                'max_points' => 0.8,
                'notes' => null,
                'order_no' => 1
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'National Activity',
                'evidence_needed' => " Certificate / Program / Photos",
                'max_points' => 0.6,
                'notes' => null,
                'order_no' => 2
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'Regional Activity / Mindanao-wide',
                'evidence_needed' => " Certificate / Program / Photos",
                'max_points' => 0.4,
                'notes' => null,
                'order_no' => 3
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'Local Activity (Institutional, City/College/Campus)',
                'evidence_needed' => " Certificate / Program / Photos",
                'max_points' => 0.2,
                'notes' => null,
                'order_no' => 4
            ];
        }

        // ---------------- Conduct ----------------
        $conductSections = DB::table('rubric_sections')
            ->where('category_id', 5)
            ->pluck('section_id');

        foreach ($conductSections as $sectionId) {
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'Sport Reports Recorded',
                'evidence_needed' => " Taken charge by OSAS",
                'max_points' => -1.0,
                'notes' => null,
                'order_no' => 1
            ];
            $sections[] = [
                'section_id' => $sectionId,
                'sub_section' => 'Disciplinary Concerns Documented',
                'evidence_needed' => " Taken charge by OSAS",
                'max_points' => -2.0,
                'notes' => null,
                'order_no' => 2
            ];
        }

        DB::table('rubric_subsections')->insert($sections);
    }
}
