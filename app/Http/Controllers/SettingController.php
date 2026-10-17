<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $tenant = $tenant_id ? Tenant::find($tenant_id) : Tenant::first();

        $settings = $tenant ? [
            'company_name' => $tenant->name,
            'phone' => $tenant->phone,
            'address' => $tenant->address,
            'email' => $tenant->email,
            'tin' => $tenant->tin,
            'vat_reg' => $tenant->vat_reg,
        ] : [
            'company_name' => 'Daawafy Pharmacy',
            'phone' => '',
            'address' => '',
        ];

        if (request()->wantsJson()) {
            return response()->json(['settings' => $settings]);
        }

        return view('settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $tenant = $tenant_id ? Tenant::find($tenant_id) : Tenant::first();

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        if ($tenant) {
            $tenant->update([
                'name' => $validated['company_name'] ?? $tenant->name,
                'phone' => $validated['phone'] ?? $tenant->phone,
                'address' => $validated['address'] ?? $tenant->address,
            ]);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
