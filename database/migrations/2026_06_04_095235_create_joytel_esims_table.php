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
        Schema::create('joytel_esims', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('data')->nullable();
            $table->enum('traffic_type', ['daily','unlimited','total'])->nullable();
            $table->string('service_day')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('code')->unique();
            $table->json('coverage');
            $table->string('type');
            $table->longText('product_description');
	        $table->longText('memo')->nullable();
            $table->string('activation_type')->nullable();
	        $table->string('provider');
	        $table->string('network');
	        $table->string('hotspot');
	        $table->string('recharge');
            $table->string('photo')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joytel_esims');
    }
};
