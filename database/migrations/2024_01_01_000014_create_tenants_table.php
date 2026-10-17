<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('enable_pharmacist')->default(true);
            $table->boolean('enable_dda')->default(true);
            $table->enum('payment_status', ['Pending', 'Paid'])->default('Pending');
            $table->decimal('subscription_price', 10, 2)->default(0);
            $table->date('license_expiry')->nullable();
            $table->string('tin')->nullable();
            $table->string('vat_reg')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('status')->default('Active');
            // Feature flags
            $table->boolean('feature_ai_sales')->default(true);
            $table->boolean('feature_whatsapp')->default(true);
            $table->boolean('feature_wallet')->default(true);
            $table->boolean('feature_data_transfer')->default(true);
            $table->boolean('feature_erp')->default(true);
            $table->boolean('feature_online_purchases')->default(true);
            $table->boolean('feature_vat')->default(true);
            $table->boolean('feature_backup')->default(true);
            $table->boolean('feature_advanced_pharmacy')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
