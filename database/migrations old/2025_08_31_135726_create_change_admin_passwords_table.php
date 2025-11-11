<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('change_admin_passwords', function (Blueprint $table) {
            $table->increments('change_id');
            $table->string('admin_id', 15);
            $table->string('old_password_hashed', 255);
            $table->string('password_hashed', 255);
            $table->dateTime('date_pass_changed')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('admin_id')->on('admin_profiles')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('change_admin_passwords');
    }
};
