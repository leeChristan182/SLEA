<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class RubricSectionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rubric_sections')->delete();

        $catId = DB::table('rubric_categories')->pluck('id', 'key');
        $now = now();

        $rows = [
            // ---------------- I. LEADERSHIP EXCELLENCE ----------------

            // A. Campus-Based Student Government
            [
                'key'          => 'leadership.campus_government',
                'category_key' => 'leadership',
                'title'        => 'A. Campus-Based Student Government',
                'evidence'     => 'Oath of Office; certification from the organization or council adviser / immediate supervisor for the term of service.',
                'aggregation'  => 'sum',
                'notes'        => 'Credits are given for every academic year of full-time service. For campus-based positions, the certification must be signed by the adviser designated during that term.',
                'order_no'     => 1,
            ],

            // B. Designation in Special Orders / Office Orders
            [
                'key'          => 'leadership.designations',
                'category_key' => 'leadership',
                'title'        => 'B. Designation in Special Orders / Office Orders',
                'evidence'     => 'Certification from the chairperson or committee and the corresponding accomplishment report.',
                'aggregation'  => 'sum',
                'notes'        => 'Points are based on officially issued special orders or office orders with supporting accomplishment reports.',
                'order_no'     => 2,
            ],

            // C. Community-Based
            [
                'key'          => 'leadership.community_based',
                'category_key' => 'leadership',
                'title'        => 'C. Community-Based',
                'evidence'     => 'Oath of Office; certification from the organization adviser or immediate supervisor.',
                'aggregation'  => 'sum',
                'notes'        => 'If the student holds two or more positions in a year, only the position with the higher point value is credited.',
                'order_no'     => 3,
            ],

            // D. Leadership Trainings / Seminars / Conferences
            [
                'key'          => 'leadership.trainings',
                'category_key' => 'leadership',
                'title'        => 'D. Leadership Trainings / Seminars / Conferences Attended',
                'evidence'     => 'Certificate of attendance, appreciation or participation issued for the leadership activity.',
                'aggregation'  => 'sum',
                'notes'        => 'Activities less than one day (e.g., half-day) are not counted as seminars or trainings. Only activities duly approved, endorsed or recognized by CHED, NYC, DILG, USeP, LGUs and other SEC-registered organizations are credited.',
                'order_no'     => 4,
            ],

            // ---------------- II. ACADEMIC EXCELLENCE ----------------

            [
                'key'          => 'academic.gwa',
                'category_key' => 'academic',
                'title'        => 'General Weighted Average (1st Semester)',
                'evidence'     => 'Portal-generated Certificate of Grades from first year up to the first semester of the current academic year.',
                'aggregation'  => 'max_only',
                'notes'        => 'GWA is computed for the whole duration of leadership; no failing grade and no INC merits the highest points.',
                'order_no'     => 1,
            ],

            // ---------------- III. AWARDS / RECOGNITION ----------------

            [
                'key'          => 'awards.recognition',
                'category_key' => 'awards',
                'title'        => 'Awards / Recognition Received',
                'evidence'     => 'Certificate of recognition and the selection criteria or guidelines from the award-giving body.',
                'aggregation'  => 'sum',
                'notes'        => 'Only the top three placements and awards that went through a screening or selection process are credited.',
                'order_no'     => 1,
            ],

            // ---------------- IV. COMMUNITY INVOLVEMENT ----------------

            [
                'key'          => 'community.service',
                'category_key' => 'community',
                'title'        => 'Community Involvement',
                'evidence'     => 'Certificate of recognition or appreciation, plus an activity program or invitation and photo documentation.',
                'aggregation'  => 'sum',
                'notes'        => 'Covers participation in community activities, civic involvement and public service at international, national, regional and local levels.',
                'order_no'     => 1,
            ],

            // ---------------- V. GOOD CONDUCT ----------------

            [
                'key'          => 'conduct.good_conduct',
                'category_key' => 'conduct',
                'title'        => 'Good Conduct',
                'evidence'     => 'Official behavioral record from OSAS, including spot reports and disciplinary concerns.',
                'aggregation'  => 'sum',
                'notes'        => 'This criterion is handled by OSAS. Spot reports incur a deduction of 1 point each, while documented disciplinary concerns incur a deduction of 2 points each.',
                'order_no'     => 1,
            ],
        ];

        foreach ($rows as $r) {
            DB::table('rubric_sections')->insert([
                'category_id'        => $catId[$r['category_key']],
                'key'                => $r['key'],
                'title'              => $r['title'],
                'evidence'           => $r['evidence'],
                'aggregation'        => $r['aggregation'],
                'aggregation_params' => null,
                'notes'              => $r['notes'] ?? null,
                'order_no'           => $r['order_no'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }
}
