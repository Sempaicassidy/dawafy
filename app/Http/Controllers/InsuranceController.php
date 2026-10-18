<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use App\Models\InsuranceClaim;
use App\Services\Insurance\InsuranceGatewayManager;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $providers = InsuranceProvider::all();
        $claims = InsuranceClaim::with(['transaction', 'insuranceProvider'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('insurance.index', compact('providers', 'claims'));
    }

    public function createProvider()
    {
        return view('insurance.create_provider');
    }

    public function storeProvider(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 10));
        }

        InsuranceProvider::create($validated);

        return redirect()->route('insurance.index')->with('success', 'Insurance provider created successfully');
    }

    /**
     * Update Provider API Credentials and Environment
     */
    public function updateSettings(Request $request, $id)
    {
        $provider = InsuranceProvider::findOrFail($id);

        $validated = $request->validate([
            'facility_code' => 'nullable|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string',
            'api_base_url' => 'nullable|string|max:255',
            'api_environment' => 'required|in:sandbox,production',
            'is_integrated' => 'nullable|boolean',
        ]);

        $validated['is_integrated'] = $request->has('is_integrated');

        $provider->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Mipangilio ya API ya {$provider->name} imesasishwa kikamilifu!",
                'provider' => $provider,
            ]);
        }

        return redirect()->route('insurance.index')->with('success', "Mipangilio ya API ya {$provider->name} imesasishwa kikamilifu!");
    }

    /**
     * Real-time verification endpoint for POS and cashier
     */
    public function verifyCard(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable',
            'provider_code' => 'nullable',
            'card_number' => 'required|string|min:3|max:100',
            'patient_name' => 'nullable|string|max:200',
            'referral_number' => 'nullable|string|max:100',
        ]);

        $provider = null;
        if (!empty($validated['provider_id'])) {
            $provider = InsuranceProvider::find($validated['provider_id']);
        }

        if (!$provider && !empty($validated['provider_code'])) {
            $provider = InsuranceProvider::where('code', strtoupper($validated['provider_code']))->first();
        }

        if (!$provider) {
            $provider = InsuranceProvider::where('code', 'NHIF')->first() ?? InsuranceProvider::first();
        }

        $gateway = InsuranceGatewayManager::gatewayFor($provider);
        $result = $gateway->verifyMember($validated['card_number'], [
            'patient_name' => $validated['patient_name'] ?? null,
            'referral_number' => $validated['referral_number'] ?? null,
        ]);

        $result['provider_id'] = $provider?->id;
        $result['provider_name'] = $provider?->name ?? 'NHIF';
        $result['provider_code'] = $provider?->code ?? 'NHIF';

        return response()->json($result);
    }

    /**
     * Test connection to provider gateway
     */
    public function testConnection(Request $request, $id)
    {
        $provider = InsuranceProvider::findOrFail($id);
        $gateway = InsuranceGatewayManager::gatewayFor($provider);
        $result = $gateway->testConnection();

        return response()->json($result);
    }

    public function createClaim()
    {
        $providers = InsuranceProvider::all();
        return view('insurance.create_claim', compact('providers'));
    }

    public function storeClaim(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'insurance_provider_id' => 'required|exists:insurance_providers,id',
            'amount' => 'required|numeric|min:0',
            'member_number' => 'nullable|string',
            'member_name' => 'nullable|string',
            'scheme_name' => 'nullable|string',
            'authorization_code' => 'nullable|string',
            'referral_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['claim_number'] = 'CLAIM-' . date('Ymd') . '-' . rand(1000, 9999);
        $validated['status'] = 'Pending';
        $validated['tenant_id'] = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $claim = InsuranceClaim::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dai la bima limerekodiwa kikamilifu!',
                'claim' => $claim,
            ]);
        }

        return redirect()->route('insurance.index')->with('success', 'Insurance claim created successfully');
    }

    public function approveClaim(InsuranceClaim $claim)
    {
        $claim->update(['status' => 'Approved']);
        return redirect()->route('insurance.index')->with('success', 'Claim approved');
    }

    public function rejectClaim(InsuranceClaim $claim)
    {
        $claim->update(['status' => 'Rejected']);
        return redirect()->route('insurance.index')->with('success', 'Claim rejected');
    }
}
