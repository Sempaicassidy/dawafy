<?php

namespace App\Services\Insurance;

use App\Models\InsuranceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JubileeGateway implements InsuranceGatewayInterface
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
            return $this->simulateJubileeVerification($cardNumber, $params);
        }

        try {
            $endpoint = rtrim($this->provider->api_base_url, '/') . '/members/verify';

            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->provider->auth_token,
                    'X-Client-ID' => $this->provider->api_key,
                    'X-Clinic-Code' => $this->provider->facility_code,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, [
                    'MemberPolicyNo' => $cardNumber,
                    'ClinicCode' => $this->provider->facility_code,
                    'RequestDate' => now()->format('Y-m-d H:i:s'),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'is_valid' => ($data['Status'] ?? '') === 'Active',
                    'provider' => 'JUBILEE',
                    'member_name' => $data['MemberName'] ?? 'Jubilee Member',
                    'member_number' => $cardNumber,
                    'scheme' => $data['PolicyPlan'] ?? 'Jubilee Corporate Health',
                    'status' => $data['Status'] ?? 'Active',
                    'authorization_code' => $data['PreAuthCode'] ?? ('JUB-AUTH-' . strtoupper(substr(md5($cardNumber . time()), 0, 8))),
                    'benefit_balance' => $data['AvailableBalance'] ?? 'Unlimited/Full Cover',
                    'message' => 'Jubilee Smart Card verification successful.',
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'is_valid' => false,
                'provider' => 'JUBILEE',
                'message' => 'Kosa la API ya Jubilee: ' . ($response->json('message') ?? 'Kadi haikupatikana au imezuiwa.'),
            ];
        } catch (\Throwable $e) {
            Log::error('Jubilee Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'is_valid' => false,
                'provider' => 'JUBILEE',
                'message' => 'Hitilafu ya muunganisho wa Jubilee: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        if ($this->provider->isSandbox()) {
            return [
                'success' => true,
                'mode' => 'sandbox',
                'latency_ms' => rand(50, 110),
                'message' => 'Muunganisho wa Jubilee Smart EDI Sandbox uko Imara na Tayari.',
                'facility_code' => $this->provider->facility_code,
            ];
        }

        try {
            $start = microtime(true);
            $endpoint = rtrim($this->provider->api_base_url, '/') . '/ping';
            $response = Http::timeout(6)
                ->withHeaders(['X-Client-ID' => $this->provider->api_key])
                ->get($endpoint);

            $latency = round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->successful(),
                'mode' => 'production',
                'latency_ms' => $latency,
                'message' => $response->successful()
                    ? "Muunganisho wa Jubilee Live Gateway unafanya kazi kikamilifu ({$latency}ms)."
                    : "Jubilee Gateway imerudisha hitilafu ya nambari {$response->status()}.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mode' => 'production',
                'message' => 'Haikuweza kuunganishwa na seva ya Jubilee: ' . $e->getMessage(),
            ];
        }
    }

    protected function simulateJubileeVerification(string $cardNumber, array $params): array
    {
        if (str_contains(strtoupper($cardNumber), 'EXP') || $cardNumber === '00000000') {
            return [
                'success' => true,
                'is_valid' => false,
                'provider' => 'JUBILEE',
                'member_name' => 'Michael K. Temu (Suspended)',
                'member_number' => $cardNumber,
                'scheme' => 'Jubilee Corporate Silver',
                'status' => 'Suspended',
                'authorization_code' => null,
                'message' => 'Kadi ya Jubilee IMESITISHWA (Policy Suspended). Tafadhali shauri mgonjwa awasiliane na bima yake.',
            ];
        }

        $names = [
            'Geoffrey K. Mushi',
            'Beatrice Daniel Mtaki',
            'Emmanuel Peter Minja',
            'Grace Lucas Shirima',
        ];

        $idx = abs(crc32($cardNumber)) % count($names);
        $memberName = !empty($params['patient_name']) ? $params['patient_name'] : $names[$idx];

        $authCode = 'JUB-EDI-' . strtoupper(substr(md5($cardNumber . date('Ymd')), 0, 8));

        return [
            'success' => true,
            'is_valid' => true,
            'provider' => 'JUBILEE',
            'member_name' => $memberName,
            'member_number' => $cardNumber,
            'scheme' => 'Jubilee Corporate Platinum Plus',
            'status' => 'Active',
            'expiry_date' => date('Y-12-31', strtotime('+1 year')),
            'authorization_code' => $authCode,
            'facility_code' => $this->provider->facility_code ?? 'JUB-TZ-CLINIC-409',
            'benefit_balance' => 'TZS 3,500,000 Outpatient Pharmacy Cap',
            'co_payment' => 0,
            'message' => "Mwanachama {$memberName} amethibitishwa na Jubilee Insurance. Kadi iko Hewani (Active). Namba ya Idhini: {$authCode}",
            'mode' => 'sandbox',
        ];
    }
}
