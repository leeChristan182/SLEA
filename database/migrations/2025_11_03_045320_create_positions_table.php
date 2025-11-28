<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();

            // short machine-readable key, no longer globally unique
            $table->string('key', 150);

            // human-readable name (e.g. "President", "Vice President")
            $table->string('name', 191);

            // Link each position to a leadership type (USG, CCO, etc.)
            $table->foreignId('leadership_type_id')
                ->nullable()
                ->constrained('leadership_types')
                ->onDelete('cascade');

            // For sorting in dropdowns, etc.
            $table->unsignedSmallInteger('rank_order')->default(100);

            $table->boolean('is_executive')->default(false);
            $table->boolean('is_elected')->default(true);

            $table->timestamps();

            // Composite unique: "President" can exist per leadership type,
            // but not duplicated within the same leadership_type_id
            $table->unique(
                ['leadership_type_id', 'name'],
                'positions_leadership_name_unique'
            );

            // If you *still* want key to be unique globally, uncomment this:
            // $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
