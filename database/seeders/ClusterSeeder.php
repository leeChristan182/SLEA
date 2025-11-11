<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cluster;
use Illuminate\Support\Str;

class ClusterSeeder extends Seeder
{
    public function run()
    {
        $clusters = [
            ['key' => 'academic',     'name' => 'Academic Cluster'],
            ['key' => 'campus_min',   'name' => 'Campus Ministry Cluster'],
            ['key' => 'culture_arts', 'name' => 'Culture and Arts Cluster'],
            ['key' => 'socio_civic',  'name' => 'Socio-Civic Cluster'],
            ['key' => 'sports',       'name' => 'Sports Cluster'],
        ];

        foreach ($clusters as $c) {
            \App\Models\Cluster::updateOrCreate(['name' => $c['name']], $c);
        }
    }
}
