<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rubric_categories')->truncate();

        DB::table('rubric_categories')->insert([
            [
                'key' => 'leadership',
                'title' => 'Leadership Excellence',
                'description' => 'Covers the applicant’s leadership experience, including elective/appointive roles in campus or community organizations and participation in co-curricular/extracurricular activities.',
                'max_points' => 20,
                'order_no' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'academic',
                'title' => 'Academic Excellence',
                'description' => 'Assesses the student’s academic standing throughout their leadership period, balancing academics and extracurriculars.',
                'max_points' => 20,
                'order_no' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'awards',
                'title' => 'Awards/Recognition Received',
                'description' => 'Recognizes co-curricular and extracurricular awards or distinctions received from credible bodies at different levels.',
                'max_points' => 20,
                'order_no' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'community',
                'title' => 'Community Involvement',
                'description' => 'Evaluates the student’s participation in community service, outreach, and civic activities at local, regional, national, or international levels.',
                'max_points' => 20,
                'order_no' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'conduct',
                'title' => 'Good Conduct',
                'description' => 'Assesses the student’s behavior and disciplinary record throughout their stay in the university.',
                'max_points' => 20,
                'order_no' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
