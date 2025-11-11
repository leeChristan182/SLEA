<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('max_points', 8, 2)->default(0);
            $table->decimal('min_required_points', 8, 2)->default(0);

            $table->string('aggregation', 20)->default('capped_sum');
            $table->foreign('aggregation')->references('key')->on('rubric_aggregations');
            $table->json('aggregation_params')->nullable();

            $table->unsignedSmallInteger('order_no')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('rubric_categories');
    }
};
