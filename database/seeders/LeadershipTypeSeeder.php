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
            ['key' => 'cco', 'name' => 'Council of Clubs and Organizations (CCO)',    'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => true], // needs cluster+org
            ['key' => 'lgu', 'name' => 'Local Government Unit (LGU)',                 'domain' => 'lgu',     'scope' => 'local',         'requires_org' => false],
            ['key' => 'lcm', 'name' => 'League of Class Mayors (LCM)',                'domain' => 'campus',  'scope' => 'institutional', 'requires_org' => false],
        ];

        foreach ($rows as $r) {
            LeadershipType::updateOrCreate(['key' => $r['key']], $r);
        }
    }
}
