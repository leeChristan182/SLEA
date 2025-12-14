<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_position', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
            $table->string('alias', 191)->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'position_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('organization_position');
    }
};
