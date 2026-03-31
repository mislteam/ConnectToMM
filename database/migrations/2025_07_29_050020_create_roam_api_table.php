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
        Schema::create('roam_api', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->string('secret_key');
            $table->string('client_key');
            $table->string('api_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roam_api');
    }
    
};
