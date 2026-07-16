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
        Schema::create('joytel_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('joytel_order_id')->constrained('joytel_orders')->cascadeOnDelete();
            $table->string('product_code', 100)->nullable();
            $table->string('sn_code', 50)->nullable()->comment('Joytel serial number');
            $table->string('sn_pin', 100)->nullable()->comment('Coupon code for QR redeem');
            $table->string('cid', 50)->nullable()->comment('eSIM profile CID');
            $table->tinyInteger('qrcode_type')->nullable()->comment('0=url,1=lpa string');
            $table->text('qrcode')->nullable()->comment('QR URL or LPA string');
            $table->string('pin1', 20)->nullable();
            $table->string('pin2', 20)->nullable();
            $table->string('puk1', 20)->nullable();
            $table->string('puk2', 20)->nullable();
            $table->string('sale_plan_name')->nullable();
            $table->integer('sale_plan_days')->nullable();
            $table->date('product_expire_date')->nullable();
            $table->tinyInteger('esim_status')->nullable()->comment('0=Unknown/1=Activated/2=Expired');
            $table->string('profile_state', 50)->nullable()
                ->comment('AVAILABLE/ALLOCATED/LINKED/CONFIRMED/RELEASED/DOWNLOADED/INSTALLED/ENABLED/DISABLED/DELETED');
            $table->string('eid', 128)->nullable();
            $table->unsignedBigInteger('used_bytes')->nullable();
            $table->unsignedBigInteger('total_usage_bytes')->nullable();
            $table->dateTime('activation_time')->nullable();
            $table->dateTime('expiration_time')->nullable();
            $table->json('raw_usage_data')->nullable();
            $table->json('raw_callback_data')->nullable();
            $table->timestamps();

            $table->index('joytel_order_id', 'joytel_order_items_order_idx');
            $table->index('product_code', 'joytel_order_items_product_code_idx');
            $table->index('sn_code', 'joytel_order_items_sn_code_idx');
            $table->index('sn_pin', 'joytel_order_items_sn_pin_idx');
            $table->index('cid', 'joytel_order_items_cid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joytel_order_items');
    }
};
