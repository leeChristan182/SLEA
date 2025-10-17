<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
            Schema::create('rubric_categories', function (Blueprint $table) {
                $table->id('category_id'); // Auto-incrementing integer primary key
                $table->string('title', 50)->unique(); // e.g., Leadership Excellence
                $table->decimal('max_points', 5, 2); // e.g., 100.00
                $table->unsignedTinyInteger('order_no')->unique()->default(1); // unique global order
                $table->timestamps();
            });

    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_categories');
    }
};
