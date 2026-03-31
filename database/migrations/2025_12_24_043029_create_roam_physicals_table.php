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
        Schema::create('roam_physicals', function (Blueprint $table) {
            $table->id();
            $table->string('dp_id');
            $table->string('sku_id');
            $table->json('packages');
            $table->json('support_country');
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roam_physicals');
    }
};
