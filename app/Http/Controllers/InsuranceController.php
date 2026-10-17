<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use App\Models\InsuranceClaim;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function index()
    {
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
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        InsuranceProvider::create($validated);

        return redirect()->route('insurance.index')->with('success', 'Insurance provider created successfully');
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
            'notes' => 'nullable|string',
        ]);

        $validated['claim_number'] = 'CLAIM-' . date('Ymd') . '-' . rand(1000, 9999);
        $validated['status'] = 'Pending';

        InsuranceClaim::create($validated);

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
