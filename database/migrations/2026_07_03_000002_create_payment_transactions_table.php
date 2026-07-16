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
        Schema::create('uab_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('transaction_id')->nullable()->index();
            $table->string('merchant_reference')->index();
            $table->string('invoice_no')->index();
            $table->string('order_no')->index();
            $table->decimal('amount', 16, 2);
            $table->string('currency', 3);
            $table->string('payment_method', 50);
            $table->string('selected_payment_method', 50)->nullable()->after('payment_method');
            $table->string('selected_payment_type', 50)->nullable()->after('selected_payment_method');
            $table->string('selected_card_type', 50)->nullable()->after('selected_payment_type');
            $table->string('status', 20)->index();
            $table->json('provider_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uab_payment_transactions');
    }
};
