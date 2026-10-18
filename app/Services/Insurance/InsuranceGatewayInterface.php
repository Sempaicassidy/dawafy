<?php

namespace App\Services\Insurance;

use App\Models\InsuranceProvider;

interface InsuranceGatewayInterface
{
    /**
     * Verify insurance member card in real-time or sandbox.
     *
     * @param string $cardNumber
     * @param array $params Additional context (e.g. patient_name, referral_number)
     * @return array
     */
    public function verifyMember(string $cardNumber, array $params = []): array;

    /**
     * Test connection to the provider's API.
     *
     * @return array
     */
    public function testConnection(): array;
}
