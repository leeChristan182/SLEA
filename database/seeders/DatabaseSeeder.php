<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersAdminSeeder;
use Database\Seeders\CollegesProgramsMajorsSeeder;
use Database\Seeders\ClusterSeeder;
use Database\Seeders\LeadershipTypeSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\RubricCategorySeeder;
use Database\Seeders\RubricSectionsSeeder;
use Database\Seeders\RubricSubsectionsSeeder;
use Database\Seeders\RubricOptionsSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🔒 Disable foreign key checks for SQLite/MySQL
        DB::statement('PRAGMA foreign_keys = OFF;');
        // For MySQL, use: DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Call all your seeders safely
        $this->call([
            UsersAdminSeeder::class,
            CollegesProgramsMajorsSeeder::class,
            LeadershipTypeSeeder::class,
            ClusterSeeder::class,
            OrganizationSeeder::class,
            PositionSeeder::class,
            RubricCategorySeeder::class,
            RubricSectionsSeeder::class,
            RubricSubsectionsSeeder::class,
            RubricOptionsSeeder::class,
        ]);

        // 🔓 Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys = ON;');
        // For MySQL: DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
