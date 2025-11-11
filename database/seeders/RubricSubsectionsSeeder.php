<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricSubsectionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('rubric_subsections')->delete();
        DB::statement('PRAGMA foreign_keys = ON;');

        $sectionId = DB::table('rubric_sections')->pluck('section_id', 'key');
        $now = now();

        $rows = [
            // Leadership A (options: each position = option)
            [
                'key' => 'leadership.campus_government.univ_student_gov',
                'section_key' => 'leadership.campus_government',
                'sub_section' => 'Student Government (University / Campus)',
                'scoring_method' => 'option',
                'unit' => 'role',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 1
            ],

            [
                'key' => 'leadership.campus_government.osc',
                'section_key' => 'leadership.campus_government',
                'sub_section' => 'Campus Student Council',
                'scoring_method' => 'option',
                'unit' => 'role',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 2
            ],

            [
                'key' => 'leadership.campus_government.local_councils',
                'section_key' => 'leadership.campus_government',
                'sub_section' => 'Local Councils (College Level)',
                'scoring_method' => 'option',
                'unit' => 'role',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 3
            ],

            [
                'key' => 'leadership.campus_government.student_orgs',
                'section_key' => 'leadership.campus_government',
                'sub_section' => 'Student Clubs and Organizations',
                'scoring_method' => 'option',
                'unit' => 'role',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 4
            ],

            // Leadership B (designation)
            [
                'key' => 'leadership.designations.office_order',
                'section_key' => 'leadership.designations',
                'sub_section' => 'Designation in Special Orders / Office Order',
                'scoring_method' => 'option',
                'unit' => 'designation',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 1
            ],

            // Leadership C (community-based)
            [
                'key' => 'leadership.community_based.lgu',
                'section_key' => 'leadership.community_based',
                'sub_section' => 'Local Government Unit (LGU)',
                'scoring_method' => 'option',
                'unit' => 'role',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 1
            ],

            [
                'key' => 'leadership.community_based.non_lgu',
                'section_key' => 'leadership.community_based',
                'sub_section' => 'Other Recognized Organizations (Non-LGU)',
                'scoring_method' => 'option',
                'unit' => 'role',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 2
            ],

            // Leadership D (rates with per-level caps)
            [
                'key' => 'leadership.trainings.international',
                'section_key' => 'leadership.trainings',
                'sub_section' => 'International (max 4 points)',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => 4,
                'score_params' => json_encode(['rate' => 0.5]),
                'order_no' => 1
            ],
            [
                'key' => 'leadership.trainings.national',
                'section_key' => 'leadership.trainings',
                'sub_section' => 'National (max 3 points)',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => 3,
                'score_params' => json_encode(['rate' => 0.6]),
                'order_no' => 2
            ],
            [
                'key' => 'leadership.trainings.regional',
                'section_key' => 'leadership.trainings',
                'sub_section' => 'Regional / Mindanao-wide (max 2 points)',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => 2,
                'score_params' => json_encode(['rate' => 0.4]),
                'order_no' => 3
            ],
            [
                'key' => 'leadership.trainings.local',
                'section_key' => 'leadership.trainings',
                'sub_section' => 'Local (Institutional, City/College/Campus) (max 1 point)',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => 1,
                'score_params' => json_encode(['rate' => 0.2]),
                'order_no' => 4
            ],

            // Academic (GWA bands = options)
            [
                'key' => 'academic.gwa.level',
                'section_key' => 'academic.gwa',
                'sub_section' => 'GWA Band',
                'scoring_method' => 'option',
                'unit' => 'gwa_level',
                'cap_points' => null,
                'score_params' => null,
                'order_no' => 1
            ],

            // Awards (levels = options)
            [
                'key' => 'awards.recognition.level',
                'section_key' => 'awards.recognition',
                'sub_section' => 'Award Level',
                'scoring_method' => 'option',
                'unit' => 'award_level',
                'cap_points' => 20,
                'score_params' => null,
                'order_no' => 1
            ],

            // Community (rates per level)
            [
                'key' => 'community.service.international',
                'section_key' => 'community.service',
                'sub_section' => 'International Activity',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => null,
                'score_params' => json_encode(['rate' => 0.8]),
                'order_no' => 1
            ],
            [
                'key' => 'community.service.national',
                'section_key' => 'community.service',
                'sub_section' => 'National Activity',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => null,
                'score_params' => json_encode(['rate' => 0.6]),
                'order_no' => 2
            ],
            [
                'key' => 'community.service.regional',
                'section_key' => 'community.service',
                'sub_section' => 'Regional / Mindanao-wide',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => null,
                'score_params' => json_encode(['rate' => 0.4]),
                'order_no' => 3
            ],
            [
                'key' => 'community.service.local',
                'section_key' => 'community.service',
                'sub_section' => 'Local Activity (Institutional, City/College/Campus)',
                'scoring_method' => 'rate',
                'unit' => 'day',
                'cap_points' => null,
                'score_params' => json_encode(['rate' => 0.2]),
                'order_no' => 4
            ],

            // Conduct (infractions = options; points are negative)
            [
                'key' => 'conduct.good_conduct.offense',
                'section_key' => 'conduct.good_conduct',
                'sub_section' => 'Infractions',
                'scoring_method' => 'option',
                'unit' => 'offense',
                'cap_points' => 10,
                'score_params' => json_encode(['deduction' => true]),
                'order_no' => 1
            ],
        ];

        foreach ($rows as $r) {
            DB::table('rubric_subsections')->insert([
                'section_id'      => $sectionId[$r['section_key']],
                'key'             => $r['key'],
                'sub_section'     => $r['sub_section'],
                'evidence_needed' => null,
                'max_points'      => null,
                'cap_points'      => $r['cap_points'],
                'scoring_method'  => $r['scoring_method'],
                'unit'            => $r['unit'],
                'score_params'    => $r['score_params'],
                'notes'           => null,
                'order_no'        => $r['order_no'],
                'created_at'      => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
