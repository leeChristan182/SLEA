<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_subsection_leadership', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('sub_section_id')->nullable(); // 🔹 New column
            $table->string('position', 255);
            $table->decimal('points', 4, 2);
            $table->unsignedTinyInteger('position_order')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('section_id')
                ->references('section_id')
                ->on('rubric_sections')
                ->cascadeOnDelete();

            $table->foreign('sub_section_id') // 🔹 New relationship
                ->references('sub_items')
                ->on('rubric_subsections')
                ->nullOnDelete(); // If subsection deleted, keep record but set null

            // Ensure unique order per section
            $table->unique(['section_id', 'sub_section_id', 'position_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_subsection_leadership');
    }
};
