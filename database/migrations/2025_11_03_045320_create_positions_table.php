<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();   // e.g., president, vp_internal
            $table->string('name', 255)->unique();  // canonical label
            $table->unsignedSmallInteger('rank_order')->default(100);
            $table->boolean('is_executive')->default(false);
            $table->boolean('is_elected')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
