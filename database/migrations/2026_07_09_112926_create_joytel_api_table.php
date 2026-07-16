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
        Schema::create('joytel_api', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code');
            $table->string('customer_auth');
            $table->string('api_url');
            $table->string('rsp_appid')->nullable();
            $table->string('rsp_secret')->nullable();
            $table->string('rsp_baseurl')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joytel_api');
    }
};
