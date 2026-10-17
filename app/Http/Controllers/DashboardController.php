<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\Expense;
use App\Models\User;
use App\Models\Patient;
use App\Models\AuditLog;
use App\Models\InsuranceClaim;
use App\Models\InsuranceProvider;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $roleName = auth()->user()?->role?->name ?? session('role');
        if ($roleName === 'Super Admin' && empty(session('masquerade_tenant_id'))) {
            return redirect()->route('saas.dashboard');
        }

        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        // =========================================================================
        // 1. FINANCIAL OVERVIEW (Real-time Sales, COGS, Profit Margins, Cash Flow)
        // =========================================================================
        $todaySales = (float)(Transaction::whereDate('created_at', today())
            ->where('tenant_id', $tenant_id)
            ->sum('total_amount') ?? 0);

        $todayTxnCount = Transaction::whereDate('created_at', today())
            ->where('tenant_id', $tenant_id)
            ->count();

        $weekSales = (float)(Transaction::where('created_at', '>=', now()->startOfWeek())
            ->where('tenant_id', $tenant_id)
            ->sum('total_amount') ?? 0);

        $monthSales = (float)(Transaction::where('created_at', '>=', now()->startOfMonth())
            ->where('tenant_id', $tenant_id)
            ->sum('total_amount') ?? 0);

        $allTimeSales = (float)(Transaction::where('tenant_id', $tenant_id)
            ->sum('total_amount') ?? 0);

        // Active revenue base for monthly calculations
        $revenueBase = $monthSales > 0 ? $monthSales : $allTimeSales;

        // Payment Method Breakdown
        $paymentBreakdown = Transaction::where('tenant_id', $tenant_id)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Calculate Cost of Goods Sold (COGS) & Gross Profit
        $txItems = TransactionItem::whereHas('transaction', function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id);
            })
            ->with('drug')
            ->get();

        $totalCOGS = 0;
        foreach ($txItems as $item) {
            $buyPrice = (float)($item->drug?->buying_price ?? 0);
            if ($buyPrice <= 0) {
                // If buying price not recorded yet, benchmark COGS at 70% of selling price (30% standard gross margin)
                $buyPrice = (float)($item->price * 0.70);
            }
            $totalCOGS += ($buyPrice * $item->quantity);
        }

        $grossProfit = max(0, $revenueBase - $totalCOGS);
        $grossMarginPct = $revenueBase > 0 ? round(($grossProfit / $revenueBase) * 100, 1) : 0;

        // Cash Flow & Expenses
        $totalExpenses = (float)(Expense::where('tenant_id', $tenant_id)->sum('amount') ?? 0);
        $expensesByCategory = Expense::where('tenant_id', $tenant_id)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $netProfit = $grossProfit - $totalExpenses;
        $netMarginPct = $revenueBase > 0 ? round(($netProfit / $revenueBase) * 100, 1) : 0;
        $netCashFlow = $revenueBase - $totalExpenses;

        // =========================================================================
        // 2. INVENTORY & STOCK CONTROL (Expiry Alerts, Low Stock, Fast-Moving)
        // =========================================================================
        // Expiring within 3 months (Critical)
        $expiring3Months = Batch::with('drug')
            ->where('tenant_id', $tenant_id)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addMonths(3))
            ->where('expiry_date', '>=', now())
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Expiring within 6 months (Warning)
        $expiring6MonthsCount = Batch::where('tenant_id', $tenant_id)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addMonths(6))
            ->where('expiry_date', '>=', now())
            ->count();

        // Expired (Past expiry date)
        $alreadyExpiredCount = Batch::where('tenant_id', $tenant_id)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', now())
            ->count();

        // Low stock items
        $lowStockDrugs = Drug::with('batches')
            ->where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })
            ->get()
            ->filter(function($drug) {
                $totalStock = $drug->batches ? $drug->batches->sum('quantity') : 0;
                return $totalStock <= $drug->reorder_level;
            })
            ->values();

        // Fast-Moving Items (Top 5 items by units sold)
        $fastMovingItems = TransactionItem::whereHas('transaction', function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id);
            })
            ->select('drug_id', DB::raw('SUM(quantity) as units_sold'), DB::raw('SUM(price * quantity) as revenue_generated'))
            ->groupBy('drug_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->with('drug')
            ->get();

        // =========================================================================
        // 3. STAFF PERFORMANCE & SECURITY (Sales per Staff, Activity Audit Trail)
        // =========================================================================
        $staffSales = Transaction::where('tenant_id', $tenant_id)
            ->select('user_id', DB::raw('COUNT(*) as total_txns'), DB::raw('SUM(total_amount) as total_volume'))
            ->groupBy('user_id')
            ->with('user.role')
            ->orderByDesc('total_volume')
            ->get();

        $auditLogs = AuditLog::where('tenant_id', $tenant_id)
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        // =========================================================================
        // 4. ACCOUNTS RECEIVABLE (Insurance Claims & Creditors / Debtors Ledger)
        // =========================================================================
        $pendingClaims = InsuranceClaim::where('status', 'Pending')->get();
        $approvedClaims = InsuranceClaim::whereIn('status', ['Approved', 'Paid'])->get();
        $rejectedClaims = InsuranceClaim::where('status', 'Rejected')->get();

        $insuranceMetrics = [
            'pending_count' => $pendingClaims->count(),
            'pending_amount' => (float)$pendingClaims->sum('amount'),
            'approved_count' => $approvedClaims->count(),
            'approved_amount' => (float)$approvedClaims->sum('amount'),
            'rejected_count' => $rejectedClaims->count(),
            'rejected_amount' => (float)$rejectedClaims->sum('amount'),
            'recent_claims' => InsuranceClaim::with(['insuranceProvider', 'transaction'])->latest()->limit(5)->get()
        ];

        // Creditors / Debtors Ledger
        $debtors = Patient::where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })
            ->where('outstanding_debt', '>', 0)
            ->orderByDesc('outstanding_debt')
            ->limit(10)
            ->get();

        $totalOutstandingDebt = (float)Patient::where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })
            ->sum('outstanding_debt');

        // Recent 5 Transactions
        $recentTxns = Transaction::with(['user', 'patient'])
            ->where('tenant_id', $tenant_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $metrics = [
            // Financial
            'today_sales' => $todaySales,
            'today_txns' => $todayTxnCount,
            'week_sales' => $weekSales,
            'month_sales' => $monthSales,
            'revenue_base' => $revenueBase,
            'cogs' => $totalCOGS,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => $grossMarginPct,
            'total_expenses' => $totalExpenses,
            'expenses_by_category' => $expensesByCategory,
            'net_profit' => $netProfit,
            'net_margin_pct' => $netMarginPct,
            'net_cash_flow' => $netCashFlow,
            'payment_breakdown' => $paymentBreakdown,

            // Inventory
            'expiring_3_months' => $expiring3Months,
            'expiring_6_months_count' => $expiring6MonthsCount,
            'already_expired_count' => $alreadyExpiredCount,
            'low_stock_drugs' => $lowStockDrugs,
            'fast_moving_items' => $fastMovingItems,

            // Staff & Security
            'staff_sales' => $staffSales,
            'audit_logs' => $auditLogs,

            // Accounts Receivable
            'insurance' => $insuranceMetrics,
            'debtors' => $debtors,
            'total_debt' => $totalOutstandingDebt,

            // Recent
            'recent_txns' => $recentTxns,
        ];

        if (request()->wantsJson()) {
            return response()->json($metrics);
        }

        return view('dashboard', compact('metrics'));
    }
}
