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
            $table->foreignId('payment_setting_id')->constrained('payment_settings')->onDelete('cascade');
            $table->string('channel');
            $table->string('merchant_user_id');
            $table->string('api_url');
            $table->string('access_key');
            $table->string('secret_key');
            $table->string('client_secret');
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
