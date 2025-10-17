<?php

namespace Database\Seeders;

use App\Models\RubricSection;   
use Illuminate\Database\Seeder;
use Database\Seeders\ClusterSeeder;
use Database\Seeders\LeadershipTypeSeeder;
use Database\Seeders\RubricCategorySeeder;
use Database\Seeders\RubricSectionsSeeder;
use Database\Seeders\RubricSubsectionsSeeder;
use Database\Seeders\RubricSubsectionsLeadershipSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ClusterSeeder::class,
            LeadershipTypeSeeder::class,
            OrganizationSeeder::class,
            RubricCategorySeeder::class,
           RubricSectionsSeeder::class,
            RubricSubsectionsSeeder::class,
            RubricSubsectionsLeadershipSeeder::class,
        ]);
    }
}
