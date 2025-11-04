<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RubricSubsectionLeadership;

class RubricSubsectionsLeadershipSeeder extends Seeder
{
    public function run(): void
    {
        RubricSubsectionLeadership::truncate();

        $leadershipPositions = [

            // ============================================================
            // A. Campus-Based Student Government (Section 1)
            // ============================================================
            ['section_id' => 1, 'sub_section_id' => 1, 'position' => 'Student Regent / President', 'points' => 5.00, 'position_order' => 1],
            ['section_id' => 1, 'sub_section_id' => 1, 'position' => 'Vice President for Internal and External Affairs', 'points' => 4.00, 'position_order' => 2],
            ['section_id' => 1, 'sub_section_id' => 1, 'position' => 'Vice President for Business Correspondence and Records', 'points' => 3.80, 'position_order' => 3],
            ['section_id' => 1, 'sub_section_id' => 1, 'position' => 'Vice President for Finance, Audit and Logistics', 'points' => 3.60, 'position_order' => 4],
            ['section_id' => 1, 'sub_section_id' => 1, 'position' => 'Vice President for Publication and Information', 'points' => 3.40, 'position_order' => 5],

            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC President', 'points' => 3.30, 'position_order' => 6],
            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC Vice President for Internal Affairs', 'points' => 3.20, 'position_order' => 7],
            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC Vice President for External Affairs', 'points' => 3.10, 'position_order' => 8],
            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC General Secretary', 'points' => 3.00, 'position_order' => 9],
            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC General Treasurer', 'points' => 2.90, 'position_order' => 10],
            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC General Auditor', 'points' => 2.80, 'position_order' => 11],
            ['section_id' => 1, 'sub_section_id' => 2, 'position' => 'OSC Public Information Officer', 'points' => 2.70, 'position_order' => 12],

            ['section_id' => 1, 'sub_section_id' => 3, 'position' => 'Governor', 'points' => 2.60, 'position_order' => 13],
            ['section_id' => 1, 'sub_section_id' => 3, 'position' => 'Vice Governor', 'points' => 2.50, 'position_order' => 14],
            ['section_id' => 1, 'sub_section_id' => 3, 'position' => 'Secretary (Local Council)', 'points' => 2.40, 'position_order' => 15],
            ['section_id' => 1, 'sub_section_id' => 3, 'position' => 'Treasurer (Local Council)', 'points' => 2.30, 'position_order' => 16],
            ['section_id' => 1, 'sub_section_id' => 3, 'position' => 'Auditor (Local Council)', 'points' => 2.20, 'position_order' => 17],
            ['section_id' => 1, 'sub_section_id' => 3, 'position' => 'Two College House Representatives (HoR)', 'points' => 2.10, 'position_order' => 18],

            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'President', 'points' => 2.00, 'position_order' => 19],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'Internal Vice President', 'points' => 1.90, 'position_order' => 20],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'External Vice President', 'points' => 1.80, 'position_order' => 21],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'Secretary (Student Club)', 'points' => 1.70, 'position_order' => 22],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'Treasurer (Student Club)', 'points' => 1.60, 'position_order' => 23],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'Auditor (Student Club)', 'points' => 1.50, 'position_order' => 24],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'Public Information Officer', 'points' => 1.40, 'position_order' => 25],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => 'Business Manager', 'points' => 1.30, 'position_order' => 26],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => '4th Year Representative', 'points' => 0.50, 'position_order' => 27],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => '3rd Year Representative', 'points' => 0.50, 'position_order' => 28],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => '2nd Year Representative', 'points' => 0.50, 'position_order' => 29],
            ['section_id' => 1, 'sub_section_id' => 4, 'position' => '1st Year Representative', 'points' => 0.50, 'position_order' => 30],

            // ============================================================
            // B. Designation in Special Orders / Office Orders (Section 2)
            // ============================================================
            ['section_id' => 2, 'sub_section_id' => 5, 'position' => 'Designation per Special Order', 'points' => 0.50, 'position_order' => 31],

            // ============================================================
            // C. Community-Based (Section 3)
            // ============================================================
            ['section_id' => 3, 'sub_section_id' => 6, 'position' => 'Municipal Councilor / SK Federated President', 'points' => 5.00, 'position_order' => 32],
            ['section_id' => 3, 'sub_section_id' => 6, 'position' => 'Barangay Councilor / SK Chairperson', 'points' => 4.00, 'position_order' => 33],
            ['section_id' => 3, 'sub_section_id' => 6, 'position' => 'Barangay Secretary / Treasurer', 'points' => 3.00, 'position_order' => 34],
            ['section_id' => 3, 'sub_section_id' => 6, 'position' => 'SK Councilor', 'points' => 2.00, 'position_order' => 35],

            ['section_id' => 3, 'sub_section_id' => 7, 'position' => 'President (Elective/Appointive Org)', 'points' => 2.00, 'position_order' => 36],
            ['section_id' => 3, 'sub_section_id' => 7, 'position' => 'Vice President (Elective/Appointive Org)', 'points' => 1.80, 'position_order' => 37],
            ['section_id' => 3, 'sub_section_id' => 7, 'position' => 'Indigenous People / Youth Leader', 'points' => 1.60, 'position_order' => 38],
            ['section_id' => 3, 'sub_section_id' => 7, 'position' => 'Secretary / Treasurer (Elective/Appointive Org)', 'points' => 1.40, 'position_order' => 39],

            // ============================================================
            // D. Leadership Trainings / Seminars / Conferences (Section 4)
            // ============================================================
            ['section_id' => 4, 'sub_section_id' => 8, 'position' => 'Participant', 'points' => 1.00, 'position_order' => 40],
        ];

        RubricSubsectionLeadership::insert($leadershipPositions);
    }
}
