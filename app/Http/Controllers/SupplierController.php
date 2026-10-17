<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $query = Supplier::query();
        if ($tenant_id) {
            $query->where('tenant_id', $tenant_id);
        }

        $suppliers = $query->orderBy('name', 'asc')->get();
        if ($suppliers->isEmpty()) {
            $suppliers = Supplier::orderBy('name', 'asc')->get();
        }

        if (request()->wantsJson()) {
            return response()->json([
                'suppliers' => $suppliers
            ]);
        }

        return view('suppliers', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant_id;

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request)
    {
        $id = $request->input('supplier_id') ?? $request->input('id');
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
