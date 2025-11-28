<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_subsections', function (Blueprint $table) {
            $table->id('sub_section_id');

            $table->unsignedBigInteger('section_id');
            $table->foreign('section_id')->references('section_id')->on('rubric_sections')->onDelete('cascade');

            $table->string('key', 150)->nullable()->unique();
            $table->string('sub_section', 191);
            $table->text('evidence_needed')->nullable();
            $table->decimal('max_points', 8, 2)->nullable();
            $table->decimal('cap_points', 8, 2)->nullable();

            $table->string('scoring_method', 10)->default('fixed');
            $table->foreign('scoring_method')->references('key')->on('scoring_methods');
            $table->string('unit', 50)->nullable();
            $table->json('score_params')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('order_no')->default(1);
            $table->timestamps();

            $table->unique(['section_id', 'sub_section']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('rubric_subsections');
    }
};
