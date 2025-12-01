<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Avoid duplicate organizations with same name, cluster, and leadership type
            $table->unique(
                ['name', 'cluster_id', 'leadership_type_id'],
                'org_name_cluster_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropUnique('org_name_cluster_type_unique');
        });
    }
};
