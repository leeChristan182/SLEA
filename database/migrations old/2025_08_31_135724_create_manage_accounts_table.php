<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('manage_accounts', function (Blueprint $table) {
            $table->increments('account_id');
            $table->string('email_address', 50);
            $table->string('admin_id', 15);
            $table->enum('user_type', ['student', 'assessor']);
            $table->string('account_status', 20)->default('active');
            $table->dateTime('last_login')->nullable();
            $table->string('action', 20)->nullable();
            $table->timestamps();

            $table->foreign('email_address')->references('email_address')->on('admin_profiles')->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('admin_profiles')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('manage_accounts');
    }
};
