<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 50)->unique();
                $table->string('display_name', 100);
                $table->string('icon', 50)->default('bi-cash');
                $table->string('color', 50)->default('primary');
                $table->text('description')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_locked')->default(false); // e.g. Cash cannot be disabled
                $table->integer('sort_order')->default(0);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            // Seed default payment methods
            $now = now();
            DB::table('payment_methods')->insert([
                [
                    'code' => 'cash',
                    'name' => 'Cash',
                    'display_name' => 'Pesa Taslimu (Cash)',
                    'icon' => 'bi-cash-stack',
                    'color' => 'success',
                    'description' => 'Malipo ya pesa taslimu mkononi. Hii ni njia ya msingi ya mfumo na haiwezi kuzimwa kamwe (Locked).',
                    'is_enabled' => true,
                    'is_locked' => true,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'mobile_money',
                    'name' => 'Mobile Money',
                    'display_name' => 'Mobile Money (M-Pesa / Tigo Pesa / Airtel / HaloPesa)',
                    'icon' => 'bi-phone',
                    'color' => 'info',
                    'description' => 'Malipo ya mitandao ya simu za mkononi nchini Tanzania (M-Pesa, Tigo Pesa, Airtel Money, HaloPesa).',
                    'is_enabled' => true,
                    'is_locked' => false,
                    'sort_order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'card',
                    'name' => 'Card',
                    'display_name' => 'Kadi ya Benki (POS Terminal / Visa / Mastercard)',
                    'icon' => 'bi-credit-card',
                    'color' => 'primary',
                    'description' => 'Malipo ya kadi za kielektroniki za kibenki kupitia mashine ya benki ya POS (Visa, Mastercard, UnionPay).',
                    'is_enabled' => true,
                    'is_locked' => false,
                    'sort_order' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'insurance',
                    'name' => 'Insurance',
                    'display_name' => 'Bima ya Afya (NHIF / Jubilee / Strategis)',
                    'icon' => 'bi-shield-plus',
                    'color' => 'warning',
                    'description' => 'Malipo ya kadi za bima za afya zilizohakikiwa mtandaoni kwa NHIF, Jubilee au Strategis.',
                    'is_enabled' => true,
                    'is_locked' => false,
                    'sort_order' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'wallet',
                    'name' => 'Wallet',
                    'display_name' => 'Customer Wallet (Akaunti ya Mteja)',
                    'icon' => 'bi-wallet2',
                    'color' => 'secondary',
                    'description' => 'Malipo kupitia salio la mteja lililopo kwenye pochi yake (Customer Prepaid Wallet).',
                    'is_enabled' => true,
                    'is_locked' => false,
                    'sort_order' => 5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
