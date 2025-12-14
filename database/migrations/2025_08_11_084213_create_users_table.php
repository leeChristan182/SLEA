<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            // Generated ONLY after final approval (admin + academic/leadership approved)
            $table->string('user_code', 30)
                ->nullable()
                ->unique();

            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();

            $table->string('email', 100)->unique();
            $table->string('password', 191);
            $table->string('contact', 20)->nullable();

            $table->date('birth_date')->nullable();
            $table->string('profile_picture_path')->nullable();

            // Enum-style foreign keys
            $table->string('role', 32);
            $table->string('status', 32)->default('pending');

            // 🔹 Limited account flag (true = restricted access)
            $table->boolean('is_account_limited')->default(false);

            $table->timestamps();
            $table->softDeletes(); // 🔹 soft delete support

            // Foreign keys to enum tables
            $table->foreign('role')
                ->references('key')
                ->on('user_roles')
                ->cascadeOnUpdate();

            $table->foreign('status')
                ->references('key')
                ->on('user_statuses')
                ->cascadeOnUpdate();
        });

        Schema::create('password_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('previous_password_hash', 191);
            $table->timestamp('changed_at')->useCurrent();
            $table->string('changed_by')->nullable();  // 'self' or admin id/email
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 191)->nullable();
            $table->timestamps();
        });

        Schema::create('assessor_info', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('created_by_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // If you want to track temp passwords (optional)
            $table->string('temporary_password_hash', 191)->nullable();
            $table->boolean('must_change_password')->default(true);

            $table->dateTime('date_created')->useCurrent();
            $table->timestamps();
        });

        // If you later add an admin_privileges table,
        // create it in a separate migration or here with matching drop in down().
    }

    public function down(): void
    {
        // Drop child tables first to satisfy FK constraints

        Schema::dropIfExists('users');
    }
};
