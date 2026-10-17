<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('user')->orderBy('created_at', 'desc')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        Shift::create([
            'user_id' => auth()->id(),
            'start_time' => now(),
            'opening_balance' => $validated['opening_balance'],
            'tenant_id' => auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1,
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift started successfully');
    }

    public function end(Shift $shift)
    {
        $validated = request()->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift->update([
            'end_time' => now(),
            'closing_balance' => $validated['closing_balance'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift ended successfully');
    }
}
