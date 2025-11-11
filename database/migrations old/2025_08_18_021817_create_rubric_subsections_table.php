<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_subsections', function (Blueprint $table) {
            $table->bigIncrements('sub_items'); // Primary Key
            $table->unsignedBigInteger('section_id'); // FK -> rubric_sections.section_id

            $table->string('sub_section', 255);
            $table->text('evidence_needed')->nullable(); // allow longer text than 255
            $table->decimal('max_points', 5, 2)->nullable(); // supports positive/negative values
            $table->text('notes')->nullable(); // allows full paragraph notes
            $table->unsignedTinyInteger('order_no')->default(1);
            $table->timestamps();

            // Foreign key relationship
            $table->foreign('section_id')
                ->references('section_id')
                ->on('rubric_sections')
                ->cascadeOnDelete();

            // Ensure each subsection order is unique within a section
            $table->unique(['section_id', 'order_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_subsections');
    }
};
