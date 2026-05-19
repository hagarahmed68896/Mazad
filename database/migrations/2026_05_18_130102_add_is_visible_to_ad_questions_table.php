<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_questions', function (Blueprint $table) {
            $table->boolean('is_visible')->default(false)->after('answered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_questions', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
