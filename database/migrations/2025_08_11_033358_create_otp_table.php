<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp', function (Blueprint $table) {
            $table->id('otp_id'); // Primary key
            $table->foreignId('log_id')
                  ->constrained('log_in')
                  ->onDelete('cascade'); // FK relationship
            $table->string('otp_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp');
    }
};
