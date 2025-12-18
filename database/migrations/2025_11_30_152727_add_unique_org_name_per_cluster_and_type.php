<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add the unique index if table/columns exist
        if (
            Schema::hasTable('organizations') &&
            Schema::hasColumn('organizations', 'name') &&
            Schema::hasColumn('organizations', 'cluster_id')
        ) {
            Schema::table('organizations', function (Blueprint $table) {
                // Enforce unique organization name per cluster
                $table->unique(
                    ['name', 'cluster_id'],
                    'org_name_cluster_type_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropUnique('org_name_cluster_type_unique');
            });
        }
    }
};
