<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('role')->where('username', $credentials['username'])
            ->where(function($query) {
                $query->where('status', 'Active')
                      ->orWhereNull('status');
            })
            ->first();

        $validPassword = false;
        if ($user) {
            $rawPass = $user->getAuthPassword();
            if ($rawPass) {
                if ($rawPass === $credentials['password']) {
                    $validPassword = true;
                    // Automatically re-hash plaintext password to Bcrypt
                    $user->update(['password' => Hash::make($credentials['password'])]);
                } elseif (Hash::check($credentials['password'], $rawPass)) {
                    $validPassword = true;
                } elseif (password_verify($credentials['password'], $rawPass)) {
                    $validPassword = true;
                }
            }
        }

        if ($user && $validPassword) {
            // Check if user is already logged in on another device
            $activeThresholdMinutes = 30; // Inactivity threshold in minutes
            $currentSessionId = session()->getId();

            $isAlreadyLoggedIn = !empty($user->session_id)
                && $user->session_id !== $currentSessionId
                && !empty($user->last_activity)
                && $user->last_activity > now()->subMinutes($activeThresholdMinutes);

            if ($isAlreadyLoggedIn) {
                return back()->withErrors([
                    'username' => 'Akaunti hii tayari inatumika kwenye kifaa kingine. Tafadhali toka (logout) kwanza kwenye kifaa hicho au subiri kikao kile kimalizike.',
                ])->withInput($request->only('username'));
            }

            // Regenerate session to get a clean, secure session ID
            $request->session()->regenerate();

            Auth::login($user);

            // Record this device's active session
            $user->update([
                'session_id' => session()->getId(),
                'last_activity' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $roleName = $user->role?->name ?? 'Manager';

            // Store tenant and role in session
            session(['tenant_id' => $user->tenant_id]);
            session(['role' => $roleName]);
            session(['actual_role' => $roleName]);
            session(['full_name' => $user->full_name ?? $user->username]);

            // Log the login action
            try {
                if ($user->tenant_id) {
                    $user->auditLogs()->create([
                        'tenant_id' => $user->tenant_id,
                        'action' => 'Login',
                        'details' => 'User logged in',
                        'ip_address' => $request->ip(),
                    ]);
                }
            } catch (\Exception $e) {
                // Ignore audit log error if any
            }

            // Redirect based on role
            return match($roleName) {
                'Super Admin' => redirect()->route('saas.dashboard'),
                'Cashier' => redirect()->route('pos.index'),
                'Pharmacist' => redirect()->route('prescriptions.index'),
                'Manager', 'Admin' => redirect()->route('dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return back()->withErrors([
            'username' => 'Invalid username or password, or account is suspended.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            try {
                $user->update([
                    'session_id' => null,
                    'last_activity' => null,
                ]);
            } catch (\Throwable $e) {
                // Ignore if update fails
            }

            // Safely log the logout action
            if ($user->tenant_id) {
                try {
                    $user->auditLogs()->create([
                        'tenant_id' => $user->tenant_id,
                        'action' => 'Logout',
                        'details' => 'User logged out',
                        'ip_address' => $request->ip(),
                    ]);
                } catch (\Throwable $e) {
                    // Ignore audit log error if any
                }
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
