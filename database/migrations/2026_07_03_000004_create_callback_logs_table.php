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
        Schema::create('uab_callback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')
                ->nullable()
                ->constrained('uab_payment_transactions')
                ->nullOnDelete();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status', 20)->index();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uab_callback_logs');
    }
};
