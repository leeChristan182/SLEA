<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_monitoring_and_logs', function (Blueprint $table) {
            $table->id('logs_id'); // PK
            $table->unsignedBigInteger('log_id')->nullable(); // FK -> log_in.log_id
            $table->string('user_role')->nullable();
            $table->string('user_name')->nullable();
            $table->string('activity_type');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('log_id')
                ->references('log_id')
                ->on('log_in')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_monitoring_and_logs');
    }
};
