<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rubric_subsection_leadership', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('position', 255);
            $table->decimal('points', 4, 2);
            $table->unsignedTinyInteger('position_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_subsection_leadership');
    }
};
