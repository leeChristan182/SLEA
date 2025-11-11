<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('approval_of_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_of_accounts', 'student_id')) {
                $table->string('student_id', 20)->primary(); // if table had another PK, rebuild is safer
            }
        });
    }
    public function down(): void {}
};
