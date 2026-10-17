<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $activeRole = session('role') ?? $user->role?->name ?? 'Manager';

        // Super Admin has access everywhere
        if ($activeRole === 'Super Admin' || $user->role?->name === 'Super Admin') {
            return $next($request);
        }

        // Support Manager and Admin interchangeably
        $checkRoles = [];
        foreach ($roles as $r) {
            $checkRoles[] = $r;
            if ($r === 'Manager') {
                $checkRoles[] = 'Admin';
            }
            if ($r === 'Admin') {
                $checkRoles[] = 'Manager';
            }
        }

        $userRoleName = $user->role?->name;
        if (empty($roles) || in_array($activeRole, $checkRoles) || in_array($userRoleName, $checkRoles)) {
            return $next($request);
        }

        // Unauthorized role access
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized action for role: ' . $activeRole], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized access for your current role.');
    }
}
