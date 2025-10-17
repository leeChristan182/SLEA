<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submission_records', function (Blueprint $table) {
            // Add foreign key columns for rubric relationships
            $table->unsignedInteger('category_id')->nullable()->after('leadership_id');
            $table->unsignedInteger('section_id')->nullable()->after('category_id');
            $table->unsignedInteger('sub_items')->nullable()->after('section_id');
            
            // Add indexes for better performance
            $table->index('category_id');
            $table->index('section_id');
            $table->index('sub_items');
            
            // Add foreign key constraints
            $table->foreign('category_id')->references('category_id')->on('rubric_categories')->onDelete('set null');
            $table->foreign('section_id')->references('section_id')->on('rubric_sections')->onDelete('set null');
            $table->foreign('sub_items')->references('sub_items')->on('rubric_subsections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_records', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['category_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['sub_items']);
            
            // Drop indexes
            $table->dropIndex(['category_id']);
            $table->dropIndex(['section_id']);
            $table->dropIndex(['sub_items']);
            
            // Drop columns
            $table->dropColumn(['category_id', 'section_id', 'sub_items']);
        });
    }
};