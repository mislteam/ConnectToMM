<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('profile_image')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('auth_provider')->default('email');
            $table->string('provider_user_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = inactive, 1 = active');
            $table->string('role')->default('customer');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique('email', 'email');
            $table->unique('phone', 'phone');
            $table->unique('email', 'customers_email_unique');
            $table->unique('phone', 'customers_phone_unique');
            $table->unique(['auth_provider', 'provider_user_id']);
            $table->index('auth_provider', 'customers_auth_provider_index');
            $table->index('status', 'customers_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
