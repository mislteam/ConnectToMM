<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joytel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique()->comment('Our internal order number');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('service_type', 20)->comment('esim or physical');
            $table->string('channel_type', 30)->nullable()->comment('joytel warehouse order or recharge flow');

            $table->string('customer_code', 64)->nullable()->comment('Joytel assigned customer code');
            $table->string('order_tid', 80)->nullable()->comment('Customer-side order id sent to Joytel');
            $table->string('joytel_order_code', 80)->nullable()->comment('Joytel generated order code / recharge code');
            $table->string('warehouse', 80)->nullable();
            $table->unsignedTinyInteger('submit_type')->nullable()->comment('Joytel submit type, for example 3 for eSIM snPin orders');
            $table->unsignedTinyInteger('reply_type')->nullable()->comment('0 = email, 1 = callback/webhook');

            $table->string('receive_name', 120)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 191)->nullable();
            $table->text('remark')->nullable();

            $table->string('request_signature', 128)->nullable()->comment('autoGraph / signature used for request verification');

            $table->unsignedTinyInteger('status')->default(0)->comment('0 = draft, 1 = submitted, 2 = processing, 3 = completed, 4 = failed, 5 = cancelled');
            $table->string('remote_status_code', 32)->nullable()->comment('Joytel status code or state code returned by query/callback');
            $table->string('remote_status_label', 120)->nullable()->comment('Joytel status description returned by query/callback');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->json('query_payload')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['service_type', 'status']);
            $table->index(['customer_id', 'created_at']);
            $table->index('order_tid');
            $table->index('joytel_order_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joytel_orders');
    }
};
