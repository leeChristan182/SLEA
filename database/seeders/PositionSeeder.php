<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Canonical positions (points are NOT here; points live in rubric options)
        $positions = [
            // USG-specific
            ['name' => 'Student Regent / President',                         'rank' => 1,  'exec' => true],
            ['name' => 'Vice President for Internal and External Affairs',   'rank' => 2,  'exec' => true],
            ['name' => 'Vice President for Business Correspondence and Records', 'rank' => 3, 'exec' => true],
            ['name' => 'Vice President for Finance, Audit and Logistics',    'rank' => 4,  'exec' => true],
            ['name' => 'Vice President for Publication and Information',     'rank' => 5,  'exec' => true],

            // Common student-org roles
            ['name' => 'President',                       'rank' => 6,  'exec' => true],
            ['name' => 'Vice President',                  'rank' => 7,  'exec' => true],
            ['name' => 'Vice President for Internal Affairs', 'rank' => 7, 'exec' => true],
            ['name' => 'Vice President for External Affairs', 'rank' => 7, 'exec' => true],
            ['name' => 'General Secretary',               'rank' => 10, 'exec' => false],
            ['name' => 'General Treasurer',               'rank' => 10, 'exec' => false],
            ['name' => 'General Auditor',                 'rank' => 10, 'exec' => false],
            ['name' => 'Public Information Officer',      'rank' => 12, 'exec' => false],
            ['name' => 'Business Manager',                'rank' => 12, 'exec' => false],
            ['name' => 'Secretary',                       'rank' => 15, 'exec' => false],
            ['name' => 'Treasurer',                       'rank' => 15, 'exec' => false],
            ['name' => 'Auditor',                         'rank' => 15, 'exec' => false],

            // Representatives
            ['name' => 'College House Representative',    'rank' => 30, 'exec' => false],
            ['name' => '1st Year Representative',         'rank' => 30, 'exec' => false],
            ['name' => '2nd Year Representative',         'rank' => 30, 'exec' => false],
            ['name' => '3rd Year Representative',         'rank' => 30, 'exec' => false],
            ['name' => '4th Year Representative',         'rank' => 30, 'exec' => false],

            // LC-specific labels
            ['name' => 'Secretary (Local Council)',       'rank' => 15, 'exec' => false],
            ['name' => 'Treasurer (Local Council)',       'rank' => 15, 'exec' => false],
            ['name' => 'Auditor (Local Council)',         'rank' => 15, 'exec' => false],
            ['name' => 'Governor',                        'rank' => 8,  'exec' => true],
            ['name' => 'Vice Governor',                   'rank' => 9,  'exec' => true],

            // LGU / community
            ['name' => 'Municipal Councilor / SK Federated President', 'rank' => 5,  'exec' => false],
            ['name' => 'Barangay Councilor / SK Chairperson',          'rank' => 6,  'exec' => false],
            ['name' => 'Barangay Secretary / Treasurer',               'rank' => 15, 'exec' => false],
            ['name' => 'SK Councilor',                                 'rank' => 25, 'exec' => false],
            ['name' => 'Indigenous People / Youth Leader',             'rank' => 25, 'exec' => false],
            ['name' => 'Secretary / Treasurer (Elective/Appointive Org)', 'rank' => 20, 'exec' => false],
            ['name' => 'President (Elective/Appointive Org)',          'rank' => 10, 'exec' => true],
            ['name' => 'Vice President (Elective/Appointive Org)',     'rank' => 12, 'exec' => true],
        ];

        foreach ($positions as $p) {
            DB::table('positions')->updateOrInsert(
                ['name' => $p['name']],
                [
                    'key'          => Str::slug($p['name'], '_'),
                    'rank_order'   => $p['rank'],
                    'is_executive' => $p['exec'],
                    'is_elected'   => $p['elected'] ?? true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }

        // Refresh maps
        $posIdByName = DB::table('positions')->pluck('id', 'name');
        $orgIdByName = DB::table('organizations')->pluck('id', 'name');

        // 2) Attach positions to special orgs (aliases show up in UI)
        $attach = function (string $orgName, array $items) use ($orgIdByName, $posIdByName) {
            $orgId = $orgIdByName[$orgName] ?? null;
            if (!$orgId) {
                echo "⚠️ Org '{$orgName}' not found; skipping.\n";
                return;
            }

            foreach ($items as $item) {
                // Accept either string or ['name' => 'Canonical', 'alias' => 'Custom Label']
                if (is_string($item)) {
                    $name  = $item;
                    $alias = null;
                } else {
                    $name  = $item['name'] ?? null;
                    $alias = $item['alias'] ?? null;
                }
                if (!$name) continue;

                $posId = $posIdByName[$name] ?? null;
                if (!$posId) {
                    echo "⚠️ Position '{$name}' not found; skipping.\n";
                    continue;
                }

                DB::table('organization_position')->updateOrInsert(
                    ['organization_id' => $orgId, 'position_id' => $posId],
                    ['alias' => $alias, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        };

        // USG
        $attach('University Student Government (USG)', [
            'Student Regent / President',
            'Vice President for Internal and External Affairs',
            'Vice President for Business Correspondence and Records',
            'Vice President for Finance, Audit and Logistics',
            'Vice President for Publication and Information',
            'General Secretary',
            'General Treasurer',
            'General Auditor',
            'Public Information Officer',
        ]);

        // OSC (use aliases so labels match paper form expectations)
        $attach('Obrero Student Council (OSC)', [
            ['name' => 'President',                         'alias' => 'OSC President'],
            ['name' => 'Vice President for Internal Affairs', 'alias' => 'OSC Vice President for Internal Affairs'],
            ['name' => 'Vice President for External Affairs', 'alias' => 'OSC Vice President for External Affairs'],
            ['name' => 'General Secretary',                 'alias' => 'OSC General Secretary'],
            ['name' => 'General Treasurer',                 'alias' => 'OSC General Treasurer'],
            ['name' => 'General Auditor',                   'alias' => 'OSC General Auditor'],
            ['name' => 'Public Information Officer',        'alias' => 'OSC Public Information Officer'],
        ]);

        // Local Council (LC)
        $attach('Local Council (LC)', [
            'Governor',
            'Vice Governor',
            'Secretary (Local Council)',
            'Treasurer (Local Council)',
            'Auditor (Local Council)',
            '4th Year Representative',
            '3rd Year Representative',
            '2nd Year Representative',
            '1st Year Representative',
        ]);

        // CCO
        $attach('Council of Clubs and Organizations (CCO)', [
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
        ]);

        // LGU
        $attach('Local Government Unit (LGU)', [
            'Municipal Councilor / SK Federated President',
            'Barangay Councilor / SK Chairperson',
            'Barangay Secretary / Treasurer',
            'SK Councilor',
            'Indigenous People / Youth Leader',
            'President (Elective/Appointive Org)',
            'Vice President (Elective/Appointive Org)',
            'Secretary / Treasurer (Elective/Appointive Org)',
        ]);

        // 3) Give default positions to all other orgs (clubs etc.)
        $special = [
            'University Student Government (USG)',
            'Obrero Student Council (OSC)',
            'Local Council (LC)',
            'Council of Clubs and Organizations (CCO)',
            'Local Government Unit (LGU)',
        ];
        $defaultPositions = [
            'President',
            'Vice President',
            'Secretary',
            'Treasurer',
            'Auditor',
            'Public Information Officer',
            'Business Manager',
        ];

        $defaultPosIds = array_values(array_filter(array_map(fn($n) => $posIdByName[$n] ?? null, $defaultPositions)));
        foreach ($orgIdByName as $orgName => $orgId) {
            if (in_array($orgName, $special, true)) continue;

            foreach ($defaultPosIds as $pid) {
                DB::table('organization_position')->updateOrInsert(
                    ['organization_id' => $orgId, 'position_id' => $pid],
                    ['alias' => null, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $this->command?->info('✅ Positions and org-position links seeded/updated.');
    }
}
