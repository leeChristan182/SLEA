<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::create('rubric_subsections', function (Blueprint $table) {
            $table->bigIncrements('sub_items');               // PK (AI)
            $table->unsignedInteger('section_id');            // FK -> rubric_sections.section_id

            $table->string('sub_section', 255);
            $table->string('evidence_needed', 255)->nullable();
            $table->unsignedTinyInteger('order_no')->default(1);

            $table->timestamps();

            // FK (requires DB_FOREIGN_KEYS=true and a recent SQLite)
            $table->foreign('section_id')
                  ->references('section_id')->on('rubric_sections')
                  ->cascadeOnDelete();
                  $table->unique(['section_id', 'order_no']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_subsections');
    }
};
