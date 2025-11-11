<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->foreignId('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();

            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('organizations')->nullOnDelete();

            $table->string('domain', 20)->default('campus');
            $table->string('scope_level', 20)->default('institutional');
            $table->foreign('domain')->references('key')->on('organization_domains');
            $table->foreign('scope_level')->references('key')->on('scope_levels');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'parent_id']);
            $table->index(['domain', 'scope_level']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
