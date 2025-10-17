<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admin_passwords', function (Blueprint $table) {
            $table->increments('password_id');
            $table->string('admin_id', 15);
            $table->string('password_hashed', 255);
            $table->dateTime('date_pass_created')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('admin_id')->on('admin_profiles')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('admin_passwords');
    }
};
