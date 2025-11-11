<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rubric_categories')->delete();

        $now = now();
        DB::table('rubric_categories')->insert([
            [
                'key' => 'leadership',
                'title' => 'Leadership Excellence',
                'description' => 'Leadership roles in campus/community orgs, designations, and leadership trainings.',
                'max_points' => 20,
                'min_required_points' => 0,
                'aggregation' => 'capped_sum',
                'aggregation_params' => null,
                'order_no' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'academic',
                'title' => 'Academic Excellence',
                'description' => 'Academic standing during the leadership period.',
                'max_points' => 20,
                'min_required_points' => 0,
                'aggregation' => 'capped_sum',
                'aggregation_params' => null,
                'order_no' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'awards',
                'title' => 'Awards/Recognition Received',
                'description' => 'Co-/extracurricular distinctions with a selection process.',
                'max_points' => 20,
                'min_required_points' => 0,
                'aggregation' => 'capped_sum',
                'aggregation_params' => null,
                'order_no' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'community',
                'title' => 'Community Involvement',
                'description' => 'Service/outreach/civic activities.',
                'max_points' => 20,
                'min_required_points' => 0,
                'aggregation' => 'capped_sum',
                'aggregation_params' => null,
                'order_no' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'conduct',
                'title' => 'Good Conduct',
                'description' => 'Behavioral record; deductions up to 10.',
                'max_points' => 10, // max deduction is 10
                'min_required_points' => 0,
                'aggregation' => 'capped_sum',
                'aggregation_params' => json_encode(['deduction' => true]),
                'order_no' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
