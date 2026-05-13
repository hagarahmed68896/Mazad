<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add bid_increment to ads table
        Schema::table('ads', function (Blueprint $table) {
            $table->decimal('bid_increment', 10, 2)->default(500)->after('price');
        });

        // Add max_limit to bids table (the ceiling the bidder sets)
        Schema::table('bids', function (Blueprint $table) {
            $table->decimal('max_limit', 12, 2)->nullable()->after('amount');
            $table->boolean('is_auto')->default(false)->after('max_limit'); // was this bid placed by the system automatically?
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('bid_increment');
        });
        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn(['max_limit', 'is_auto']);
        });
    }
};
