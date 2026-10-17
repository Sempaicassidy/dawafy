<?php

namespace App\Http\Controllers;

use App\Models\Quarantine;
use App\Models\Batch;
use Illuminate\Http\Request;

class QuarantineController extends Controller
{
    public function index()
    {
        $quarantined = Quarantine::with(['batch.drug', 'actionBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quarantine.index', compact('quarantined'));
    }

    public function create()
    {
        $batches = Batch::with('drug')->get();
        return view('quarantine.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        Quarantine::create([
            'batch_id' => $validated['batch_id'],
            'reason' => $validated['reason'],
            'quarantine_date' => now(),
            'notes' => $validated['notes'] ?? null,
            'action_by' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1,
        ]);

        return redirect()->route('quarantine.index')->with('success', 'Batch quarantined successfully');
    }

    public function release(Quarantine $quarantine)
    {
        $quarantine->delete();
        return redirect()->route('quarantine.index')->with('success', 'Batch released from quarantine');
    }
}
