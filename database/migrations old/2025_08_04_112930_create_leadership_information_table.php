<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leadership_information', function (Blueprint $table) {
            $table->id('leadership_id');
            $table->string('student_id', 20);
            $table->string('leadership_type', 255);
            $table->string('organization_name', 255);
            $table->string('position', 255);
            $table->string('term', 255);
            $table->string('issued_by', 255);
            $table->string('leadership_status', 255);
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('student_personal_information')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('leadership_information');
    }
};
