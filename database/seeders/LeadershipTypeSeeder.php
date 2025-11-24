<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeadershipType;

class LeadershipTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'usg', 'name' => 'University Student Government (USG)',         'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => false],
            ['key' => 'osc', 'name' => 'Obrero Student Council (OSC)',                'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => false],
            ['key' => 'lc',  'name' => 'Local Council (LC)',                          'domain' => 'college', 'scope' => 'institutional', 'requires_org' => false],
            ['key' => 'cco', 'name' => 'Council of Clubs and Organizations (CCO)',    'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => true], // needs cluster+org (N/A)
            ['key' => 'sco', 'name' => 'Student Clubs and Organizations (SCO)',       'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => true], // needs cluster+org (actual selection)
            ['key' => 'lgu', 'name' => 'Local Government Unit (LGU)',                 'domain' => 'lgu',     'scope' => 'local',         'requires_org' => false],
            ['key' => 'lcm', 'name' => 'League of Class Mayors (LCM)',                'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => false],
            ['key' => 'eap', 'name' => 'Elective/Appointive Position (in organizations with approved/recognized Constitution and By-laws other than LGU)', 'domain' => 'campus', 'scope' => 'institutional', 'requires_org' => false],
        ];

        // Get the keys that should exist
        $validKeys = array_column($rows, 'key');

        // Delete any leadership types that are not in the seeder list
        LeadershipType::whereNotIn('key', $validKeys)->delete();

        // Update or create the leadership types from the seeder
        foreach ($rows as $r) {
            LeadershipType::updateOrCreate(['key' => $r['key']], $r);
        }
    }
}
