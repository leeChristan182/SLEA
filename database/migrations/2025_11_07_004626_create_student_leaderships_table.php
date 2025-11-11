<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_leaderships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('leadership_type_id')->nullable()->constrained('leadership_types')->nullOnDelete();

            $table->string('school_year', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status', 20)->default('pending');
            $table->foreign('status')->references('key')->on('student_leadership_statuses');
            $table->string('issued_by', 255)->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'organization_id', 'position_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('student_leaderships');
    }
};
