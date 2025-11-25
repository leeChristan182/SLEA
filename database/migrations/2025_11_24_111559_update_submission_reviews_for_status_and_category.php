<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submission_reviews', function (Blueprint $table) {
            // Link to main SLEA rubric category for easier querying
            $table->unsignedBigInteger('rubric_category_id')
                ->nullable()
                ->after('submission_id');

            $table->foreign('rubric_category_id')
                ->references('id')
                ->on('rubric_categories')
                ->nullOnDelete();

            // New workflow-like status (mirrors submissions.status)
            $table->string('status', 32)
                ->nullable()
                ->after('score_source'); // or after 'override_reason' if you prefer

            // New remarks field (mirrors submissions.remarks)
            $table->text('remarks')
                ->nullable()
                ->after('status');

            // Status should use the same enum table as submissions.status
            $table->foreign('status')
                ->references('key')
                ->on('submission_statuses');

            $table->index(['status', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('submission_reviews', function (Blueprint $table) {
            // Drop FKs first, then columns
            $table->dropForeign(['rubric_category_id']);
            $table->dropForeign(['status']);

            $table->dropIndex(['status', 'reviewed_at']);

            $table->dropColumn('rubric_category_id');
            $table->dropColumn('status');
            $table->dropColumn('remarks');
        });
    }
};
