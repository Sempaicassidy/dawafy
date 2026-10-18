<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tenant_id = $user?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $isSuperAdmin = ($user?->role?->name === 'Super Admin' || session('role') === 'Super Admin');

        // Resolve HQ and sister branches
        $currentTenant = Tenant::find($tenant_id);
        $hqId = $currentTenant?->parent_id ?? $tenant_id;

        if ($isSuperAdmin) {
            $branches = Tenant::orderBy('id', 'asc')->get();
        } else {
            $branches = Tenant::where('id', $hqId)->orWhere('parent_id', $hqId)->orderBy('id', 'asc')->get();
        }

        $branchIds = $branches->pluck('id')->toArray();

        $query = User::with(['role', 'tenant']);
        if (!$isSuperAdmin) {
            $query->whereIn('tenant_id', $branchIds);
        }

        $users = $query->orderBy('id', 'asc')->get()->map(function ($u) {
            $isOnline = !empty($u->session_id) && $u->last_activity && $u->last_activity > now()->subMinutes(30);
            return [
                'id' => $u->id,
                'username' => $u->username,
                'full_name' => $u->full_name,
                'phone' => $u->phone,
                'role' => $u->role?->name ?? 'Staff',
                'all_roles' => $u->all_roles,
                'tenant_id' => $u->tenant_id,
                'branch_name' => $u->tenant?->name ?? 'HQ / Main Store',
                'is_online' => $isOnline,
                'last_activity' => $u->last_activity,
                'last_login_ip' => $u->last_login_ip,
                'created_at' => $u->created_at,
            ];
        });

        if (request()->wantsJson()) {
            return response()->json(['users' => $users, 'branches' => $branches]);
        }

        return view('users', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:100',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:4',
            'roles' => 'nullable|array|min:1',
            'roles.*' => 'string|in:Manager,Pharmacist,Cashier',
            'role' => 'nullable|string|in:Manager,Pharmacist,Cashier',
            'branch_id' => 'nullable|integer|exists:tenants,id',
        ], [
            'username.unique' => "The username ':input' is already taken by another account. Please choose a different username.",
            'password.min' => 'The password must be at least 4 characters long.',
            'full_name.required' => 'Please enter the staff member\'s full name.',
        ]);

        // Process roles list
        $chosenRoles = $request->input('roles', []);
        if (empty($chosenRoles) && !empty($validated['role'])) {
            $chosenRoles = [$validated['role']];
        }
        if (empty($chosenRoles)) {
            $chosenRoles = ['Pharmacist'];
        }
        $chosenRoles = array_values(array_unique((array)$chosenRoles));

        // Determine primary role
        $primaryRoleName = in_array('Manager', $chosenRoles) ? 'Manager' : (in_array('Pharmacist', $chosenRoles) ? 'Pharmacist' : 'Cashier');
        $role = Role::where('name', $primaryRoleName)->first();
        if (!$role && $primaryRoleName === 'Manager') {
            $role = Role::where('name', 'Admin')->first();
        }

        $cleanUsername = trim($validated['username']);
        $hashedPassword = Hash::make($validated['password']);
        $assignedBranchId = !empty($validated['branch_id']) ? $validated['branch_id'] : $tenant_id;

        $user = User::create([
            'username' => $cleanUsername,
            'full_name' => trim($validated['full_name']),
            'phone' => $request->filled('phone') ? trim($request->input('phone')) : null,
            'password' => $hashedPassword,
            'password_hash' => $hashedPassword,
            'role_id' => $role ? $role->id : 2,
            'secondary_roles' => $chosenRoles,
            'tenant_id' => $assignedBranchId,
            'status' => 'Active',
        ]);

        $branch = Tenant::find($assignedBranchId);
        $rolesStr = implode(', ', $chosenRoles);

        return redirect()->back()->with('success', "Staff account '{$user->username}' registered with roles [{$rolesStr}] and assigned to {$branch?->name}.");
    }

    public function update(Request $request)
    {
        $id = $request->input('user_id') ?? $request->input('id');
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'roles' => 'nullable|array|min:1',
            'roles.*' => 'string|in:Manager,Pharmacist,Cashier',
            'role' => 'nullable|string|in:Manager,Pharmacist,Cashier',
            'branch_id' => 'nullable|integer|exists:tenants,id',
            'password' => 'nullable|string|min:4',
        ]);

        // Process roles
        $chosenRoles = $request->input('roles');
        if (empty($chosenRoles) && !empty($validated['role'])) {
            $chosenRoles = [$validated['role']];
        }
        if (!empty($chosenRoles)) {
            $chosenRoles = array_values(array_unique((array)$chosenRoles));
            $primaryRoleName = in_array('Manager', $chosenRoles) ? 'Manager' : (in_array('Pharmacist', $chosenRoles) ? 'Pharmacist' : 'Cashier');
            $role = Role::where('name', $primaryRoleName)->first();
            if (!$role && $primaryRoleName === 'Manager') {
                $role = Role::where('name', 'Admin')->first();
            }
            if ($role) {
                $user->role_id = $role->id;
            }
            $user->secondary_roles = $chosenRoles;
        }

        // Process branch assignment / transfer
        $branchChanged = false;
        $oldBranchName = $user->tenant?->name ?? 'HQ / Main Store';
        if (!empty($validated['branch_id']) && $validated['branch_id'] != $user->tenant_id) {
            $oldBranchId = $user->tenant_id;
            $newBranch = Tenant::find($validated['branch_id']);
            $user->tenant_id = $validated['branch_id'];
            $branchChanged = true;

            try {
                AuditLog::create([
                    'user_id' => auth()->id() ?? $user->id,
                    'tenant_id' => $oldBranchId,
                    'action' => 'TRANSFER_STAFF',
                    'details' => "Staff '{$user->username}' transferred from {$oldBranchName} to " . ($newBranch?->name ?? 'Branch #' . $validated['branch_id']),
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Exception $e) {
                // Ignore if log table not ready
            }
        }

        if (!empty($validated['password'])) {
            $hashed = Hash::make($validated['password']);
            $user->password = $hashed;
            $user->password_hash = $hashed;
        }

        if ($request->has('phone')) {
            $user->phone = $request->filled('phone') ? trim($request->input('phone')) : null;
        }

        $user->full_name = trim($validated['full_name']);
        $user->save();

        $msg = "Staff account '{$user->username}' updated successfully.";
        if ($branchChanged) {
            $msg .= " Employee transferred to {$user->tenant?->name}.";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function resetSession(User $user)
    {
        $user->update([
            'session_id' => null,
            'last_activity' => null,
        ]);

        return redirect()->route('users.index')->with('success', "Active session for '{$user->username}' has been terminated successfully. The user can now log in again.");
    }
}

