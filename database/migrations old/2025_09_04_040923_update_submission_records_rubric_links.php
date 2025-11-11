<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations safely.
     */
    public function up(): void
    {
        Schema::table('submission_records', function (Blueprint $table) {
            // === Add missing columns only if they don't exist ===
            if (!Schema::hasColumn('submission_records', 'category_id')) {
                $table->unsignedInteger('category_id')->nullable()->after('leadership_id');
                $table->index('category_id');
            }

            if (!Schema::hasColumn('submission_records', 'section_id')) {
                $table->unsignedInteger('section_id')->nullable()->after('category_id');
                $table->index('section_id');
            }

            if (!Schema::hasColumn('submission_records', 'sub_items')) {
                $table->unsignedInteger('sub_items')->nullable()->after('section_id');
                $table->index('sub_items');
            }
        });

        // === Add foreign keys safely ===
        Schema::table('submission_records', function (Blueprint $table) {
            try {
                if (!Schema::hasColumn('submission_records', 'category_id_fk')) {
                    $table->foreign('category_id')
                        ->references('category_id')
                        ->on('rubric_categories')
                        ->onDelete('set null');
                }

                if (!Schema::hasColumn('submission_records', 'section_id_fk')) {
                    $table->foreign('section_id')
                        ->references('section_id')
                        ->on('rubric_sections')
                        ->onDelete('set null');
                }

                if (!Schema::hasColumn('submission_records', 'sub_items_fk')) {
                    $table->foreign('sub_items')
                        ->references('sub_items')
                        ->on('rubric_subsections')
                        ->onDelete('set null');
                }
            } catch (\Throwable $e) {
                // Ignore FK errors in SQLite (common in local dev)
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_records', function (Blueprint $table) {
            // Safely drop FKs if they exist
            if (Schema::hasColumn('submission_records', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropIndex(['category_id']);
            }
            if (Schema::hasColumn('submission_records', 'section_id')) {
                $table->dropForeign(['section_id']);
                $table->dropIndex(['section_id']);
            }
            if (Schema::hasColumn('submission_records', 'sub_items')) {
                $table->dropForeign(['sub_items']);
                $table->dropIndex(['sub_items']);
            }

            // Drop columns only if present
            $columns = collect(['category_id', 'section_id', 'sub_items'])
                ->filter(fn($col) => Schema::hasColumn('submission_records', $col))
                ->all();

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
