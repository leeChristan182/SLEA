<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sub_section_id');
            $table->foreign('sub_section_id')->references('sub_section_id')->on('rubric_subsections')->onDelete('cascade');

            $table->string('code', 100)->nullable();
            $table->string('label', 255);
            $table->decimal('points', 8, 2);   // can be negative
            $table->unsignedSmallInteger('order_no')->default(1);

            $table->timestamps();
            $table->unique(['sub_section_id', 'label']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('rubric_options');
    }
};
