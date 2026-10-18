<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
            'tin' => $tenant->tin ?? '101-001-001',
            'vat_reg' => $tenant->vat_reg ?? 'VAT-001',
            'logo_path' => $tenant->logo_path,
        ] : [
            'company_name' => 'Dawafy Pharmacy',
            'phone' => '',
            'address' => '',
            'logo_path' => null,
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
            'tin' => 'nullable|string|max:50',
            'vat_reg' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        if ($tenant) {
            $dataToUpdate = [
                'name' => $validated['company_name'] ?? $tenant->name,
                'phone' => $validated['phone'] ?? $tenant->phone,
                'address' => $validated['address'] ?? $tenant->address,
            ];

            // Handle logo file upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $destinationPath = public_path('uploads/logos');
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true, true);
                }

                $filename = 'logo_' . $tenant->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);

                $dataToUpdate['logo_path'] = 'uploads/logos/' . $filename;
            }

            $tenant->update($dataToUpdate);

            // Also synchronize session or global settings if needed
            session(['tenant_name' => $tenant->name, 'tenant_logo' => $tenant->logo_path]);
        }

        return redirect()->route('settings.index')->with('success', 'Pharmacy settings and brand logo updated successfully!');
    }
}
