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
            $recentQuery = Transaction::with(['user', 'patient']);
            if ($tenant_id) {
                $recentQuery->where('tenant_id', $tenant_id);
            }
            $recent_transactions = $recentQuery
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'cashier_name' => $t->user?->name ?? $t->user?->username ?? 'Staff',
                        'customer_name' => $t->patient?->name ?? 'Walk-in Customer',
                        'payment_method' => $t->payment_method ?? 'Cash',
                        'total_amount' => $t->total_amount,
                        'created_at' => $t->created_at,
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

        return view('reports', compact(
            'total_sales',
            'total_orders',
            'inventory_value',
            'recent_transactions',
            'stock_valuation_list'
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
        return $this->index();
    }

    public function daily()
    {
        return $this->index();
    }
}

