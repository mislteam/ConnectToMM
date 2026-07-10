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
        Schema::create('uab_payment_api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')
                ->nullable()
                ->constrained('uab_payment_transactions')
                ->nullOnDelete();
            $table->string('endpoint');
            $table->string('http_method', 20);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('execution_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uab_payment_api_logs');
    }
};
