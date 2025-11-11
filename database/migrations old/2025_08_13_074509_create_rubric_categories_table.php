<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('key', 50)->unique(); // ✅ added for internal reference
            $table->string('title', 100)->unique();
            $table->text('description')->nullable();
            $table->decimal('max_points', 5, 2)->default(20.00);
            $table->unsignedTinyInteger('order_no')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_categories');
    }
};
