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

        $activeRole = session('role') ?? $user->role?->name ?? 'Admin';

        // Super Admin has access everywhere
        if ($activeRole === 'Super Admin' || $user->role?->name === 'Super Admin') {
            return $next($request);
        }

        if (empty($roles) || in_array($activeRole, $roles) || in_array($user->role?->name, $roles)) {
            return $next($request);
        }

        // Unauthorized role access
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized action for role: ' . $activeRole], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized access for your current role.');
    }
}
