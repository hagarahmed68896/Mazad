<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['alert', 'notification']);
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('status', ['sent', 'scheduled', 'unsent'])->default('unsent');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
