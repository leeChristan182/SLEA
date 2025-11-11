<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_sections', function (Blueprint $table) {
            $table->id('section_id');
            $table->foreignId('category_id')
                ->constrained('rubric_categories')->onDelete('cascade');

            $table->string('key', 150)->nullable()->unique();
            $table->string('title', 255);
            $table->text('evidence')->nullable();

            // Optional aggregation config (but no max points here)
            $table->string('aggregation', 20)->default('sum');
            $table->foreign('aggregation')->references('key')->on('rubric_aggregations');
            $table->json('aggregation_params')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('order_no')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_sections');
    }
};
