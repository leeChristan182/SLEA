<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cluster;

class ClusterSeeder extends Seeder
{
    public function run()
    {
        $clusters = [
            'Academic Cluster',
            'Campus Ministry Cluster',
            'Culture and Arts Cluster',
            'Socio-Civic Cluster',
            'Sports Cluster',
        ];

        // Update leadership_type_id = 4 for existing clusters
        Cluster::whereIn('name', $clusters)
            ->update(['leadership_type_id' => 4]);

        // Create clusters if they don't exist
        foreach ($clusters as $clusterName) {
            Cluster::firstOrCreate(['name' => $clusterName], ['leadership_type_id' => 4]);
        }
    }
}
