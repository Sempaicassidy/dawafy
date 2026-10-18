<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Drug;
use App\Models\Batch;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $txQuery = Transaction::query();
        if ($tenant_id) {
            $txQuery->where('tenant_id', $tenant_id);
        }
        $total_sales = (clone $txQuery)->sum('total_amount') ?? 0;
        $total_orders = (clone $txQuery)->count();

        $inventory_value = 0;
        try {
            $batchQuery = Batch::join('drugs', 'batches.drug_id', '=', 'drugs.id');
            if ($tenant_id) {
                $batchQuery->where('batches.tenant_id', $tenant_id);
            }
            $inventory_value = $batchQuery->sum(DB::raw('batches.quantity * drugs.price')) ?? 0;
        } catch (\Exception $e) {
            $inventory_value = 0;
        }

        $recent_transactions = [];
        try {
            $recentQuery = Transaction::with(['user', 'patient', 'customer', 'cashier', 'pharmacist', 'items.drug']);
            if ($tenant_id) {
                $recentQuery->where('tenant_id', $tenant_id);
            }
            if (request('my_sales') == '1' || request()->has('my_sales')) {
                $userId = auth()->id();
                $recentQuery->where(function($q) use ($userId) {
                    $q->where('cashier_id', $userId)
                      ->orWhere('user_id', $userId)
                      ->orWhere('pharmacist_id', $userId);
                });
            }
            $recent_transactions = $recentQuery
                ->latest()
                ->limit(50)
                ->get()
                ->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'cashier_name' => $t->cashier?->name ?? $t->user?->name ?? 'Staff',
                        'pharmacist_name' => $t->pharmacist?->name ?? 'Pharmacist',
                        'customer_name' => $t->patient?->name ?? ($t->customer?->name ?? 'Walk-in Customer'),
                        'payment_method' => $t->payment_method ?? 'Cash',
                        'total_amount' => $t->total_amount,
                        'created_at' => $t->created_at,
                        'status' => $t->status,
                        'items' => $t->items->map(function($i) {
                            return [
                                'name' => $i->drug?->name ?? ($i->item_name ?? 'Medicine'),
                                'quantity' => $i->quantity,
                                'price' => $i->unit_price ?? $i->price,
                                'subtotal' => $i->subtotal ?? ($i->quantity * ($i->unit_price ?? $i->price)),
                            ];
                        })->toArray(),
                    ];
                });
        } catch (\Exception $e) {
            $recent_transactions = [];
        }

        $stock_valuation_list = [];
        try {
            $drugQuery = Drug::with(['batches', 'category']);
            if ($tenant_id) {
                $drugQuery->where('tenant_id', $tenant_id);
            }
            $stock_valuation_list = $drugQuery->get()->map(function ($drug) {
                $qty = $drug->batches ? $drug->batches->sum('quantity') : 0;
                return [
                    'name' => $drug->name,
                    'brand_name' => $drug->brand_name,
                    'category' => $drug->category?->name ?? 'General',
                    'quantity' => $qty,
                    'price' => $drug->price,
                    'total_val' => $qty * $drug->price,
                ];
            });
        } catch (\Exception $e) {
            $stock_valuation_list = [];
        }

        $total_expenses = (float) (\App\Models\Expense::where('tenant_id', $tenant_id)->sum('amount') ?? 0);
        $expenses_by_category = \App\Models\Expense::where('tenant_id', $tenant_id)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('reports', compact(
            'total_sales',
            'total_orders',
            'inventory_value',
            'recent_transactions',
            'stock_valuation_list',
            'total_expenses',
            'expenses_by_category'
        ));
    }

    public function sales(Request $request)
    {
        return $this->index();
    }

    public function inventory()
    {
        return $this->index();
    }

    public function auditLogs()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $logs = AuditLog::with(['user.role'])
            ->where('tenant_id', $tenant_id)
            ->latest()
            ->paginate(25);
        return view('audit_logs', compact('logs'));
    }

    public function daily()
    {
        return $this->index();
    }
}

