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

        $statuses = [
            'Active',
            'Inactive',
        ];

        foreach ($statuses as $key) {
            DB::table('student_leadership_statuses')->updateOrInsert(
                ['key' => $key],
                ['key' => $key]
            );
        }
    }
}
