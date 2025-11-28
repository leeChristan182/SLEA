<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_edit_history', function (Blueprint $table) {
            $table->bigIncrements('edit_id');        // Primary Key
            $table->string('admin_id', 15);          // Optional FK if you add admins later

            // Foreign Key to rubric_subsection_leadership.id
            $table->unsignedBigInteger('subsection_id');

            $table->dateTime('edit_timestamp');
            $table->string('changes_made', 191)->nullable();
            $table->string('field_edited', 191)->nullable();
            $table->timestamps();

            // ✅ Foreign Key setup (must match PK type in rubric_subsection_leadership)
            $table->foreign('subsection_id')
                ->references('id')
                ->on('rubric_subsection_leadership')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_edit_history');
    }
};
