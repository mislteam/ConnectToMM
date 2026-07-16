<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joytel_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('joytel_order_id')->constrained('joytel_orders')->cascadeOnDelete();

            $table->unsignedInteger('line_no')->default(1);
            $table->string('product_code', 80);
            $table->string('product_name', 191)->nullable();
            $table->unsignedInteger('quantity')->default(1);

            $table->string('service_day', 40)->nullable();
            $table->unsignedInteger('days')->nullable()->comment('Physical SIM recharge days');

            $table->decimal('unit_price', 18, 2)->nullable();
            $table->decimal('total_price', 18, 2)->nullable();
            $table->decimal('line_total', 18, 2)->nullable();

            $table->string('sn_code', 40)->nullable();
            $table->string('sn_pin', 64)->nullable()->comment('Coupon / snPin');
            $table->string('coupon', 64)->nullable()->comment('RSP coupon, same as snPin');

            $table->unsignedTinyInteger('qrcode_type')->nullable()->comment('0 = QR image URL, 1 = QR content text');
            $table->text('qrcode')->nullable();

            $table->string('cid', 40)->nullable();
            $table->string('eid', 128)->nullable();
            $table->string('profile_type', 128)->nullable();
            $table->string('sale_plan_name', 191)->nullable();
            $table->unsignedSmallInteger('sale_plan_days')->nullable();
            $table->string('pin1', 16)->nullable();
            $table->string('pin2', 16)->nullable();
            $table->string('puk1', 32)->nullable();
            $table->string('puk2', 32)->nullable();

            $table->string('rsp_order_id', 80)->nullable();
            $table->string('rsp_tid', 80)->nullable();
            $table->string('outbound_code', 80)->comment('JoyTel warehouse deliver code returned in order query');
            $table->string('product_expire_date', 20)->nullable();

            $table->unsignedTinyInteger('status')->default(0)->comment('0 = pending, 1 = success, 2 = failed, 3 = submitted, 4 = need_recharge');

            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('callback_payload')->nullable();

            $table->timestamps();

            $table->unique(['joytel_order_id', 'line_no']);
            $table->index(['product_code', 'status']);
            $table->index('sn_code');
            $table->index('sn_pin');
            $table->index('coupon');
            $table->index('cid');
            $table->index('outbound_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joytel_order_items');
    }
};
