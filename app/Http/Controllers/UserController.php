<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $query = User::with('role');
        if ($tenant_id) {
            $query->where('tenant_id', $tenant_id);
        }

        $users = $query->orderBy('id', 'asc')->get()->map(function ($u) {
            $isOnline = !empty($u->session_id) && $u->last_activity && $u->last_activity > now()->subMinutes(30);
            return [
                'id' => $u->id,
                'username' => $u->username,
                'full_name' => $u->full_name,
                'phone' => $u->phone,
                'role' => $u->role?->name ?? 'Staff',
                'is_online' => $isOnline,
                'last_activity' => $u->last_activity,
                'last_login_ip' => $u->last_login_ip,
                'created_at' => $u->created_at,
            ];
        });

        if (request()->wantsJson()) {
            return response()->json(['users' => $users]);
        }

        return view('users', compact('users'));
    }

    public function store(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:100',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:4',
            'role' => 'required|string|in:Manager,Pharmacist,Cashier',
        ], [
            'username.unique' => "The username ':input' is already taken by another account. Please choose a different username.",
            'password.min' => 'The password must be at least 4 characters long.',
            'full_name.required' => 'Please enter the staff member\'s full name.',
        ]);

        $role = Role::where('name', $validated['role'])->first();
        if (!$role && $validated['role'] === 'Manager') {
            $role = Role::where('name', 'Admin')->first();
        }

        $cleanUsername = trim($validated['username']);
        $hashedPassword = Hash::make($validated['password']);

        $user = User::create([
            'username' => $cleanUsername,
            'full_name' => trim($validated['full_name']),
            'phone' => $request->filled('phone') ? trim($request->input('phone')) : null,
            'password' => $hashedPassword,
            'password_hash' => $hashedPassword,
            'role_id' => $role ? $role->id : 2,
            'tenant_id' => $tenant_id,
            'status' => 'Active',
        ]);

        return redirect()->back()->with('success', "Staff account '{$user->username}' registered successfully.");
    }

    public function update(Request $request)
    {
        $id = $request->input('user_id') ?? $request->input('id');
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|in:Manager,Pharmacist,Cashier',
            'password' => 'nullable|string|min:4',
        ]);

        if (!empty($validated['role'])) {
            $role = Role::where('name', $validated['role'])->first();
            if (!$role && $validated['role'] === 'Manager') {
                $role = Role::where('name', 'Admin')->first();
            }
            if ($role) {
                $user->role_id = $role->id;
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

        return redirect()->back()->with('success', "Staff account '{$user->username}' updated successfully.");
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
