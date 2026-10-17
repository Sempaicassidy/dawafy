<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add chronic_condition and outstanding_debt to patients table if not exists
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'chronic_condition')) {
                $table->string('chronic_condition')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('patients', 'outstanding_debt')) {
                $table->decimal('outstanding_debt', 12, 2)->default(0)->after('chronic_condition');
            }
        });

        // 2. Add phone to users table if not exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('full_name');
            }
        });

        // 3. Add buying_price to drugs table if not exists
        Schema::table('drugs', function (Blueprint $table) {
            if (!Schema::hasColumn('drugs', 'buying_price')) {
                $table->decimal('buying_price', 10, 2)->nullable()->default(0)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'chronic_condition')) {
                $table->dropColumn('chronic_condition');
            }
            if (Schema::hasColumn('patients', 'outstanding_debt')) {
                $table->dropColumn('outstanding_debt');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });

        Schema::table('drugs', function (Blueprint $table) {
            if (Schema::hasColumn('drugs', 'buying_price')) {
                $table->dropColumn('buying_price');
            }
        });
    }
};
