<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id('admin_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email_address', 100)->unique();
            $table->string('contact_number', 20)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('profile_picture_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_profiles');
    }
};
