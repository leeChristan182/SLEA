<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessor_accounts', function (Blueprint $table) {
            // Use email as primary key (non-incrementing string)
            $table->string('email_address', 50)->primary();

            // Foreign key: which admin created this assessor
            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')
                ->references('admin_id')
                ->on('admin_profiles')
                ->onDelete('cascade');

            $table->string('last_name', 50);
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('position', 50);
            $table->string('default_password', 255);
            $table->dateTime('dateacc_created');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessor_accounts');
    }
};
