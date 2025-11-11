<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadership_types', function (Blueprint $table) {
            $table->id();

            // Leadership type name (e.g., President, Treasurer)
            $table->string('name')->unique();

            // Foreign keys to connect relationships
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('rubric_leadership_id')->nullable();

            $table->timestamps();

            // Define foreign key relationships
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->foreign('rubric_leadership_id')
                ->references('id')
                ->on('rubric_subsection_leadership')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadership_types');
    }
};
