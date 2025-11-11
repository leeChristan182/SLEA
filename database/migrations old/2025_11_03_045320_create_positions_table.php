<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leadership_type_id'); // linked to leadership_types
            $table->string('name', 255); // Position name, e.g., President, Treasurer, etc.
            $table->timestamps();

            $table->foreign('leadership_type_id')
                ->references('id')
                ->on('leadership_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
