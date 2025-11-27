<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadershipStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('student_leadership_statuses')) {
            return;
        }

        // Define the leadership statuses
        $statuses = [
            ['key' => 'Active'],
            ['key' => 'Inactive'],
        ];

        // Clear existing data and insert fresh data
        DB::table('student_leadership_statuses')->truncate();

        foreach ($statuses as $status) {
            DB::table('student_leadership_statuses')->insert([
                'key' => $status['key'],
            ]);
        }
    }
}



