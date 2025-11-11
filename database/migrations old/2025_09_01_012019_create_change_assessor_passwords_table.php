<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('change_assessor_passwords', function (Blueprint $table) {
            $table->id('change_pass_id');
            $table->string('email_address', 50);
            $table->string('old_password_hashed', 50);
            $table->string('new_password_hashed', 50);
            $table->dateTime('date_pass_changed');
            $table->timestamps();

            $table->foreign('email_address')->references('email_address')->on('assessor_accounts')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('change_assessor_passwords');
    }
};
