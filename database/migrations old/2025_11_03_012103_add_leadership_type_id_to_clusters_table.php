<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('clusters', 'leadership_type_id')) {
            Schema::table('clusters', function (Blueprint $table) {
                $table->foreignId('leadership_type_id')
                    ->nullable()
                    ->constrained('leadership_types')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            if (Schema::hasColumn('clusters', 'leadership_type_id')) {
                $table->dropConstrainedForeignId('leadership_type_id');
            }
        });
    }
};
