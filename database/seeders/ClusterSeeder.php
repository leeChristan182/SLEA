<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cluster;

class ClusterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $clusters = [
            ['name' => 'Academic Cluster'],
            ['name' => 'Campus Ministry Cluster'],
            ['name' => 'Culture and Arts Cluster'],
            ['name' => 'Socio-Civic Cluster'],
            ['name' => 'Sports Cluster'],
        ];

        foreach ($clusters as $cluster) {
            Cluster::firstOrCreate($cluster);
        }
    }
}
