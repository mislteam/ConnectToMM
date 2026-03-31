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
        Schema::create('joytels', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('product_name');
            $table->longText('usage_location');
            $table->string('supplier');
            $table->string('product_type');
            $table->longText('plan');
            $table->string('photo')->nullable();
            $table->string('activation_policy')->nullable();
            $table->string('delivery_time')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joytels');
    }
};
