<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class TenantCheck
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $tenant_id = session('tenant_id');

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin bypasses tenant checks
        if ($user->role->name === 'Super Admin') {
            return $next($request);
        }

        // Check if tenant exists and is active
        if ($tenant_id) {
            $tenant = Tenant::find($tenant_id);

            if (!$tenant) {
                return redirect()->route('dashboard')->with('error', 'Tenant not found');
            }

            // Check branch activation
            if ($tenant->parent_id && $tenant->status !== 'Active') {
                return redirect()->route('dashboard')->with('error', 'Branch not activated');
            }

            // Check payment status and license expiry
            if ($tenant->payment_status !== 'Paid' || $tenant->isExpired()) {
                return redirect()->route('dashboard')->with('error', 'Account payment pending or license expired');
            }
        }

        return $next($request);
    }
}
