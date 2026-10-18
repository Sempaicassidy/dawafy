<?php

namespace App\Services\Insurance;

use App\Models\InsuranceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NhifGateway implements InsuranceGatewayInterface
{
    protected InsuranceProvider $provider;

    public function __construct(InsuranceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function verifyMember(string $cardNumber, array $params = []): array
    {
        $cardNumber = trim($cardNumber);

        // In Sandbox / Sample Mode: provide realistic Tanzanian NHIF data
        if ($this->provider->isSandbox()) {
            return $this->simulateNhifVerification($cardNumber, $params);
        }

        // In Production Mode: execute live API call to NHIF Web Service
        try {
            $endpoint = rtrim($this->provider->api_base_url, '/') . '/CardVerification';
            
            $payload = [
                'FacilityCode' => $this->provider->facility_code,
                'CardNo' => $cardNumber,
                'ReferralNo' => $params['referral_number'] ?? null,
                'Timestamp' => now()->toISOString(),
            ];

            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->provider->auth_token,
                    'X-API-Key' => $this->provider->api_key,
                    'X-Facility-Code' => $this->provider->facility_code,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'is_valid' => ($data['CardStatus'] ?? '') === 'Active',
                    'provider' => 'NHIF',
                    'member_name' => $data['FullName'] ?? $data['MemberName'] ?? 'Unknown Member',
                    'member_number' => $cardNumber,
                    'scheme' => $data['SchemeName'] ?? 'NHIF Standard Scheme',
                    'status' => $data['CardStatus'] ?? 'Active',
                    'expiry_date' => $data['ExpiryDate'] ?? null,
                    'authorization_code' => $data['AuthorizationNo'] ?? ('NHIF-AUTH-' . strtoupper(substr(md5($cardNumber . time()), 0, 8))),
                    'facility_code' => $this->provider->facility_code,
                    'message' => 'Uhakiki wa NHIF umekamilika kikamilifu.',
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'is_valid' => false,
                'provider' => 'NHIF',
                'message' => 'Kosa la API ya NHIF: ' . ($response->json('message') ?? 'Huduma ya NHIF haikupatikana kwa sasa.'),
                'status_code' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('NHIF Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'is_valid' => false,
                'provider' => 'NHIF',
                'message' => 'Hitilafu ya muunganisho wa NHIF: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        if ($this->provider->isSandbox()) {
            return [
                'success' => true,
                'mode' => 'sandbox',
                'latency_ms' => rand(45, 120),
                'message' => 'Muunganisho wa NHIF Sandbox Gateway uko Imara na Tayari (Simulated OK).',
                'facility_code' => $this->provider->facility_code,
            ];
        }

        try {
            $start = microtime(true);
            $endpoint = rtrim($this->provider->api_base_url, '/') . '/HealthCheck';
            $response = Http::timeout(6)
                ->withHeaders(['X-API-Key' => $this->provider->api_key])
                ->get($endpoint);

            $latency = round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->successful(),
                'mode' => 'production',
                'latency_ms' => $latency,
                'status_code' => $response->status(),
                'message' => $response->successful() 
                    ? "Muunganisho wa NHIF Live Gateway unafanya kazi kikamilifu ({$latency}ms)."
                    : "NHIF Seva imerudisha hitilafu ya nambari {$response->status()}.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mode' => 'production',
                'message' => 'Haikuweza kuunganishwa na seva ya NHIF: ' . $e->getMessage(),
            ];
        }
    }

    protected function simulateNhifVerification(string $cardNumber, array $params): array
    {
        // Check for test expired trigger
        if (str_contains(strtoupper($cardNumber), 'EXP') || $cardNumber === '00000000') {
            return [
                'success' => true,
                'is_valid' => false,
                'provider' => 'NHIF',
                'member_name' => 'John Doe (Expired Member)',
                'member_number' => $cardNumber,
                'scheme' => 'Bima ya Jamii',
                'status' => 'Expired',
                'expiry_date' => '2025-12-31',
                'authorization_code' => null,
                'message' => 'Kadi hii ya NHIF IMEKWISHA MUDA WAKE (Expired). Mwanachama hawezi kupatiwa dawa kwa bima hii.',
            ];
        }

        $names = [
            'Amani Juma Mwangi',
            'Salma Ramadhani Bakari',
            'Baraka Emmanuel Mushi',
            'Neema Josephat Lyimo',
            'Zainab Hassan Mwinyi',
            'Kassim Ally Mkali',
        ];

        // Deterministic pick based on card number digits
        $idx = abs(crc32($cardNumber)) % count($names);
        $memberName = !empty($params['patient_name']) ? $params['patient_name'] : $names[$idx];

        $schemes = ['Watumishi wa Umma (Civil Servants)', 'Bima ya Jamii', 'Najali Afya Plan', 'Toto Afya Scheme'];
        $scheme = $schemes[abs(crc32($cardNumber . 's')) % count($schemes)];

        $authCode = 'NHIF-AUTH-' . strtoupper(substr(md5($cardNumber . date('Ymd')), 0, 8));

        return [
            'success' => true,
            'is_valid' => true,
            'provider' => 'NHIF',
            'member_name' => $memberName,
            'member_number' => $cardNumber,
            'scheme' => $scheme,
            'status' => 'Active',
            'expiry_date' => date('Y-12-31', strtotime('+1 year')),
            'authorization_code' => $authCode,
            'facility_code' => $this->provider->facility_code ?? 'TZ-NHIF-90412',
            'co_payment' => 0,
            'message' => "Mwanachama {$memberName} amethibitishwa na NHIF. Kadi iko Hewani (Active). Namba ya Idhini: {$authCode}",
            'mode' => 'sandbox',
        ];
    }
}
