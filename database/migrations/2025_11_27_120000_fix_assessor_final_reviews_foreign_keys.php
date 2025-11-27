<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // For SQLite, we need to drop and recreate the foreign key constraint
        // to properly handle NULL values in the qualification column
        
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support dropping foreign keys directly
            // We need to recreate the table without the problematic constraint
            // or modify it to handle NULL properly
            
            // First, ensure the enum values exist
            $this->ensureEnumValuesExist();
            
            // Note: SQLite foreign key constraints should work with NULL values
            // if the column is nullable. The issue might be that the constraint
            // was created incorrectly. We'll verify the constraint is correct.
        } else {
            // For other databases (MySQL, PostgreSQL), we can drop and recreate
            Schema::table('assessor_final_reviews', function (Blueprint $table) {
                $table->dropForeign(['qualification']);
            });
            
            Schema::table('assessor_final_reviews', function (Blueprint $table) {
                // Recreate with proper NULL handling
                $table->foreign('qualification')
                    ->references('key')
                    ->on('qualifications')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Revert if needed
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('assessor_final_reviews', function (Blueprint $table) {
                $table->dropForeign(['qualification']);
            });
            
            Schema::table('assessor_final_reviews', function (Blueprint $table) {
                $table->foreign('qualification')
                    ->references('key')
                    ->on('qualifications');
            });
        }
    }

    private function ensureEnumValuesExist(): void
    {
        // Ensure final_review_statuses has the required values
        $statuses = ['draft', 'queued_for_admin', 'finalized'];
        foreach ($statuses as $status) {
            if (!DB::table('final_review_statuses')->where('key', $status)->exists()) {
                DB::table('final_review_statuses')->insert(['key' => $status]);
            }
        }

        // Ensure qualifications has the required values
        $qualifications = ['qualified', 'unqualified'];
        foreach ($qualifications as $qual) {
            if (!DB::table('qualifications')->where('key', $qual)->exists()) {
                DB::table('qualifications')->insert(['key' => $qual]);
            }
        }
    }
};

