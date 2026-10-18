<?php

namespace App\Services\Insurance;

use App\Models\InsuranceProvider;
use InvalidArgumentException;

class InsuranceGatewayManager
{
    /**
     * Resolve the corresponding gateway for an insurance provider.
     */
    public static function gatewayFor(InsuranceProvider|string|int $provider): InsuranceGatewayInterface
    {
        if (!$provider instanceof InsuranceProvider) {
            $model = is_numeric($provider) 
                ? InsuranceProvider::find($provider) 
                : InsuranceProvider::where('code', strtoupper($provider))->first();

            if (!$model) {
                // Default fallback if not found in db: create transient NHIF
                $model = new InsuranceProvider([
                    'name' => 'NHIF Tanzania',
                    'code' => 'NHIF',
                    'api_environment' => 'sandbox',
                    'facility_code' => 'TZ-NHIF-90412',
                ]);
            }
            $provider = $model;
        }

        $code = strtoupper($provider->code ?? $provider->name);

        if (str_contains($code, 'NHIF')) {
            return new NhifGateway($provider);
        }

        if (str_contains($code, 'JUBILEE')) {
            return new JubileeGateway($provider);
        }

        if (str_contains($code, 'STRATEGIS') || str_contains($code, 'STRATEG')) {
            return new StrategisGateway($provider);
        }

        // Generic fallback to NHIF gateway architecture
        return new NhifGateway($provider);
    }
}
