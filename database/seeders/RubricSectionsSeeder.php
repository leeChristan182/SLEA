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
            // Leadership grouping (no caps here; caps are at category or subsection level)
            [
                'key' => 'leadership.campus_government',
                'category_key' => 'leadership',
                'title' => 'A. Campus-Based Student Government',
                'evidence' => 'Oath of Office; Certification from the Organization Adviser / Immediate Supervisor',
                'aggregation' => 'sum',
                'order_no' => 1
            ],

            [
                'key' => 'leadership.designations',
                'category_key' => 'leadership',
                'title' => 'B. Designation in Special Orders / Office Orders',
                'evidence' => 'Certification from Chair/Committee; Accomplishment Report',
                'aggregation' => 'sum',
                'order_no' => 2
            ],

            [
                'key' => 'leadership.community_based',
                'category_key' => 'leadership',
                'title' => 'C. Community-Based',
                'evidence' => 'Oath of Office; Certification from Org Adviser / Immediate Supervisor',
                'aggregation' => 'sum',
                'order_no' => 3
            ],

            [
                'key' => 'leadership.trainings',
                'category_key' => 'leadership',
                'title' => 'D. Leadership Trainings / Seminars / Conferences Attended',
                'evidence' => 'Certificate of Attendance / Appreciation / Participation',
                'aggregation' => 'sum',
                'order_no' => 4
            ],

            // Academic
            [
                'key' => 'academic.gwa',
                'category_key' => 'academic',
                'title' => 'General Weighted Average (1st Semester)',
                'evidence' => 'Certificate of Grades (Portal Generated) from 1st year to 1st sem of current AY',
                'aggregation' => 'max_only',
                'order_no' => 1
            ],

            // Awards
            [
                'key' => 'awards.recognition',
                'category_key' => 'awards',
                'title' => 'Awards / Recognition Received',
                'evidence' => 'Certificate of Recognition; Selection Criteria/Guidelines',
                'aggregation' => 'sum',
                'order_no' => 1
            ],

            // Community
            [
                'key' => 'community.service',
                'category_key' => 'community',
                'title' => 'Community Involvement',
                'evidence' => 'Certificate; Program/Invitation; Photos',
                'aggregation' => 'sum',
                'order_no' => 1
            ],

            // Conduct
            [
                'key' => 'conduct.good_conduct',
                'category_key' => 'conduct',
                'title' => 'Good Conduct',
                'evidence' => 'OSAS official behavioral record',
                'aggregation' => 'sum',
                'order_no' => 1
            ],
        ];

        foreach ($rows as $r) {
            DB::table('rubric_sections')->insert([
                'category_id' => $catId[$r['category_key']],
                'key' => $r['key'],
                'title' => $r['title'],
                'evidence' => $r['evidence'],
                'aggregation' => $r['aggregation'],
                'aggregation_params' => null,
                'notes' => null,
                'order_no' => $r['order_no'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
