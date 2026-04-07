<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['electronic_auction', 'hybrid_auction', 'direct_purchase', 'tender'])->default('direct_purchase');
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('bids_count')->default(0);
            $table->enum('status', ['active', 'upcoming', 'finished'])->default('active');
            $table->string('bid_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
