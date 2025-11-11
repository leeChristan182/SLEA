<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();

            $table->string('email', 100)->unique();
            $table->string('password', 255);            // ensure length
            $table->string('contact', 20)->nullable();

            $table->date('birth_date')->nullable();     // enforce “student ⇒ required” in validation or trigger
            $table->string('profile_picture_path')->nullable();

            $table->string('role', 20);
            $table->string('status', 20)->default('pending');

            $table->timestamps();

            $table->foreign('role')->references('key')->on('user_roles');
            $table->foreign('status')->references('key')->on('user_statuses');
        });

        Schema::create('password_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('previous_password_hash', 255);
            $table->timestamp('changed_at')->useCurrent();
            $table->string('changed_by')->nullable();  // 'self' or admin id/email
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('assessor_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            // $table->string('default_password', 255);   // REMOVE
            $table->string('temporary_password_hash', 255)->nullable(); // if you insist on temp passwords
            $table->boolean('must_change_password')->default(true);
            $table->dateTime('date_created');
            $table->timestamps();
        });

        // Optional: Admin privileges (if needed)
        Schema::create('admin_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to users
            $table->string('admin_level')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_privileges');
        Schema::dropIfExists('assessor_info');
        Schema::dropIfExists('password_changes');
        Schema::dropIfExists('student_academic');
        Schema::dropIfExists('users');
    }
};
