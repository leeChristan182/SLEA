<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leadership_types', function (Blueprint $table) {
            if (!Schema::hasColumn('leadership_types', 'organization_id')) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('name')
                    ->constrained('organizations')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('leadership_types', 'rubric_leadership_id')) {
                $table->foreignId('rubric_leadership_id')
                    ->nullable()
                    ->after('organization_id')
                    ->constrained('rubric_subsection_leadership')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('leadership_types', function (Blueprint $table) {
            if (Schema::hasColumn('leadership_types', 'organization_id')) {
                $table->dropConstrainedForeignId('organization_id');
            }

            if (Schema::hasColumn('leadership_types', 'rubric_leadership_id')) {
                $table->dropConstrainedForeignId('rubric_leadership_id');
            }
        });
    }
};
