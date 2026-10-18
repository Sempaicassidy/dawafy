<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $baseQuery = Expense::where('tenant_id', $tenant_id);

        // Calculate KPI Metrics before applying search filters
        $today_expenses = (float) (clone $baseQuery)
            ->whereDate('expense_date', today())
            ->sum('amount');

        $month_expenses = (float) (clone $baseQuery)
            ->where('expense_date', '>=', now()->startOfMonth())
            ->sum('amount');

        $topCategoryRow = (clone $baseQuery)
            ->select('category', DB::raw('SUM(amount) as total_cat'))
            ->groupBy('category')
            ->orderByDesc('total_cat')
            ->first();
        $top_category = $topCategoryRow ? $topCategoryRow->category : 'N/A';

        // Filtered Query
        $query = (clone $baseQuery)->with('user');

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderBy('expense_date', 'desc')->orderBy('id', 'desc')->get();
        $total_expenses = (float) $expenses->sum('amount');

        // Distinct list of categories for filter dropdown
        $all_categories = (clone $baseQuery)->select('category')->distinct()->pluck('category')->toArray();
        $standard_categories = [
            'Utilities',
            'Rent',
            'Salaries',
            'Transport',
            'Procurement',
            'Maintenance',
            'Cleaning & Sanitation',
            'Stationery & Packaging',
            'Regulatory & Licenses',
            'Petty Cash',
            'Other'
        ];
        $category_options = array_values(array_unique(array_merge($standard_categories, $all_categories)));

        if ($request->wantsJson()) {
            return response()->json([
                'expenses' => $expenses,
                'total_expenses' => $total_expenses,
                'today_expenses' => $today_expenses,
                'month_expenses' => $month_expenses,
            ]);
        }

        return view('expenses', compact(
            'expenses',
            'total_expenses',
            'today_expenses',
            'month_expenses',
            'top_category',
            'category_options'
        ));
    }

    public function store(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'custom_category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $category = trim($validated['category']);
        if ($category === 'Other' && !empty($validated['custom_category'])) {
            $category = trim($validated['custom_category']);
        }

        Expense::create([
            'tenant_id' => $tenant_id,
            'user_id' => auth()->id() ?? 1,
            'category' => $category,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense of TZS ' . number_format($validated['amount']) . ' recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $expense = Expense::where('tenant_id', $tenant_id)->findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'custom_category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $category = trim($validated['category']);
        if ($category === 'Other' && !empty($validated['custom_category'])) {
            $category = trim($validated['custom_category']);
        }

        $expense->update([
            'category' => $category,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy($id)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $expense = Expense::where('tenant_id', $tenant_id)->findOrFail($id);
        $amount = $expense->amount;
        $category = $expense->category;
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', "Expense record '{$category}' (TZS " . number_format($amount) . ") has been deleted.");
    }
}
