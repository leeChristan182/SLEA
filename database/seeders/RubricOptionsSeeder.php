<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RubricOptionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rubric_options')->delete();

        $subId = DB::table('rubric_subsections')->pluck('sub_section_id', 'key');
        $now = now();

        $rows = [];

        // ---------------- Leadership A: Campus-Based Student Government ----------------
        $leadA = [
            'leadership.campus_government.univ_student_gov' => [
                ['Student Regent / President',                         5.00],
                ['Vice President for Internal and External Affairs',   4.00],
                ['Vice President for Business Correspondence and Records', 3.80],
                ['Vice President for Finance, Audit and Logistics',    3.60],
                ['Vice President for Publication and Information',     3.40],
            ],
            'leadership.campus_government.osc' => [
                ['OSC President',                                      3.30],
                ['OSC Vice President for Internal Affairs',            3.20],
                ['OSC Vice President for External Affairs',            3.10],
                ['OSC General Secretary',                              3.00],
                ['OSC General Treasurer',                              2.90],
                ['OSC General Auditor',                                2.80],
                ['OSC Public Information Officer',                     2.70],
            ],
            'leadership.campus_government.local_councils' => [
                ['Governor',                                          2.60],
                ['Vice Governor',                                     2.50],
                ['Secretary (Local Council)',                         2.40],
                ['Treasurer (Local Council)',                         2.30],
                ['Auditor (Local Council)',                           2.20],
                ['Two College House Representatives (HoR)',           2.10],
            ],
            'leadership.campus_government.student_orgs' => [
                ['President',                                         2.00],
                ['Internal Vice President',                           1.90],
                ['External Vice President',                           1.80],
                ['Secretary (Student Club)',                          1.70],
                ['Treasurer (Student Club)',                          1.60],
                ['Auditor (Student Club)',                            1.50],
                ['Public Information Officer',                        1.40],
                ['Business Manager',                                  1.30],
                ['4th Year Representative',                           0.50],
                ['3rd Year Representative',                           0.50],
                ['2nd Year Representative',                           0.50],
                ['1st Year Representative',                           0.50],
            ],
        ];

        foreach ($leadA as $subKey => $opts) {
            $sid = $subId[$subKey] ?? null;
            if (!$sid) continue;
            $order = 1;
            foreach ($opts as [$label, $points]) {
                $rows[] = [
                    'sub_section_id' => $sid,
                    'code'           => Str::slug($label), // can align with positions.key if you want
                    'label'          => $label,
                    'points'         => $points,
                    'order_no'       => $order++,
                    'created_at'     => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // ---------------- Leadership B: Designations ----------------
        if (!empty($subId['leadership.designations.office_order'])) {
            $rows[] = [
                'sub_section_id' => $subId['leadership.designations.office_order'],
                'code'           => 'designation-per-so',
                'label'          => 'Designation per Special Order',
                'points'         => 0.50,
                'order_no'       => 1,
                'created_at'     => $now,
                'updated_at' => $now,
            ];
        }

        // ---------------- Leadership C: Community-Based ----------------
        $leadC = [
            'leadership.community_based.lgu' => [
                ['Municipal Councilor / SK Federated President', 5.00],
                ['Barangay Councilor / SK Chairperson',          4.00],
                ['Barangay Secretary / Treasurer',               3.00],
                ['SK Councilor',                                  2.00],
            ],
            'leadership.community_based.non_lgu' => [
                ['President (Elective/Appointive Org)',          2.00],
                ['Vice President (Elective/Appointive Org)',     1.80],
                ['Indigenous People / Youth Leader',             1.60],
                ['Secretary / Treasurer (Elective/Appointive Org)', 1.40],
            ],
        ];
        foreach ($leadC as $subKey => $opts) {
            $sid = $subId[$subKey] ?? null;
            if (!$sid) continue;
            $order = 1;
            foreach ($opts as [$label, $points]) {
                $rows[] = [
                    'sub_section_id' => $sid,
                    'code'           => Str::slug($label),
                    'label'          => $label,
                    'points'         => $points,
                    'order_no'       => $order++,
                    'created_at'     => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // ---------------- Academic: GWA bands ----------------
        if (!empty($subId['academic.gwa.level'])) {
            $gwa = [
                ['No Failing Grade/INC', 20],
                ['With INC',             17],
                ['With Failing Grade',   15],
                ['With Failing Grade & INC', 10],
            ];
            $order = 1;
            foreach ($gwa as [$label, $points]) {
                $rows[] = [
                    'sub_section_id' => $subId['academic.gwa.level'],
                    'code'           => Str::slug($label),
                    'label'          => $label,
                    'points'         => $points,
                    'order_no'       => $order++,
                    'created_at'     => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // ---------------- Awards: levels ----------------
        if (!empty($subId['awards.recognition.level'])) {
            $awards = [
                ['International',                                  20],
                ['National',                                       15],
                ['Regional / Mindanao-wide',                       10],
                ['Local (Institutional, City/College/Campus)',      5],
            ];
            $order = 1;
            foreach ($awards as [$label, $points]) {
                $rows[] = [
                    'sub_section_id' => $subId['awards.recognition.level'],
                    'code'           => Str::slug($label),
                    'label'          => $label,
                    'points'         => $points,
                    'order_no'       => $order++,
                    'created_at'     => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // ---------------- Conduct: infractions (negative points) ----------------
        if (!empty($subId['conduct.good_conduct.offense'])) {
            $conduct = [
                ['Minor Infraction', -1.0],
                ['Major Infraction', -2.0],
            ];
            $order = 1;
            foreach ($conduct as [$label, $points]) {
                $rows[] = [
                    'sub_section_id' => $subId['conduct.good_conduct.offense'],
                    'code'           => Str::slug($label),
                    'label'          => $label,
                    'points'         => $points,
                    'order_no'       => $order++,
                    'created_at'     => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows) {
            DB::table('rubric_options')->insert($rows);
        }
    }
}
