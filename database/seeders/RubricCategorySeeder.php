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
                'description' => 'Leadership experience through elected or appointed roles in campus and community organizations, including related competitions, conferences, seminars and workshops.',
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
                'description' => 'Overall academic standing for the duration of the student’s leadership, showing the ability to balance academics with co-curricular and extra-curricular activities.',
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
                'description' => 'Co-curricular and extra-curricular awards or recognitions received during the student’s stay in the university.',
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
                'description' => 'Participation in community activities, civic involvement and public service at local, regional, national and international levels.',
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
                'description' => 'Behavioral record while enrolled, based on official OSAS spot reports and disciplinary concerns, expressed as point deductions.',
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
