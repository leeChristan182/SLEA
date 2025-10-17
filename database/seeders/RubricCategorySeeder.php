<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('rubric_categories')->updateOrInsert(
    ['order_no' => 1],
    ['title' => 'Leadership Excellence', 'max_points' => 20.00]
);

DB::table('rubric_categories')->updateOrInsert(
    ['order_no' => 2],
    ['title' => 'Academic Excellence', 'max_points' => 20.00]
);

DB::table('rubric_categories')->updateOrInsert(
    ['order_no' => 3],
    ['title' => 'Awards/Recognition Received', 'max_points' => 20.00]
);

DB::table('rubric_categories')->updateOrInsert(
    ['order_no' => 4],
    ['title' => 'Community Involvement & Good Conduct', 'max_points' => 20.00]
);


       
    }
}
