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
                'role' => $u->role->name ?? 'Staff',
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
            'password' => 'required|string|min:4',
            'role' => 'required|string',
        ]);

        $role = Role::where('name', $validated['role'])->first();

        User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role ? $role->id : null,
            'tenant_id' => $tenant_id,
            'status' => 'Active',
        ]);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function update(Request $request)
    {
        $id = $request->input('user_id') ?? $request->input('id');
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'role' => 'nullable|string',
            'password' => 'nullable|string|min:4',
        ]);

        if (!empty($validated['role'])) {
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->role_id = $role->id;
            }
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->full_name = $validated['full_name'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function resetSession(User $user)
    {
        $user->update([
            'session_id' => null,
            'last_activity' => null,
        ]);

        return redirect()->route('users.index')->with('success', "Session ya '{$user->username}' imefutwa. Sasa mtumiaji huyo anaweza kuingia upya.");
    }
}
