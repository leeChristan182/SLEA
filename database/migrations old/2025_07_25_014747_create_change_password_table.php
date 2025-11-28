<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('change_passwords', function (Blueprint $table) {
            $table->id('change_pass_id');
            $table->string('email_address', 191);
            $table->string('old_password', 191);
            $table->string('new_password', 191);
            $table->timestamp('date_changed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_passwords');
    }
};
