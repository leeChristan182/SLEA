<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\RubricSection;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CollegeProgramSeeder;
use Database\Seeders\ClusterSeeder;
use Database\Seeders\LeadershipTypeSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\RubricCategorySeeder;
use Database\Seeders\RubricSectionsSeeder;
use Database\Seeders\RubricSubsectionsSeeder;
use Database\Seeders\RubricSubsectionsLeadershipSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🔒 Disable foreign key checks for SQLite/MySQL
        DB::statement('PRAGMA foreign_keys = OFF;');
        // For MySQL, use: DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Call all your seeders safely
        $this->call([
            AdminSeeder::class,
            CollegeProgramSeeder::class,
            LeadershipTypeSeeder::class,
            ClusterSeeder::class,
            OrganizationSeeder::class,
            PositionSeeder::class,
            RubricCategorySeeder::class,
            RubricSectionsSeeder::class,
            RubricSubsectionsSeeder::class,
            RubricSubsectionsLeadershipSeeder::class,
        ]);

        // 🔓 Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys = ON;');
        // For MySQL: DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
