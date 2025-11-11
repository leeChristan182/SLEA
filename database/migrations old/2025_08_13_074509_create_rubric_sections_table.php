<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_sections', function (Blueprint $table) {
            $table->bigIncrements('section_id');
            $table->unsignedBigInteger('category_id');
            $table->string('title', 255);
            $table->text('evidence')->nullable();
            $table->decimal('max_points', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('order_no')->default(1);
            $table->timestamps();

            $table->foreign('category_id')
                ->references('category_id')
                ->on('rubric_categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_sections');
    }
};
