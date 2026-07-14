<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE service_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending_payment'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE service_orders MODIFY COLUMN status ENUM('pending_payment', 'paid', 'under_review', 'completed', 'cancelled') NOT NULL DEFAULT 'pending_payment'");
        }
    }
};
