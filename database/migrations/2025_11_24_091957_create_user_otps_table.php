<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_user_otps_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('code_hash');           // hashed 6-digit code
            $table->string('context')->nullable(); // 'login', 'password_reset', etc.
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable(); // null = still active

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
