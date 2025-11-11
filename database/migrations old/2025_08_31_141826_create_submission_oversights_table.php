<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_oversights', function (Blueprint $table) {
            $table->id('sub_oversight_id'); // PK
            $table->unsignedBigInteger('pending_sub_id'); // FK1
            $table->string('admin_id', 15); // FK2
            $table->string('submission_status', 20);
            $table->string('flag', 20)->nullable();
            $table->string('action', 20)->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('pending_sub_id')->references('pending_sub_id')->on('pending_submissions')->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_oversights');
    }
};
