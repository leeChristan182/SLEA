<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leadership_types', function (Blueprint $table) {
            // meta for council types
            $table->string('domain', 20)->nullable()->after('name'); // campus | college | lgu
            $table->string('scope', 20)->nullable()->after('domain'); // institutional | local
            $table->boolean('requires_org')->default(false)->after('scope'); // true for CCO
        });
    }

    public function down(): void
    {
        Schema::table('leadership_types', function (Blueprint $table) {
            $table->dropColumn(['domain', 'scope', 'requires_org']);
        });
    }
};
