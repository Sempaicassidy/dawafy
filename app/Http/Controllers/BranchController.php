<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tenantId = $user?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id');
        $isSuperAdmin = ($user?->role?->name === 'Super Admin' || session('role') === 'Super Admin');

        if ($isSuperAdmin) {
            $branches = Tenant::whereNotNull('parent_id')->get();
        } elseif ($tenantId) {
            $branches = Tenant::where('parent_id', $tenantId)->get();
        } else {
            $branches = collect();
        }

        return view('branches', ['branches' => $branches->toArray()]);
    }

    public function create()
    {
        return redirect()->route('branches.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        $parentId = auth()->user()?->tenant_id ?? Tenant::first()?->id ?? 1;
        $validated['parent_id'] = $parentId;
        $validated['status'] = 'Active';

        Tenant::create($validated);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully');
    }

    public function edit(Tenant $branch)
    {
        return redirect()->route('branches.index');
    }

    public function update(Request $request, $id = null)
    {
        $branchId = $id ?? $request->input('branch_id');
        $branch = Tenant::findOrFail($branchId);

        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully');
    }

    public function toggle(Request $request, $id = null)
    {
        $branchId = $id ?? $request->input('branch_id');
        $branch = Tenant::findOrFail($branchId);
        $newStatus = ($branch->status === 'Active') ? 'Inactive' : 'Active';
        $branch->update(['status' => $newStatus]);

        return redirect()->route('branches.index')->with('success', "Branch status changed to {$newStatus}");
    }

    public function activate(Tenant $branch)
    {
        $branch->update(['status' => 'Active']);
        return redirect()->route('branches.index')->with('success', 'Branch activated successfully');
    }
}

