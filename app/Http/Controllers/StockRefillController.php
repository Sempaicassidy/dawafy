<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Batch;
use App\Models\StockRefillRequest;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockRefillController extends Controller
{
    /**
     * Pharmacist submits a quick stock refill request from POS or Pharmacist Dashboard
     */
    public function requestRefill(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $validated = $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'requested_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $refill = StockRefillRequest::create([
            'tenant_id' => $tenant_id,
            'pharmacist_id' => auth()->id() ?? 1,
            'drug_id' => $validated['drug_id'],
            'requested_quantity' => $validated['requested_quantity'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'Stock Refill Request',
            'details' => 'Requested ' . $validated['requested_quantity'] . ' units for drug ID #' . $validated['drug_id'],
            'ip_address' => $request->ip(),
            'tenant_id' => $tenant_id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock refill request submitted successfully. Awaiting Manager confirmation.',
                'refill' => $refill->load('drug')
            ]);
        }

        return back()->with('success', 'Stock refill request submitted to Manager.');
    }

    /**
     * Manager approves the refill request with 3 choices:
     * 1. Exact amount requested
     * 2. Below requested amount (duka kuu lina dawa chache)
     * 3. Above requested amount (kusafisha stoo kuu)
     */
    public function approveRefill(Request $request, $id)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $refill = StockRefillRequest::where('tenant_id', $tenant_id)->findOrFail($id);

        if ($refill->status !== 'pending') {
            return back()->with('error', 'This refill request has already been processed.');
        }

        $validated = $request->validate([
            'decision_type' => 'required|in:exact,below,above',
            'custom_quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $decision = $validated['decision_type'];
        $requested = (int)$refill->requested_quantity;

        if ($decision === 'exact') {
            $approvedQty = $requested;
        } elseif ($decision === 'below') {
            $custom = (int)($validated['custom_quantity'] ?? 0);
            if ($custom <= 0 || $custom >= $requested) {
                return back()->with('error', 'For "Below requested", approved quantity must be less than requested (' . $requested . ').');
            }
            $approvedQty = $custom;
        } else { // above
            $custom = (int)($validated['custom_quantity'] ?? 0);
            if ($custom <= $requested) {
                return back()->with('error', 'For "Above requested", approved quantity must be greater than requested (' . $requested . ').');
            }
            $approvedQty = $custom;
        }

        DB::beginTransaction();
        try {
            // Update Refill Request
            $refill->update([
                'approved_quantity' => $approvedQty,
                'status' => 'approved',
                'manager_decision' => $decision,
                'manager_id' => auth()->id() ?? 1,
                'notes' => $validated['notes'] ?? $refill->notes,
            ]);

            $drug = $refill->drug;

            // Automatically issue/replenish stock into front dispensary
            // Add a new batch or increment an active batch
            $todayCode = date('Ymd');
            $batchNumber = 'REFILL-' . $todayCode . '-REQ' . $refill->id;

            $batch = Batch::create([
                'drug_id' => $drug->id,
                'batch_number' => $batchNumber,
                'quantity' => $approvedQty,
                'expiry_date' => now()->addYears(2),
                'tenant_id' => $tenant_id,
            ]);

            // Sync total stock quantity on drug
            $drug->update([
                'quantity' => $drug->batches()->sum('quantity')
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Stock Refill Issued',
                'details' => "Manager approved {$approvedQty} units for {$drug->name} (Decision: {$decision})",
                'ip_address' => $request->ip(),
                'tenant_id' => $tenant_id,
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully issued {$approvedQty} units of {$drug->name} to dispensary.",
                    'drug_id' => $drug->id,
                    'new_stock' => $drug->quantity,
                ]);
            }

            return back()->with('success', "Confirmed & Issued: {$approvedQty} units of {$drug->name} added to dispensary stock.");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error issuing stock: ' . $e->getMessage());
        }
    }

    /**
     * Manager rejects refill request
     */
    public function rejectRefill(Request $request, $id)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $refill = StockRefillRequest::where('tenant_id', $tenant_id)->findOrFail($id);

        $refill->update([
            'status' => 'rejected',
            'manager_id' => auth()->id() ?? 1,
            'notes' => $request->input('notes') ?? 'Rejected by Manager',
        ]);

        AuditLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'Stock Refill Rejected',
            'details' => 'Manager rejected refill request #' . $id,
            'ip_address' => $request->ip(),
            'tenant_id' => $tenant_id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Refill request rejected.']);
        }

        return back()->with('info', 'Refill request was rejected.');
    }
}
