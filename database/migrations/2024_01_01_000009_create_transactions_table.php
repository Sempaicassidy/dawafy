<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('patient_id')->nullable()->constrained('patients');
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['Cash', 'Mobile Money', 'Card', 'Insurance', 'Wallet']);
            $table->string('payment_reference')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->string('status')->default('Completed');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
