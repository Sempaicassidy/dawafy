<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add workflow columns to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'status')) {
                $table->string('status', 30)->default('dispensed')->after('payment_method'); // pending_payment, paid, dispensed, cancelled
            }
            if (!Schema::hasColumn('transactions', 'pharmacist_id')) {
                $table->unsignedBigInteger('pharmacist_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('transactions', 'cashier_id')) {
                $table->unsignedBigInteger('cashier_id')->nullable()->after('pharmacist_id');
            }
            if (!Schema::hasColumn('transactions', 'dispensed_at')) {
                $table->timestamp('dispensed_at')->nullable()->after('status');
            }
        });

        // 2. Create stock_refill_requests table
        if (!Schema::hasTable('stock_refill_requests')) {
            Schema::create('stock_refill_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('pharmacist_id');
                $table->unsignedBigInteger('drug_id');
                $table->integer('requested_quantity');
                $table->integer('approved_quantity')->nullable();
                $table->string('status', 30)->default('pending'); // pending, approved, rejected
                $table->string('manager_decision', 30)->nullable(); // exact, below, above
                $table->unsignedBigInteger('manager_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('pharmacist_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
                $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'dispensed_at')) {
                $table->dropColumn('dispensed_at');
            }
            if (Schema::hasColumn('transactions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('transactions', 'cashier_id')) {
                $table->dropColumn('cashier_id');
            }
            if (Schema::hasColumn('transactions', 'pharmacist_id')) {
                $table->dropColumn('pharmacist_id');
            }
        });

        Schema::dropIfExists('stock_refill_requests');
    }
};
