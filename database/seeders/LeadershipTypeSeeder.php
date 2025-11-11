<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeadershipType;

class LeadershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $types = [
            ['key' => 'elective',    'name' => 'Elective'],
            ['key' => 'appointive',  'name' => 'Appointive'],
            ['key' => 'designation', 'name' => 'Designation'],
            ['key' => 'training',    'name' => 'Training / Seminar'],
        ];

        foreach ($types as $t) {
            \App\Models\LeadershipType::updateOrCreate(['name' => $t['name']], $t);
        }
    }
}
