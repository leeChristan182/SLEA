<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_of_accounts', function (Blueprint $table) {
            // PK = student_id (string 20), matches academic_information.student_id
            $table->string('student_id', 20)->primary();

            // second FK (adjust table/col to your real admins table)
            $table->string('admin_id', 20)->nullable()->index();

            $table->string('action', 50);
            $table->dateTime('action_date')->nullable();

            // FKs (works if your SQLite version enforces FKs and DB_FOREIGN_KEYS=true)
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('academic_information')
                  ->cascadeOnDelete();

            // If you have an admins table:
            // $table->foreign('admin_id')->references('admin_id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_of_accounts');
    }
};
