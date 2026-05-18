<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roam_order_items', function (Blueprint $table) {
            $table->id();

            $table->string('data', 50)->comment('Flow data');
            $table->foreignId('roam_order_id')
                ->constrained('roam_orders')
                ->cascadeOnDelete()
                ->comment('Parent roam order id');

            $table->string('iccid', 50)->comment('SIM ICCID');
            $table->string('mobile_number', 50)->nullable()->comment('Assigned mobile number');
            $table->string('activation_code')->nullable()->comment('Activation code for eSIM');
            $table->string('sm_dp_address')->nullable()->comment('SM-DP+ address');
            $table->string('apn', 50)->nullable()->comment('Access Point Name');
            $table->string('dp_id', 50)->nullable()->comment('DP identifier');
            $table->integer('validity')->nullable()->comment('Plan validity in days');
            $table->decimal('used_mb', 10, 2)->nullable()->comment('Used data in MB');
            $table->dateTime('activate_before')->nullable()->comment('Activation deadline');
            $table->dateTime('start_date')->nullable()->comment('Order item service start date');
            $table->dateTime('end_date')->nullable()->comment('Order item service end date');
            $table->text('pdf_url')->nullable()->comment('PDF download URL');
            $table->json('raw_card_data')->nullable()->comment('Raw card / profile data payload');

            $table->timestamps();

            $table->index('roam_order_id');
            $table->index('iccid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roam_order_items');
    }
};
