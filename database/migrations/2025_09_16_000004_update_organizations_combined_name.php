<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Organization;
use App\Models\Cluster;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the combined_name for all organizations based on the new cluster names
        $organizations = Organization::all();
        
        foreach ($organizations as $organization) {
            $cluster = Cluster::find($organization->cluster_id);
            
            if ($cluster) {
                $organization->combined_name = $cluster->name . ' - ' . $organization->name;
                $organization->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this as it's just updating data
    }
};