<?php

namespace App\Services\Insurance;

use App\Models\InsuranceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StrategisGateway implements InsuranceGatewayInterface
{
    protected InsuranceProvider $provider;

    public function __construct(InsuranceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function verifyMember(string $cardNumber, array $params = []): array
    {
        $cardNumber = trim($cardNumber);

        if ($this->provider->isSandbox()) {
            return $this->simulateStrategisVerification($cardNumber, $params);
        }

        try {
            $endpoint = rtrim($this->provider->api_base_url, '/') . '/member/eligibility';

            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->provider->auth_token,
                    'X-Provider-Code' => $this->provider->facility_code,
                    'X-API-Secret' => $this->provider->api_secret,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, [
                    'MemberNumber' => $cardNumber,
                    'ProviderCode' => $this->provider->facility_code,
                    'BenefitType' => 'Pharmacy',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'is_valid' => ($data['EligibilityStatus'] ?? '') === 'Eligible',
                    'provider' => 'STRATEGIS',
                    'member_name' => $data['MemberName'] ?? 'Strategis Member',
                    'member_number' => $cardNumber,
                    'scheme' => $data['CorporateName'] ?? 'Strategis Corporate Plan',
                    'status' => $data['EligibilityStatus'] ?? 'Active',
                    'authorization_code' => $data['AuthNumber'] ?? ('STRAT-AUTH-' . strtoupper(substr(md5($cardNumber . time()), 0, 8))),
                    'co_payment' => $data['CoPay'] ?? 0,
                    'message' => 'Uhakiki wa Strategis umekamilika kikamilifu.',
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'is_valid' => false,
                'provider' => 'STRATEGIS',
                'message' => 'Kosa la API ya Strategis: ' . ($response->json('message') ?? 'Mwanachama huyu hana sifa au amefungiwa bima.'),
            ];
        } catch (\Throwable $e) {
            Log::error('Strategis Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'is_valid' => false,
                'provider' => 'STRATEGIS',
                'message' => 'Hitilafu ya muunganisho wa Strategis: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        if ($this->provider->isSandbox()) {
            return [
                'success' => true,
                'mode' => 'sandbox',
                'latency_ms' => rand(40, 95),
                'message' => 'Muunganisho wa Strategis Insurance Sandbox Gateway uko Imara na Tayari.',
                'facility_code' => $this->provider->facility_code,
            ];
        }

        try {
            $start = microtime(true);
            $endpoint = rtrim($this->provider->api_base_url, '/') . '/status';
            $response = Http::timeout(6)
                ->withHeaders(['X-Provider-Code' => $this->provider->facility_code])
                ->get($endpoint);

            $latency = round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->successful(),
                'mode' => 'production',
                'latency_ms' => $latency,
                'message' => $response->successful()
                    ? "Muunganisho wa Strategis Live Gateway unafanya kazi kikamilifu ({$latency}ms)."
                    : "Strategis Gateway imerudisha hitilafu ya nambari {$response->status()}.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mode' => 'production',
                'message' => 'Haikuweza kuunganishwa na seva ya Strategis: ' . $e->getMessage(),
            ];
        }
    }

    protected function simulateStrategisVerification(string $cardNumber, array $params): array
    {
        if (str_contains(strtoupper($cardNumber), 'EXP') || $cardNumber === '00000000') {
            return [
                'success' => true,
                'is_valid' => false,
                'provider' => 'STRATEGIS',
                'member_name' => 'Rashid Bakari (Inactive)',
                'member_number' => $cardNumber,
                'scheme' => 'Strategis Retail Essential',
                'status' => 'Inactive',
                'authorization_code' => null,
                'message' => 'Kadi ya Strategis HAIKO HEWANI (Account Inactive/Defaulted).',
            ];
        }

        $names = [
            'Fatma Ali Rashid',
            'Ibrahim Said Mwinjuma',
            'Devotha Charles Mrema',
            'Joshua Wilson Kwayu',
        ];

        $idx = abs(crc32($cardNumber)) % count($names);
        $memberName = !empty($params['patient_name']) ? $params['patient_name'] : $names[$idx];

        $authCode = 'STRAT-AUTH-' . strtoupper(substr(md5($cardNumber . date('Ymd')), 0, 8));

        return [
            'success' => true,
            'is_valid' => true,
            'provider' => 'STRATEGIS',
            'member_name' => $memberName,
            'member_number' => $cardNumber,
            'scheme' => 'Strategis Corporate Gold Cover',
            'status' => 'Active',
            'expiry_date' => date('Y-12-31', strtotime('+1 year')),
            'authorization_code' => $authCode,
            'facility_code' => $this->provider->facility_code ?? 'STRAT-PRV-102',
            'co_payment' => 0,
            'message' => "Mwanachama {$memberName} amethibitishwa na Strategis Insurance. Kadi iko Hewani (Active). Namba ya Idhini: {$authCode}",
            'mode' => 'sandbox',
        ];
    }
}
