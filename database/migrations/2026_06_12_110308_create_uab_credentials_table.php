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
        Schema::create('uab_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_setting_id')->constrained('payment_setting')->onDelete('cascade');
            $table->string('channel');
            $table->string('payment_methods')->nullable();
            $table->string('merchant_user_id');
            $table->string('api_url');
            $table->string('base_url')->nullable();
            $table->string('client_id')->nullable();
            $table->string('access_key');
            $table->string('secret_key');
            $table->string('client_secret');
            $table->string('merchant_id')->nullable();
            $table->string('ins_id')->nullable();
            $table->string('notify_url')->nullable();
            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal_code', 16)->nullable();
            $table->string('billing_state', 64)->nullable();
            $table->string('billing_country', 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uab_credentials');
    }
};
