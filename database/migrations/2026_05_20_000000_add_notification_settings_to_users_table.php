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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('all_notifications')->default(true);
            $table->boolean('bid_notifications')->default(true);
            $table->boolean('bid_approaching_limit_notifications')->default(true);
            $table->integer('bid_approaching_limit_percentage')->default(70); // 70, 80, 90
            $table->boolean('bid_reached_limit_notifications')->default(true);
            $table->boolean('auction_update_notifications')->default(true);
            $table->boolean('favorite_auction_notifications')->default(true);
            $table->boolean('winning_bid_notifications')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'all_notifications',
                'bid_notifications',
                'bid_approaching_limit_notifications',
                'bid_approaching_limit_percentage',
                'bid_reached_limit_notifications',
                'auction_update_notifications',
                'favorite_auction_notifications',
                'winning_bid_notifications'
            ]);
        });
    }
};
