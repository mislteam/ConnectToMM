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
        Schema::create('joytel_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('joytel_order_num', 100)
                ->comment('Internal Joytel order number that return from Joytel as name order_code');
            $table->string('outer_order_id', 100)
                ->comment('Shared customer order reference to joytel with name order_tid');
            $table->string('product_name', 100)->comment('Joytel productName');
            $table->enum('service_type', ['esim', 'physical'])->default('esim');
            $table->enum('order_type', ['new', 'recharge']);
            $table->string('source_sn_code', 50)->nullable()->comment('Required for recharge order');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedInteger('total_price');
            $table->string('payment_method', 50)->default('bank_transfer')
                ->comment('Payment gateway used, e.g., bank_transfer, uab_payment');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('validity_days')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedTinyInteger('our_status')->default(0)
                ->comment('0=ORDER_START,1=PENDING_PAYMENT,2=PAID,3=API_PROCESSING,4=API_SUCCESS,5=API_FAILED,6=COMPLETED,7=CANCELLED,8=REFUNDED,9=ADMIN_CANCEL');
            $table->tinyInteger('joytel_status')->nullable()
                ->comment('eSIM:1=Submitted,2=Validated,3=WaitingDelivery,4=Delivered,0=Issue,-1=Cancelled/Recharge:0=Recharging,1=Success,2=Failed,3=Submitted,4=NeedRecharge');
            $table->boolean('renewal')->default(false);
            $table->string('main_order_num', 100)->nullable();
            $table->text('remark')->nullable();
            $table->boolean('is_send_email')->default(false);
            $table->dateTime('purchase_date')->nullable();
            $table->dateTime('callback_received_at')->nullable()->comment('Joytel callback received time');
            $table->json('raw_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('joytel_order_num', 'joytel_orders_order_num_unique');
            $table->index(['customer_id', 'created_at'], 'joytel_orders_customer_created_idx');
            $table->index('outer_order_id', 'joytel_orders_order_tid_idx');
            $table->index('joytel_order_num', 'joytel_orders_order_code_idx');
            $table->index('our_status', 'joytel_orders_status_idx');
            $table->index('joytel_status', 'joytel_orders_joytel_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joytel_orders');
    }
};
