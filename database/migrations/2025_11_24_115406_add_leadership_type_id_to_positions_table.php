<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedBigInteger('leadership_type_id')->nullable()->after('id');
            $table->foreign('leadership_type_id')
                ->references('id')
                ->on('leadership_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['leadership_type_id']);
            $table->dropColumn('leadership_type_id');
        });
    }
};
