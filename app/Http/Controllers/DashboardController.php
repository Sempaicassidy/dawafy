<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Batch;
use App\Models\Drug;

class DashboardController extends Controller
{
    public function index()
    {
        $roleName = auth()->user()?->role?->name ?? session('role');
        if ($roleName === 'Super Admin' && empty(session('masquerade_tenant_id'))) {
            return redirect()->route('saas.dashboard');
        }

        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $todaySales = Transaction::whereDate('created_at', today())
            ->where('tenant_id', $tenant_id)
            ->sum('total_amount') ?? 0;

        $txnCount = Transaction::whereDate('created_at', today())
            ->where('tenant_id', $tenant_id)
            ->count() ?? 0;

        $lowStock = Batch::with('drug')
            ->where('tenant_id', $tenant_id)
            ->whereHas('drug', function($query) {
                $query->whereColumn('batches.quantity', '<=', 'drugs.reorder_level');
            })
            ->count() ?? 0;

        $expiring = Batch::where('tenant_id', $tenant_id)
            ->where('expiry_date', '<=', now()->addMonths(6))
            ->count() ?? 0;

        $recentTxns = Transaction::with(['user', 'patient'])
            ->where('tenant_id', $tenant_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $metrics = [
            'today_sales' => $todaySales,
            'txn_count' => $txnCount,
            'low_stock' => $lowStock,
            'expiring' => $expiring,
            'recent_txns' => $recentTxns
        ];

        if (request()->wantsJson()) {
            return response()->json($metrics);
        }

        return view('dashboard', compact('metrics'));
    }
}
