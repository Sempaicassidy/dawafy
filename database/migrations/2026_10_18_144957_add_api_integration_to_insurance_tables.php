<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_providers', 'api_environment')) {
                $table->string('api_environment')->default('sandbox')->after('status');
            }
            if (!Schema::hasColumn('insurance_providers', 'api_base_url')) {
                $table->string('api_base_url')->nullable()->after('api_environment');
            }
            if (!Schema::hasColumn('insurance_providers', 'facility_code')) {
                $table->string('facility_code')->nullable()->after('api_base_url');
            }
            if (!Schema::hasColumn('insurance_providers', 'api_key')) {
                $table->string('api_key')->nullable()->after('facility_code');
            }
            if (!Schema::hasColumn('insurance_providers', 'api_secret')) {
                $table->text('api_secret')->nullable()->after('api_key');
            }
            if (!Schema::hasColumn('insurance_providers', 'auth_token')) {
                $table->text('auth_token')->nullable()->after('api_secret');
            }
            if (!Schema::hasColumn('insurance_providers', 'auth_expires_at')) {
                $table->timestamp('auth_expires_at')->nullable()->after('auth_token');
            }
            if (!Schema::hasColumn('insurance_providers', 'is_integrated')) {
                $table->boolean('is_integrated')->default(true)->after('auth_expires_at');
            }
            if (!Schema::hasColumn('insurance_providers', 'settings')) {
                $table->json('settings')->nullable()->after('is_integrated');
            }
        });

        Schema::table('insurance_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_claims', 'member_name')) {
                $table->string('member_name')->nullable()->after('member_number');
            }
            if (!Schema::hasColumn('insurance_claims', 'scheme_name')) {
                $table->string('scheme_name')->nullable()->after('member_name');
            }
            if (!Schema::hasColumn('insurance_claims', 'authorization_code')) {
                $table->string('authorization_code')->nullable()->after('scheme_name');
            }
            if (!Schema::hasColumn('insurance_claims', 'referral_number')) {
                $table->string('referral_number')->nullable()->after('authorization_code');
            }
            if (!Schema::hasColumn('insurance_claims', 'verification_response')) {
                $table->json('verification_response')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_providers', function (Blueprint $table) {
            $table->dropColumn([
                'api_environment',
                'api_base_url',
                'facility_code',
                'api_key',
                'api_secret',
                'auth_token',
                'auth_expires_at',
                'is_integrated',
                'settings',
            ]);
        });

        Schema::table('insurance_claims', function (Blueprint $table) {
            $table->dropColumn([
                'member_name',
                'scheme_name',
                'authorization_code',
                'referral_number',
                'verification_response',
            ]);
        });
    }
};
