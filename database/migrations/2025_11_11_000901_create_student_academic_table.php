<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_academic', function (Blueprint $table) {
            $table->id();

            // Link to user
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Student no. and program affiliation
            $table->string('student_number', 20)->unique();

            $table->foreignId('college_id')
                ->nullable()
                ->constrained('colleges')
                ->nullOnDelete();

            $table->foreignId('program_id')
                ->nullable()
                ->constrained('programs')
                ->nullOnDelete();

            $table->foreignId('major_id')
                ->nullable()
                ->constrained('majors')
                ->nullOnDelete();

            // Misc
            $table->integer('year_level')->nullable();
            $table->unsignedSmallInteger('graduate_prior')->nullable();

            // Extra fields used in your app (optional but consistent)
            $table->unsignedSmallInteger('expected_grad_year')->nullable();
            $table->string('eligibility_status', 30)->nullable();
            $table->timestamp('revalidated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic');
    }
};
