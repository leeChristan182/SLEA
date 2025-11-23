<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\LeadershipType;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Get leadership type IDs
        $leadershipTypes = LeadershipType::pluck('id', 'key')->toArray();
        
        // Define positions by leadership type key
        $positionsByType = [
            // SCO - Student Clubs and Organizations (if added to LeadershipTypeSeeder)
            // Note: If SCO is needed, add it to LeadershipTypeSeeder first
            /*
            'sco' => [
                ['name' => 'President', 'rank' => 1, 'exec' => true],
                ['name' => 'Internal Vice President', 'rank' => 2, 'exec' => true],
                ['name' => 'External Vice President', 'rank' => 3, 'exec' => true],
                ['name' => 'Secretary', 'rank' => 4, 'exec' => false],
                ['name' => 'Assistant Secretary', 'rank' => 5, 'exec' => false],
                ['name' => 'Treasurer', 'rank' => 6, 'exec' => false],
                ['name' => 'Auditor', 'rank' => 7, 'exec' => false],
                ['name' => '1st Year Representative', 'rank' => 8, 'exec' => false],
                ['name' => '2nd Year Representative', 'rank' => 9, 'exec' => false],
                ['name' => '3rd Year Representative', 'rank' => 10, 'exec' => false],
                ['name' => '4th Year Representative', 'rank' => 11, 'exec' => false],
                ['name' => 'Committee Member', 'rank' => 12, 'exec' => false],
            ],
            */
            
            // CCO - Council of Clubs and Organizations
            'cco' => [
                ['name' => 'President', 'rank' => 1, 'exec' => true],
                ['name' => 'Vice President for Internal Affairs', 'rank' => 2, 'exec' => true],
                ['name' => 'Vice President for External Affairs', 'rank' => 3, 'exec' => true],
                ['name' => 'Vice President for Secretariat and Communications', 'rank' => 4, 'exec' => true],
                ['name' => 'Associate Vice President for Secretariat and Communications', 'rank' => 5, 'exec' => true],
                ['name' => 'Vice President for Audit', 'rank' => 6, 'exec' => true],
                ['name' => 'Vice President for Finance', 'rank' => 7, 'exec' => true],
                ['name' => 'Vice President for Business and Events Management', 'rank' => 8, 'exec' => true],
                ['name' => 'Vice President for Logistics and Property Superintendent', 'rank' => 9, 'exec' => true],
                ['name' => 'Campus Ministry Cluster Director', 'rank' => 10, 'exec' => false],
                ['name' => 'Legislative Board Chairperson', 'rank' => 11, 'exec' => false],
                ['name' => 'Academic Cluster Director', 'rank' => 12, 'exec' => false],
                ['name' => 'Socio-Civic Cluster Director', 'rank' => 13, 'exec' => false],
                ['name' => 'Culture and Arts Cluster Director', 'rank' => 14, 'exec' => false],
                ['name' => 'Sports Cluster Director', 'rank' => 15, 'exec' => false],
                ['name' => 'Inter-Fraternity and Sorority Cluster Director', 'rank' => 16, 'exec' => false],
                ['name' => 'Committee Member', 'rank' => 17, 'exec' => false],
            ],
            
            // SCO - Student Clubs and Organizations
            // Note: If SCO is needed, add it to LeadershipTypeSeeder first, then uncomment below
            /*
            'sco' => [
                ['name' => 'President', 'rank' => 1, 'exec' => true],
                ['name' => 'Internal Vice President', 'rank' => 2, 'exec' => true],
                ['name' => 'External Vice President', 'rank' => 3, 'exec' => true],
                ['name' => 'Secretary', 'rank' => 4, 'exec' => false],
                ['name' => 'Assistant Secretary', 'rank' => 5, 'exec' => false],
                ['name' => 'Treasurer', 'rank' => 6, 'exec' => false],
                ['name' => 'Auditor', 'rank' => 7, 'exec' => false],
                ['name' => '1st Year Representative', 'rank' => 8, 'exec' => false],
                ['name' => '2nd Year Representative', 'rank' => 9, 'exec' => false],
                ['name' => '3rd Year Representative', 'rank' => 10, 'exec' => false],
                ['name' => '4th Year Representative', 'rank' => 11, 'exec' => false],
                ['name' => 'Committee Member', 'rank' => 12, 'exec' => false],
            ],
            */
            
            // USG - University Student Government (Student Government)
            'usg' => [
                ['name' => 'Student Regent/President', 'rank' => 1, 'exec' => true],
                ['name' => 'Vice President for Internal and External Affairs', 'rank' => 2, 'exec' => true],
                ['name' => 'Vice President for Business Correspondence and Records', 'rank' => 3, 'exec' => true],
                ['name' => 'Vice President for Finance, Audit and Logistics', 'rank' => 4, 'exec' => true],
                ['name' => 'Vice President for Publication and Information', 'rank' => 5, 'exec' => true],
                ['name' => 'Committee Member', 'rank' => 6, 'exec' => false],
            ],
            
            // OSC - Obrero Student Council (Campus Student Council)
            'osc' => [
                ['name' => 'OSC President', 'rank' => 1, 'exec' => true],
                ['name' => 'OSC Vice President for Internal Affairs', 'rank' => 2, 'exec' => true],
                ['name' => 'OSC Vice President for External Affairs', 'rank' => 3, 'exec' => true],
                ['name' => 'OSC General Secretary', 'rank' => 4, 'exec' => false],
                ['name' => 'OSC General Treasurer', 'rank' => 5, 'exec' => false],
                ['name' => 'OSC General Auditor', 'rank' => 6, 'exec' => false],
                ['name' => 'OSC Public Information Officer', 'rank' => 7, 'exec' => false],
                ['name' => 'Committee Member', 'rank' => 8, 'exec' => false],
            ],
            
            // LC - Local Council
            'lc' => [
                ['name' => 'Governor', 'rank' => 1, 'exec' => true],
                ['name' => 'Vice Governor', 'rank' => 2, 'exec' => true],
                ['name' => 'Secretary', 'rank' => 3, 'exec' => false],
                ['name' => 'Treasurer', 'rank' => 4, 'exec' => false],
                ['name' => 'Auditor', 'rank' => 5, 'exec' => false],
                ['name' => 'College House Representative (1st)', 'rank' => 6, 'exec' => false],
                ['name' => 'College House Representative (2nd)', 'rank' => 7, 'exec' => false],
                ['name' => 'Committee Member', 'rank' => 8, 'exec' => false],
            ],
            
            // LCM - League of Class Mayors
            'lcm' => [
                ['name' => 'Mayor', 'rank' => 1, 'exec' => true],
                ['name' => 'Vice Mayor', 'rank' => 2, 'exec' => true],
                ['name' => 'Secretary', 'rank' => 3, 'exec' => false],
                ['name' => 'Treasurer', 'rank' => 4, 'exec' => false],
                ['name' => 'Auditor', 'rank' => 5, 'exec' => false],
            ],
            
            // LGU - Local Government Unit (keeping existing positions)
            'lgu' => [
                ['name' => 'Municipal Councilor / SK Federated President', 'rank' => 1, 'exec' => false],
                ['name' => 'Barangay Councilor / SK Chairperson', 'rank' => 2, 'exec' => false],
                ['name' => 'Barangay Secretary / Treasurer', 'rank' => 3, 'exec' => false],
                ['name' => 'SK Councilor', 'rank' => 4, 'exec' => false],
                ['name' => 'Indigenous People / Youth Leader', 'rank' => 5, 'exec' => false],
            ],
        ];

        // Clear existing positions
        DB::table('positions')->truncate();

        // Insert positions by leadership type
        foreach ($positionsByType as $typeKey => $positions) {
            $leadershipTypeId = $leadershipTypes[$typeKey] ?? null;
            
            if (!$leadershipTypeId) {
                $this->command?->warn("⚠️ Leadership type '{$typeKey}' not found; skipping positions.");
                continue;
            }

            foreach ($positions as $pos) {
                DB::table('positions')->updateOrInsert(
                    [
                        'leadership_type_id' => $leadershipTypeId,
                        'name' => $pos['name']
                    ],
                    [
                        'key' => Str::slug($pos['name'], '_') . '_' . $typeKey,
                        'rank_order' => $pos['rank'],
                        'is_executive' => $pos['exec'],
                        'is_elected' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Also add positions for Student Clubs and Organizations (SCO) - using CCO positions
        // If SCO becomes a separate leadership type, uncomment and add it to LeadershipTypeSeeder
        /*
        if (isset($leadershipTypes['sco'])) {
            $scoPositions = [
                ['name' => 'President', 'rank' => 1, 'exec' => true],
                ['name' => 'Internal Vice President', 'rank' => 2, 'exec' => true],
                ['name' => 'External Vice President', 'rank' => 3, 'exec' => true],
                ['name' => 'Secretary', 'rank' => 4, 'exec' => false],
                ['name' => 'Assistant Secretary', 'rank' => 5, 'exec' => false],
                ['name' => 'Treasurer', 'rank' => 6, 'exec' => false],
                ['name' => 'Auditor', 'rank' => 7, 'exec' => false],
                ['name' => '1st Year Representative', 'rank' => 8, 'exec' => false],
                ['name' => '2nd Year Representative', 'rank' => 9, 'exec' => false],
                ['name' => '3rd Year Representative', 'rank' => 10, 'exec' => false],
                ['name' => '4th Year Representative', 'rank' => 11, 'exec' => false],
                ['name' => 'Committee Member', 'rank' => 12, 'exec' => false],
            ];
            
            foreach ($scoPositions as $pos) {
                DB::table('positions')->insert([
                    'leadership_type_id' => $leadershipTypes['sco'],
                    'key' => Str::slug($pos['name'], '_'),
                    'name' => $pos['name'],
                    'rank_order' => $pos['rank'],
                    'is_executive' => $pos['exec'],
                    'is_elected' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        */

        $this->command?->info('✅ Positions seeded by leadership type.');
    }
}
