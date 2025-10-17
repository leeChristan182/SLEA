<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_sections', function (Blueprint $table) {
            $table->string('section_id', 20)->primary();  
           
   // String primary key
            $table->unsignedInteger('category_id');             // FK -> rubric_categories.category_id
            $table->string('title', 255);
            $table->unsignedTinyInteger('order_no')->default(1);
            $table->timestamps();

            $table->index('category_id');

            // Prevent duplicates within the same category
            $table->unique(['category_id','title']);
            $table->unique(['category_id','order_no']);

            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('rubric_categories')
                  ->onDelete('cascade'); // delete sections when category is deleted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_sections');
    }
};
