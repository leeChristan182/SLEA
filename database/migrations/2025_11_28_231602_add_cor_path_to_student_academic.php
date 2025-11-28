<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add COR path column to student_academic
        if (
            Schema::hasTable('student_academic') &&
            ! Schema::hasColumn('student_academic', 'certificate_of_registration_path')
        ) {

            Schema::table('student_academic', function (Blueprint $table) {
                $table->string('certificate_of_registration_path', 512)
                    ->nullable()
                    ->after('revalidated_at'); // adjust position if you like
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('student_academic') &&
            Schema::hasColumn('student_academic', 'certificate_of_registration_path')
        ) {

            Schema::table('student_academic', function (Blueprint $table) {
                $table->dropColumn('certificate_of_registration_path');
            });
        }
    }
};
