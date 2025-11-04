<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Get leadership type IDs mapped by name
        $leadershipTypes = DB::table('leadership_types')->pluck('id', 'name');

        // Define positions by leadership category
        $positions = [
            'Council of Clubs and Organizations (CCO)' => [
                'President',
                'Vice President',
                'Secretary',
                'Treasurer',
                'Auditor',
                'Public Information Officer',
                'Business Manager',
                '1st Year Representative',
                '2nd Year Representative',
                '3rd Year Representative',
                '4th Year Representative',
            ],
            'University Student Government (USG)' => [
                'Student Regent / President',
                'Vice President for Internal and External Affairs',
                'Vice President for Business Correspondence and Records',
                'Vice President for Finance, Audit and Logistics',
                'Vice President for Publication and Information',
            ],
            'Obrero Student Council (OSC)' => [
                'OSC President',
                'OSC Vice President for Internal Affairs',
                'OSC Vice President for External Affairs',
                'OSC General Secretary',
                'OSC General Treasurer',
                'OSC General Auditor',
                'OSC Public Information Officer',
            ],
            'Local Council (LC)' => [
                'Governor',
                'Vice Governor',
                'Secretary (Local Council)',
                'Treasurer (Local Council)',
                'Auditor (Local Council)',
                'Two College House Representatives (HoR)',
            ],
            'Local Government Unit (LGU)' => [
                'Municipal Councilor / SK Federated President',
                'Barangay Councilor / SK Chairperson',
                'Barangay Secretary / Treasurer',
                'SK Councilor',
                'President (Elective/Appointive Org)',
                'Vice President (Elective/Appointive Org)',
                'Indigenous People / Youth Leader',
                'Secretary / Treasurer (Elective/Appointive Org)',
            ],
        ];

        foreach ($positions as $typeName => $posArray) {
            // Ensure type exists
            $typeId = $leadershipTypes[$typeName] ?? null;
            if (!$typeId) {
                $this->command->warn("⚠️ Leadership Type '{$typeName}' not found — skipping positions.");
                continue;
            }

            foreach ($posArray as $pos) {
                DB::table('positions')->updateOrInsert(
                    [
                        'leadership_type_id' => $typeId,
                        'name' => $pos,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ Positions seeded successfully.');
    }
}
