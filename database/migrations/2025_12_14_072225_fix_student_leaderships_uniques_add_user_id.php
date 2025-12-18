<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $table = 'student_leaderships';

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($rows);
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $rows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $constraintName]);

        return !empty($rows);
    }

    private function ensureIndex(string $column, string $indexName): void
    {
        if (!Schema::hasTable($this->table)) return;
        if (!Schema::hasColumn($this->table, $column)) return;

        if (!$this->indexExists($this->table, $indexName)) {
            Schema::table($this->table, function (Blueprint $t) use ($column, $indexName) {
                $t->index($column, $indexName);
            });
        }
    }

    private function dropIndexIfExists(string $indexName): void
    {
        if (!Schema::hasTable($this->table)) return;

        if ($this->indexExists($this->table, $indexName)) {
            Schema::table($this->table, function (Blueprint $t) use ($indexName) {
                // dropUnique/dropIndex both drop via index name, but use dropIndex for non-unique indexes
                $t->dropIndex($indexName);
            });
        }
    }

    private function dropUniqueIfExists(string $uniqueName): void
    {
        if (!Schema::hasTable($this->table)) return;

        if ($this->indexExists($this->table, $uniqueName)) {
            Schema::table($this->table, function (Blueprint $t) use ($uniqueName) {
                $t->dropUnique($uniqueName);
            });
        }
    }

    private function ensureUnique(array $columns, string $uniqueName): void
    {
        if (!Schema::hasTable($this->table)) return;

        foreach ($columns as $col) {
            if (!Schema::hasColumn($this->table, $col)) {
                // If any column is missing, don't create the unique (prevents migration crash)
                return;
            }
        }

        if (!$this->indexExists($this->table, $uniqueName)) {
            Schema::table($this->table, function (Blueprint $t) use ($columns, $uniqueName) {
                $t->unique($columns, $uniqueName);
            });
        }
    }

    private function ensureLeadershipStatusFk(): void
    {
        if (!Schema::hasTable($this->table)) return;
        if (!Schema::hasColumn($this->table, 'leadership_status')) return;
        if (!Schema::hasTable('student_leadership_statuses')) return;

        // Default Laravel name for: student_leaderships.leadership_status -> student_leadership_statuses.key
        $fkName = 'student_leaderships_leadership_status_foreign';

        if (!$this->foreignKeyExists($this->table, $fkName)) {
            Schema::table($this->table, function (Blueprint $t) use ($fkName) {
                $t->foreign('leadership_status', $fkName)
                    ->references('key')
                    ->on('student_leadership_statuses');
            });
        }
    }

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            // If the table doesn't exist, do nothing.
            // (Your create_student_leaderships_table migration should create it.)
            return;
        }

        /**
         * 1) Ensure standalone indexes exist for FK columns
         *    This prevents MySQL from "depending" on a UNIQUE composite index
         *    as the supporting index for a FK (which causes error 1553).
         *
         *    IMPORTANT: These are safe even if they already exist.
         */
        $this->ensureIndex('user_id', 'sl_user_id_idx');
        $this->ensureIndex('leadership_type_id', 'sl_leadership_type_id_idx');
        $this->ensureIndex('cluster_id', 'sl_cluster_id_idx');
        $this->ensureIndex('organization_id', 'sl_org_id_idx');
        $this->ensureIndex('position_id', 'sl_position_id_idx');

        /**
         * 2) Drop old uniques if they exist (safe checks)
         *    Handles both your older names and any partially applied states.
         */
        $this->dropUniqueIfExists('uniq_non_org_position_per_term');
        $this->dropUniqueIfExists('uniq_org_position_per_term');

        // If you had already created per-user uniques before, drop them too (so we can standardize)
        $this->dropUniqueIfExists('uniq_user_non_org_position_per_term');
        $this->dropUniqueIfExists('uniq_user_org_position_per_term');

        // Also handle the names you used in one of your drafts
        $this->dropUniqueIfExists('uniq_assessor_admin_final_review'); // harmless if not present

        /**
         * 3) Create the correct per-user uniques (idempotent)
         *
         * Choose YOUR final intended rule here.
         * Recommended:
         * - Non-org leadership (organization_id is null): unique by user + type + position + term
         * - Org leadership (organization_id not null): unique by user + type + org + position + term
         */
        $this->ensureUnique(
            ['user_id', 'leadership_type_id', 'position_id', 'term'],
            'uniq_user_non_org_position_per_term'
        );

        $this->ensureUnique(
            ['user_id', 'leadership_type_id', 'organization_id', 'position_id', 'term'],
            'uniq_user_org_position_per_term'
        );

        /**
         * 4) Ensure leadership_status FK exists (optional but safe)
         */
        $this->ensureLeadershipStatusFk();
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) return;

        // Reverse: drop the per-user uniques we added
        $this->dropUniqueIfExists('uniq_user_non_org_position_per_term');
        $this->dropUniqueIfExists('uniq_user_org_position_per_term');

        // (Optional) you can recreate the old ones here if you really want
        // $this->ensureUnique(['leadership_type_id', 'position_id', 'term'], 'uniq_non_org_position_per_term');
        // $this->ensureUnique(['leadership_type_id', 'organization_id', 'position_id', 'term'], 'uniq_org_position_per_term');
    }
};
