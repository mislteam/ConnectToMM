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
        Schema::create('roam_physical_skus', function (Blueprint $table) {
            $table->id();
            $table->string('dp_id');
            $table->string('sku_id');
            $table->string('country_name');
            $table->string('country_code');
            // Status: 0 = inactive, 1 = active
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roam_physical_skus');
    }
};
