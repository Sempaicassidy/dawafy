<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $query = Expense::query();
        if ($tenant_id) {
            $query->where('tenant_id', $tenant_id);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();
        $total_expenses = $expenses->sum('amount');

        if (request()->wantsJson()) {
            return response()->json([
                'expenses' => $expenses,
                'total_expenses' => $total_expenses
            ]);
        }

        return view('expenses', compact('expenses', 'total_expenses'));
    }

    public function store(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant_id;

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }
}
