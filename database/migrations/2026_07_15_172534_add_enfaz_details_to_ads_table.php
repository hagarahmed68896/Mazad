<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('enfaz_license_number')->nullable()->after('is_enfaz');
            $table->string('enfaz_brochure')->nullable()->after('enfaz_license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['enfaz_license_number', 'enfaz_brochure']);
        });
    }
};
