<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roam_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete()
                ->comment('Customer who placed the order');

            $table->string('roam_order_num', 100)
                ->unique()
                ->comment('Internal Roam order number');

            $table->string('outer_order_id', 100)
                ->nullable()
                ->comment('External order id from our site, e.g. ROAM-20260506-000123');
            $table->string('sku_id')->comment('Roam SKU identifier');
            $table->unsignedBigInteger('price_id')->nullable()->comment('Price list record id');
            $table->string('api_code', 100)->comment('Upstream package unique identification code');

            $table->enum('service_type', ['esim', 'physical'])->comment('Order service type');
            $table->enum('order_type', ['new', 'recharge'])->comment('Order business type');

            $table->string('source_iccid', 50)->nullable()->comment('Source ICCID for recharge orders');
            $table->unsignedInteger('quantity')->comment('Order quantity');
            $table->decimal('unit_price', 10, 2)->nullable()->comment('Unit price');
            $table->decimal('total_price', 10, 2)->comment('Total order price');

            $table->unsignedInteger('daypass_days')->nullable()->comment('Day pass duration in days');
            $table->dateTime('start_date')->nullable()->comment('Service start date');
            $table->dateTime('end_date')->nullable()->comment('Service end date');

            $table->unsignedTinyInteger('our_status')->default(0)->comment('Internal order lifecycle:0=Order_Start,1=PENDING_PAYMENT,2=PAID,3=API_PROCESSING,4=API_SUCCESS,5=API_FAILED,6=COMPLETED,7=CANCELLED,8=REFUNDED');
            $table->unsignedTinyInteger('roam_status')->nullable()->comment('Roam upstream status: 0=Normal/Paid, 1=Unpaid, 2=Cancel, 3=Obsolete, 4=Partial Unsubscribe');
            $table->boolean('renewal')->default(false)->comment('Whether this is a renewal order');

            $table->string('main_order_num', 100)->nullable()->comment('Related main order number');
            $table->text('remark')->nullable()->comment('Internal remark');
            $table->boolean('is_send_email')->default(false)->comment('Whether email notification has been sent');
            $table->dateTime('purchase_date')->nullable()->comment('Purchase date returned by upstream');

            $table->json('raw_response')->nullable()->comment('Raw API response payload');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'created_at']);
            $table->index('sku_id');
            $table->index('price_id');
            $table->index('api_code');
            $table->index('our_status');
            $table->index('roam_status');
            $table->index(['service_type', 'order_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roam_orders');
    }
};
