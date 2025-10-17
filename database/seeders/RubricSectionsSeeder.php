<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricSectionsSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign keys first
        DB::statement('PRAGMA foreign_keys = OFF;');

        // Clear table safely
        DB::table('rubric_sections')->truncate();

        // Re-enable foreign keys
        DB::statement('PRAGMA foreign_keys = ON;');

        // Get categories
        $cat1 = DB::table('rubric_categories')->where('title', 'Leadership Excellence')->first();
        $cat2 = DB::table('rubric_categories')->where('title', 'Academic Excellence')->first();
        $cat3 = DB::table('rubric_categories')->where('title', 'Awards/Recognition Received')->first();
        $cat4 = DB::table('rubric_categories')->where('title', 'Community Involvement')->first();
        $cat5 = DB::table('rubric_categories')->where('title', 'Good Conduct')->first();

        // Insert sections using upsert (safe for duplicates)
        if ($cat1) {
            DB::table('rubric_sections')->upsert([
                ['section_id' => 'LEAD_A', 'category_id' => $cat1->category_id, 'title' => 'Campus-Based', 'order_no' => 1],
                ['section_id' => 'LEAD_B', 'category_id' => $cat1->category_id, 'title' => 'Special Orders', 'order_no' => 2],
                ['section_id' => 'LEAD_C', 'category_id' => $cat1->category_id, 'title' => 'Community-Based', 'order_no' => 3],
                ['section_id' => 'LEAD_D', 'category_id' => $cat1->category_id, 'title' => 'Trainings / Seminars', 'order_no' => 4],
            ], ['category_id', 'order_no']);
        }

        if ($cat2) {
            DB::table('rubric_sections')->upsert([
                ['section_id' => 'NONE_2', 'category_id' => $cat2->category_id, 'title' => 'Academic Excellence', 'order_no' => 1],
            ], ['category_id', 'order_no']);
        }

        if ($cat3) {
            DB::table('rubric_sections')->upsert([
                ['section_id' => 'NONE_3', 'category_id' => $cat3->category_id, 'title' => 'Awards / Recognition', 'order_no' => 1],
            ], ['category_id', 'order_no']);
        }

        if ($cat4) {
            DB::table('rubric_sections')->upsert([
                ['section_id' => 'NONE_4', 'category_id' => $cat4->category_id, 'title' => 'Community Involvement', 'order_no' => 1],
            ], ['category_id', 'order_no']);
        }

        if ($cat5) {
            DB::table('rubric_sections')->upsert([
                ['section_id' => 'NONE_5', 'category_id' => $cat5->category_id, 'title' => 'Good Conduct', 'order_no' => 1],
            ], ['category_id', 'order_no']);
        }
    }
}
