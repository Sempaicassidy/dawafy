<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Patient;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function aiSales()
    {
        $sales_analysis = [];
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        try {
            $drugs = Drug::with(['category', 'batches'])
                ->where('tenant_id', $tenant_id)
                ->limit(50)
                ->get();

            foreach ($drugs as $drug) {
                $batchIds = $drug->batches->pluck('id')->toArray();
                $totalStock = $drug->stock;

                $sold30days = \App\Models\TransactionItem::whereIn('batch_id', $batchIds)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->sum('quantity') ?? 0;

                $dailyRate = round($sold30days / 30, 2);
                $daysToStockout = $dailyRate > 0 ? round($totalStock / $dailyRate) : ($totalStock > 0 ? 999 : 0);

                $velocity = 'Low';
                $riskLevel = 'Low';
                $insights = [];

                if ($dailyRate >= 5) {
                    $velocity = 'Fast-Moving';
                } elseif ($dailyRate >= 1) {
                    $velocity = 'Normal';
                }

                if ($totalStock <= $drug->reorder_level) {
                    $riskLevel = 'Critical';
                    $insights[] = 'Stock below reorder threshold (' . $drug->reorder_level . ' units). Reorder immediately.';
                } elseif ($daysToStockout <= 7 && $dailyRate > 0) {
                    $riskLevel = 'High';
                    $insights[] = 'Stock projected to deplete within ' . $daysToStockout . ' days.';
                } else {
                    $insights[] = 'Stock velocity stable with optimal buffer.';
                }

                $sales_analysis[] = [
                    'drug' => [
                        'name' => $drug->name,
                        'category' => $drug->category?->name ?? 'General',
                        'total_stock' => $totalStock,
                        'sold_30days' => $sold30days,
                    ],
                    'daily_rate' => $dailyRate,
                    'days_to_stockout' => $daysToStockout,
                    'velocity' => $velocity,
                    'risk_level' => $riskLevel,
                    'insights' => $insights,
                ];
            }
        } catch (\Exception $e) {
            $sales_analysis = [];
        }

        return view('ai_sales_report', compact('sales_analysis'));
    }

    public function erp()
    {
        $erp_configs = [];
        return view('erp_integration', compact('erp_configs'));
    }

    public function onlinePurchases()
    {
        $purchase_orders = [];
        $suppliers = [];
        try {
            $suppliers = Supplier::all()->toArray();
            $pos = PurchaseOrder::with('supplier')->get();
            foreach ($pos as $po) {
                $purchase_orders[] = [
                    'id' => $po->id,
                    'order_reference' => 'PO-' . str_pad($po->id, 5, '0', STR_PAD_LEFT),
                    'supplier_name' => $po->supplier?->name ?? 'Wholesale Supplier',
                    'source_platform' => 'Online Portal',
                    'total_amount' => 0,
                    'order_date' => $po->created_at ? $po->created_at->format('Y-m-d') : date('Y-m-d'),
                    'status' => strtolower($po->status ?? 'pending'),
                ];
            }
        } catch (\Exception $e) {
            $purchase_orders = [];
            $suppliers = [];
        }
        return view('online_purchases', compact('purchase_orders', 'suppliers'));
    }

    public function whatsapp()
    {
        $queue = [];
        $customers = [];
        try {
            $customers = Patient::all()->toArray();
        } catch (\Exception $e) {
            $customers = [];
        }
        return view('whatsapp_customers', compact('queue', 'customers'));
    }

    public function dataTransfer()
    {
        $logs = [];
        return view('data_transfer', compact('logs'));
    }

    public function vat()
    {
        $vat_settings = [
            'vat_rate' => 18.0,
            'vat_number' => '100-200-300',
            'is_vat_registered' => true,
        ];
        $monthly_vat = 0;
        return view('vat_settings', compact('vat_settings', 'monthly_vat'));
    }

    public function backup()
    {
        $logs = [];
        $config = [
            'backup_frequency' => 'daily',
            'backup_provider' => 'Local SQL',
        ];
        return view('backup_system', compact('logs', 'config'));
    }
}
