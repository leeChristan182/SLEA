<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assessor_profiles', function (Blueprint $table) {
            $table->string('assessor_id', 15)->primary();
            $table->string('email_address', 50);
            $table->string('picture_path', 255)->nullable();
            $table->dateTime('date_upload');
            $table->timestamps();

            $table->foreign('email_address')->references('email_address')->on('assessor_accounts')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('assessor_profiles');
    }
};
