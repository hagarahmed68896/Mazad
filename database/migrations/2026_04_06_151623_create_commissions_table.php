<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['auctions', 'direct_purchase', 'tenders', 'services']);
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2);
            $table->boolean('is_percentage')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
