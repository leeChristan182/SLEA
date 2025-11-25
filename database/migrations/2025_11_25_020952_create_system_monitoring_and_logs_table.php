<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_monitoring_and_logs', function (Blueprint $table) {
            $table->bigIncrements('log_id');
            $table->enum('user_role', ['admin', 'assessor', 'student'])->nullable();
            $table->string('user_name', 150);
            $table->string('activity_type', 50);        // Login, Logout, Create, Update, Delete, etc.
            $table->text('description')->nullable();    // What happened
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_monitoring_and_logs');
    }
};
