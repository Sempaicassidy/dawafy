<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $query = PurchaseOrder::with(['supplier', 'createdBy']);
        if ($tenant_id) {
            $query->where('tenant_id', $tenant_id);
        }
        $purchaseOrders = $query->orderBy('created_at', 'desc')->get();

        return view('procurement.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $suppliers = Supplier::where('tenant_id', $tenant_id)->orWhereNull('tenant_id')->get();
        if ($suppliers->isEmpty()) {
            $suppliers = Supplier::all();
        }
        $drugs = \App\Models\Drug::where('tenant_id', $tenant_id)->get();
        return view('procurement.create', compact('suppliers', 'drugs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $totalAmount = collect($validated['items'])->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
            'supplier_id' => $validated['supplier_id'],
            'total_amount' => $totalAmount,
            'order_date' => now(),
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1,
        ]);

        foreach ($validated['items'] as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'drug_id' => $item['drug_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('procurement.index')->with('success', 'Purchase Order created successfully');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.drug', 'createdBy']);
        return view('procurement.show', compact('purchaseOrder'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'Approved']);
        return redirect()->route('procurement.index')->with('success', 'Purchase Order approved');
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'Received']);
        return redirect()->route('procurement.index')->with('success', 'Purchase Order received');
    }
}
