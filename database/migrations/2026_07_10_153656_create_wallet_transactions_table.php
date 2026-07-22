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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('customer_wallets')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('type', ['credit', 'debit']);
            $table->enum('reference_type', ['topup', 'package_purchase']);
            $table->unsignedBigInteger('balance_after');
            $table->string('transaction_state')->default('pending')->comment('pending,  approved');
            $table->string('payment_slip')->nullable();
            $table->timestamps();
            $table->index('reference_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
