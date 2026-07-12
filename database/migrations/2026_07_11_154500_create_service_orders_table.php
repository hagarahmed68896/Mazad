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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('ad_id')->nullable()->constrained('ads')->nullOnDelete(); // for services stored in ads table
            $table->decimal('price', 12, 2);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending_payment', 'paid', 'under_review', 'completed', 'cancelled'])->default('pending_payment');
            $table->string('payment_method')->nullable();
            $table->text('report_text')->nullable();
            $table->string('report_file')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
