<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('auction_type')->nullable()->after('type');
            $table->decimal('shipping_price', 12, 2)->default(0)->after('price');
            $table->string('shipping_type')->nullable()->after('shipping_price');
            $table->string('youtube_link')->nullable()->after('description');
            $table->dateTime('start_date')->nullable()->after('end_date');
            $table->integer('award_duration')->nullable()->after('start_date');
            $table->integer('quantity')->default(1)->after('award_duration');
            $table->decimal('min_sale_price', 12, 2)->default(0)->after('price');
            $table->boolean('allow_bidding')->default(false)->after('type');
            $table->boolean('allow_negotiation')->default(false)->after('allow_bidding');
            $table->decimal('buy_now_price', 12, 2)->default(0)->after('price');
            $table->dateTime('inspection_date')->nullable()->after('inspection_info');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn([
                'auction_type', 'shipping_price', 'shipping_type', 'youtube_link', 
                'start_date', 'award_duration', 'quantity', 'min_sale_price', 
                'allow_bidding', 'allow_negotiation', 'buy_now_price', 'inspection_date'
            ]);
        });
    }
};
