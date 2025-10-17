<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricSubsectionsLeadershipSeeder extends Seeder
{
    public function run()
    {
        // Clear table before seeding (SQLite safe)
        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('rubric_subsection_leadership')->truncate();
        DB::statement('PRAGMA foreign_keys = ON;');

        DB::table('rubric_subsection_leadership')->insert([
            // ======================================================
            // SECTION A: CAMPUS-BASED
            // ======================================================
            ['position' => 'Student Regent / President', 'points' => 5.0, 'position_order' => 1],
            ['position' => 'Vice President for Internal and External Affairs', 'points' => 4.0, 'position_order' => 2],
            ['position' => 'Vice President for Business Correspondence and Records', 'points' => 3.8, 'position_order' => 3],
            ['position' => 'Vice President for Finance, Audit and Logistics', 'points' => 3.6, 'position_order' => 4],
            ['position' => 'Vice President for Publication and Information', 'points' => 3.4, 'position_order' => 5],
            ['position' => 'OSC President', 'points' => 3.3, 'position_order' => 6],
            ['position' => 'OSC Vice President for Internal Affairs', 'points' => 3.2, 'position_order' => 7],
            ['position' => 'OSC Vice President for External Affairs', 'points' => 3.1, 'position_order' => 8],
            ['position' => 'OSC General Secretary', 'points' => 3.0, 'position_order' => 9],
            ['position' => 'OSC General Treasurer', 'points' => 2.9, 'position_order' => 10],
            ['position' => 'OSC General Auditor', 'points' => 2.8, 'position_order' => 11],
            ['position' => 'OSC Public Information Officer', 'points' => 2.7, 'position_order' => 12],
            ['position' => 'Governor', 'points' => 2.6, 'position_order' => 13],
            ['position' => 'Vice Governor', 'points' => 2.5, 'position_order' => 14],
            ['position' => 'Secretary', 'points' => 2.4, 'position_order' => 15],
            ['position' => 'Treasurer', 'points' => 2.3, 'position_order' => 16],
            ['position' => 'Auditor', 'points' => 2.2, 'position_order' => 17],
            ['position' => 'College House Representative', 'points' => 2.1, 'position_order' => 18],
            ['position' => 'Club President', 'points' => 2.0, 'position_order' => 19],
            ['position' => 'Internal Vice President', 'points' => 1.9, 'position_order' => 20],
            ['position' => 'External Vice President', 'points' => 1.8, 'position_order' => 21],
            ['position' => 'Secretary', 'points' => 1.7, 'position_order' => 22],
            ['position' => 'Assistant Secretary', 'points' => 1.6, 'position_order' => 23],
            ['position' => 'Treasurer', 'points' => 1.5, 'position_order' => 24],
            ['position' => 'Auditor', 'points' => 1.4, 'position_order' => 25],
            ['position' => 'Public Information Officer', 'points' => 1.3, 'position_order' => 26],
            ['position' => 'Business Manager', 'points' => 1.2, 'position_order' => 27],
            ['position' => '1st Year Representative', 'points' => 1.0, 'position_order' => 28],
            ['position' => '2nd Year Representative', 'points' => 1.0, 'position_order' => 29],
            ['position' => '3rd Year Representative', 'points' => 1.0, 'position_order' => 30],
            ['position' => '4th Year Representative', 'points' => 1.0, 'position_order' => 31],

            // ======================================================
            // SECTION B: SPECIAL ORDERS
            // ======================================================
            ['position' => 'Designation in Special Orders (per SO)', 'points' => 0.5, 'position_order' => 32],

            // ======================================================
            // SECTION C: COMMUNITY-BASED
            // ======================================================
            ['position' => 'Municipal Councilor / SK Federated President', 'points' => 5.0, 'position_order' => 33],
            ['position' => 'Barangay Councilor / SK Chairperson', 'points' => 4.0, 'position_order' => 34],
            ['position' => 'Barangay Secretary / Treasurer', 'points' => 3.0, 'position_order' => 35],
            ['position' => 'SK Councilor', 'points' => 2.0, 'position_order' => 36],
            ['position' => 'Other Org President', 'points' => 2.0, 'position_order' => 37],
            ['position' => 'Other Org Vice President', 'points' => 1.8, 'position_order' => 38],
            ['position' => 'Indigenous Peoples Leader', 'points' => 1.6, 'position_order' => 39],
            ['position' => 'Youth Leader', 'points' => 1.4, 'position_order' => 40],
            ['position' => 'Secretary / Treasurer (Other Org)', 'points' => 1.2, 'position_order' => 41],

            // ======================================================
            // SECTION D: TRAININGS / SEMINARS / CONFERENCES
            // ======================================================
            ['position' => 'Leadership Training - International (per day)', 'points' => 0.5, 'position_order' => 42],
            ['position' => 'Leadership Training - National (per day)', 'points' => 0.6, 'position_order' => 43],
            ['position' => 'Leadership Training - Regional (per day)', 'points' => 0.4, 'position_order' => 44],
            ['position' => 'Leadership Training - Local (per day)', 'points' => 0.2, 'position_order' => 45],
        ]);
    }
}
