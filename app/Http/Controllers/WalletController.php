<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Patient;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $customers = [];
        try {
            $customers = Patient::with('wallet')->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'phone' => $p->phone,
                    'email' => $p->email,
                    'wallet_balance' => $p->wallet?->balance ?? 0,
                ];
            })->toArray();
        } catch (\Exception $e) {
            $customers = [];
        }

        return view('wallet', compact('customers'));
    }

    public function transaction(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:patients,id',
            'transaction_type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $patient = Patient::findOrFail($validated['customer_id']);
        if ($validated['transaction_type'] === 'credit') {
            $this->credit($request, $patient);
        } else {
            $this->debit($request, $patient);
        }

        return redirect()->route('wallet.index')->with('success', 'Wallet transaction processed successfully');
    }


    public function credit(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $wallet = $patient->wallet ?? $patient->wallet()->create([
            'balance' => 0,
            'tenant_id' => auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1,
        ]);

        $wallet->transactions()->create([
            'type' => 'Credit',
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? 'Wallet credit',
        ]);

        $wallet->increment('balance', $validated['amount']);

        return redirect()->route('wallet.show', $patient)->with('success', 'Wallet credited successfully');
    }

    public function debit(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $wallet = $patient->wallet;

        if ($wallet->balance < $validated['amount']) {
            return back()->with('error', 'Insufficient wallet balance');
        }

        $wallet->transactions()->create([
            'type' => 'Debit',
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? 'Wallet debit',
        ]);

        $wallet->decrement('balance', $validated['amount']);

        return redirect()->route('wallet.show', $patient)->with('success', 'Wallet debited successfully');
    }
}
