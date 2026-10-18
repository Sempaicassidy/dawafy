<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use App\Models\InsuranceClaim;
use App\Models\SubCategory;
use App\Models\Drug;
use App\Models\Batch;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Patient;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $hasCashier = User::where('tenant_id', $tenant_id)
            ->whereHas('role', function($q) {
                $q->where('name', 'Cashier');
            })
            ->where(function($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['Inactive', 'Suspended']);
            })
            ->exists();

        $activeRole = session('role') ?? auth()->user()?->role?->name ?? 'Manager';
        if ($activeRole === 'Cashier') {
            return redirect()->route('pos.receive_payments');
        }

        $isPharmacist = ($activeRole === 'Pharmacist');

        $insuranceProviders = InsuranceProvider::where('tenant_id', $tenant_id)
            ->orWhereNull('tenant_id')
            ->orderBy('name')
            ->get();

        $subCategories = SubCategory::where('tenant_id', $tenant_id)
            ->orWhereNull('tenant_id')
            ->orderBy('name')
            ->get();

        $data = [
            'insurance_providers' => $insuranceProviders,
            'sub_categories' => $subCategories,
            'has_cashier' => $hasCashier,
            'is_pharmacist' => $isPharmacist,
            'active_role' => $activeRole,
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return view('pos', $data);
    }

    /**
     * Dedicated Receive Payments desk for Cashier
     */
    public function receivePaymentsDesk()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $pendingPayments = Transaction::with(['items.drug', 'pharmacist', 'patient', 'customer'])
            ->where('tenant_id', $tenant_id)
            ->where('status', 'pending_payment')
            ->latest()
            ->get();

        $todayPaidReceipts = Transaction::with(['items.drug', 'pharmacist', 'patient', 'customer', 'cashier'])
            ->where('tenant_id', $tenant_id)
            ->where('status', 'paid')
            ->whereDate('updated_at', today())
            ->latest('updated_at')
            ->limit(30)
            ->get();

        return view('receive_payments', [
            'pendingPayments' => $pendingPayments,
            'todayPaidReceipts' => $todayPaidReceipts,
        ]);
    }

    public function searchDrugs(Request $request)
    {
        $query = $request->input('q');
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $quarantinedBatchIds = \App\Models\Quarantine::pluck('batch_id')->toArray();

        $drugs = Drug::with(['category', 'subCategory', 'batches'])
            ->where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)
                  ->orWhereNull('tenant_id');
            })
            ->when($query, function($q) use ($query) {
                $q->where(function($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('brand_name', 'like', "%{$query}%")
                        ->orWhere('generic_name', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
                });
            })
            ->get();

        // Merge all drug records sharing the exact same name (case-insensitive & trimmed)
        $mergedDrugs = $drugs->groupBy(function($drug) {
            return strtolower(trim($drug->name));
        })->map(function($group) use ($quarantinedBatchIds) {
            // Representative drug is the one with stock or the first record with price
            $primary = $group->sortByDesc(function($d) {
                return $d->batches->where('quantity', '>', 0)->sum('quantity');
            })->first() ?: $group->first();

            // Collect all non-expired, non-quarantined batches across all matched drugs
            $allValidBatches = collect();
            foreach ($group as $d) {
                $bList = $d->batches
                    ->where('quantity', '>', 0)
                    ->whereNotIn('id', $quarantinedBatchIds)
                    ->filter(function($b) {
                        return $b->expiry_date === null || $b->expiry_date >= now()->startOfDay();
                    });
                $allValidBatches = $allValidBatches->concat($bList);
            }

            // Order batches strictly by FEFO / FIFO:
            // 1) Earliest expiry date first
            // 2) If dates equal or null, earliest created_at, then lowest ID
            $sortedBatches = $allValidBatches->sort(function($a, $b) {
                if ($a->expiry_date == $b->expiry_date) {
                    if ($a->created_at == $b->created_at) {
                        return $a->id <=> $b->id;
                    }
                    return $a->created_at <=> $b->created_at;
                }
                if ($a->expiry_date === null) return 1;
                if ($b->expiry_date === null) return -1;
                return $a->expiry_date <=> $b->expiry_date;
            })->values();

            $totalStock = (int)$sortedBatches->sum('quantity');
            $earliestBatch = $sortedBatches->first();

            $price = (float)$primary->price;
            if ($price <= 0) {
                $firstWithPrice = $group->first(fn($d) => (float)$d->price > 0);
                if ($firstWithPrice) $price = (float)$firstWithPrice->price;
            }

            $reorder = (int)($primary->reorder_level ?: 10);
            $stockLight = 'green';
            if ($totalStock <= 0) {
                $stockLight = 'red';
            } elseif ($totalStock <= $reorder) {
                $stockLight = 'yellow';
            }

            return [
                'id' => $primary->id,
                'drug_id' => $primary->id,
                'matched_drug_ids' => $group->pluck('id')->toArray(),
                'name' => $primary->name,
                'brand_name' => $primary->brand_name ?? '',
                'generic_name' => $primary->generic_name ?? '',
                'dosage_form' => $primary->dosage_form ?? 'Tablet',
                'barcode' => $primary->barcode ?? '',
                'category' => $primary->category->name ?? ($primary->category ?? 'POM'),
                'tmda_short' => $primary->tmda_short,
                'sub_category' => $primary->subCategory->name ?? null,
                'price' => $price,
                'stock' => $totalStock,
                'reorder_level' => $reorder,
                'stock_light' => $stockLight,
                'batch_id' => $earliestBatch?->id,
                'batch_number' => $earliestBatch?->batch_number ?? null,
                'expiry_date' => $earliestBatch?->expiry_date ? $earliestBatch->expiry_date->format('Y-m-d') : null,
                'batches_count' => $sortedBatches->count(),
            ];
        })->values();

        return response()->json($mergedDrugs);
    }

    public function findCustomer(Request $request)
    {
        $phone = $request->input('phone');
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $patient = Patient::where('phone', $phone)
            ->where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })
            ->first();

        if ($patient) {
            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Customer not found']);
    }

    public function registerCustomer(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $name = $request->input('name');
        $phone = $request->input('phone');

        if (!$name) {
            return response()->json(['success' => false, 'message' => 'Customer name required'], 422);
        }

        $patient = Patient::create([
            'name' => $name,
            'phone' => $phone,
            'tenant_id' => $tenant_id,
        ]);

        return response()->json([
            'success' => true,
            'id' => $patient->id,
            'name' => $patient->name,
        ]);
    }

    public function legacyApi(Request $request)
    {
        $action = $request->input('action', 'search');

        if ($action === 'find_customer') {
            return $this->findCustomer($request);
        }

        if ($action === 'register_customer') {
            return $this->registerCustomer($request);
        }

        if ($action === 'send_to_cashier') {
            return $this->sendToCashier($request);
        }

        if ($action === 'checkout' || $request->isMethod('post')) {
            return $this->createTransaction($request);
        }

        return $this->searchDrugs($request);
    }

    /**
     * Find all drug IDs matching the given drug's name (case-insensitive & trimmed) for the tenant.
     */
    protected function getMatchingDrugIds($drugIdOrModel, $tenant_id)
    {
        if ($drugIdOrModel instanceof Drug) {
            $name = trim($drugIdOrModel->name);
        } else {
            $drug = Drug::find($drugIdOrModel);
            $name = $drug ? trim($drug->name) : '';
        }

        if (!$name) {
            return is_numeric($drugIdOrModel) ? [(int)$drugIdOrModel] : [];
        }

        return Drug::where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get all active, non-quarantined batches for matched drug IDs ordered by strict FEFO / FIFO:
     * 1) Earliest expiry date first (non-null dates before null dates)
     * 2) Earliest created_at, then lowest ID
     */
    protected function getFifoBatchesForDrugs(array $drugIds, $tenant_id)
    {
        $quarantinedBatchIds = \App\Models\Quarantine::pluck('batch_id')->toArray();

        return Batch::whereIn('drug_id', $drugIds)
            ->where('quantity', '>', 0)
            ->whereNotIn('id', $quarantinedBatchIds)
            ->where(function($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay());
            })
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC, created_at ASC, id ASC')
            ->get();
    }

    /**
     * Parse items from request cart payload
     */
    protected function parseCartItems(Request $request, $tenant_id)
    {
        $cartItems = [];
        if ($request->has('cart') && is_string($request->input('cart'))) {
            $cartItems = json_decode($request->input('cart'), true) ?: [];
        } elseif ($request->has('items') && is_array($request->input('items'))) {
            $cartItems = $request->input('items');
        }

        $mappedItems = [];
        foreach ($cartItems as $c) {
            $drugId = $c['drug_id'] ?? $c['id'] ?? null;
            $qty = (int)($c['qty'] ?? $c['quantity'] ?? 1);
            if ($qty <= 0) continue;

            $matchedIds = $c['matched_drug_ids'] ?? null;
            if (empty($matchedIds) && $drugId) {
                $matchedIds = $this->getMatchingDrugIds($drugId, $tenant_id);
            }

            $batchId = $c['batch_id'] ?? null;
            if (!$batchId && !empty($matchedIds)) {
                $fifoBatch = $this->getFifoBatchesForDrugs($matchedIds, $tenant_id)->first();
                $batchId = $fifoBatch?->id;
            }

            if (!$batchId && $drugId) {
                $defaultBatch = Batch::firstOrCreate(
                    ['drug_id' => $drugId, 'batch_number' => 'STD-' . date('Ymd')],
                    [
                        'quantity' => 1000,
                        'expiry_date' => now()->addYears(2),
                        'tenant_id' => $tenant_id
                    ]
                );
                $batchId = $defaultBatch->id;
            }

            if ($drugId) {
                $mappedItems[] = [
                    'drug_id' => $drugId,
                    'matched_drug_ids' => !empty($matchedIds) ? $matchedIds : [$drugId],
                    'batch_id' => $batchId,
                    'quantity' => $qty,
                    'dosage' => $c['dosage'] ?? null,
                ];
            }
        }

        return $mappedItems;
    }

    /**
     * Step 1: Pharmacist chooses medicines and clicks "Send to Cashier".
     * Transaction is created with status = 'pending_payment'.
     * Stock is NOT deducted yet.
     */
    public function sendToCashier(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $items = $this->parseCartItems($request, $tenant_id);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($items as $item) {
                $drug = Drug::findOrFail($item['drug_id']);
                $matchedDrugIds = $item['matched_drug_ids'] ?? $this->getMatchingDrugIds($drug, $tenant_id);
                $qty = (int)$item['quantity'];

                // Check total stock across all merged drug batches
                $availableBatches = $this->getFifoBatchesForDrugs($matchedDrugIds, $tenant_id);
                $availableStock = (int)$availableBatches->sum('quantity');

                if ($availableStock < $qty) {
                    throw new \Exception("Insufficient stock for {$drug->name}. Available: {$availableStock}, requested: {$qty}");
                }

                $price = (float)$drug->price;
                if ($price <= 0) {
                    $altDrug = Drug::whereIn('id', $matchedDrugIds)->where('price', '>', 0)->first();
                    if ($altDrug) $price = (float)$altDrug->price;
                }

                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                $earliestBatchId = $item['batch_id'] ?: $availableBatches->first()?->id;

                $itemsData[] = [
                    'drug' => $drug,
                    'matched_drug_ids' => $matchedDrugIds,
                    'batch_id' => $earliestBatchId,
                    'quantity' => $qty,
                    'price' => $price,
                    'dosage' => $item['dosage'] ?? null,
                ];
            }

            $transaction = Transaction::create([
                'user_id' => auth()->id() ?? 1,
                'pharmacist_id' => auth()->id() ?? 1,
                'customer_id' => $request->input('customer_id') ?: $request->input('patient_id'),
                'total_amount' => $totalAmount,
                'payment_method' => $request->input('payment_method') ?: 'Cash',
                'status' => 'pending_payment',
                'tenant_id' => $tenant_id,
            ]);

            foreach ($itemsData as $row) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'batch_id' => $row['batch_id'],
                    'drug_id' => $row['drug']->id,
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'dosage' => $row['dosage'],
                    'tenant_id' => $tenant_id,
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Prescription Forwarded to Cashier',
                'details' => "Pharmacist forwarded Order #{$transaction->id} (TZS {$totalAmount}) to Cashier. Stock holding preserved.",
                'ip_address' => $request->ip(),
                'tenant_id' => $tenant_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => 'pending_payment',
                'transaction_id' => $transaction->id,
                'total_amount' => $totalAmount,
                'message' => 'Order forwarded to Cashier desk as "Pending Payment". Stock is safe and will be deducted upon cashier approval.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get pending payment queue for Cashier
     */
    public function getPendingPayments(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $orders = Transaction::with(['items.drug', 'pharmacist', 'patient'])
            ->where('tenant_id', $tenant_id)
            ->where('status', 'pending_payment')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Step 2: Cashier receives customer payment and clicks "Approve Payment" & prints receipt.
     */
    public function approvePayment(Request $request, $id)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $transaction = Transaction::where('tenant_id', $tenant_id)->findOrFail($id);

        if ($transaction->status !== 'pending_payment') {
            return response()->json(['success' => false, 'message' => 'Transaction is not awaiting payment.'], 422);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:Cash,Mobile Money,Card,Insurance,Wallet',
        ]);

        if (!PaymentMethod::isMethodEnabled($validated['payment_method'])) {
            return response()->json([
                'success' => false,
                'message' => "Njia ya malipo ya '{$validated['payment_method']}' imezimwa na Super Admin. Tafadhali tumia Cash au njia nyingine inayoruhusiwa.",
            ], 422);
        }

        $transaction->update([
            'payment_method' => $validated['payment_method'],
            'cashier_id' => auth()->id() ?? 1,
            'status' => 'paid',
        ]);

        if ($validated['payment_method'] === 'Insurance') {
            $providerId = $request->input('insurance_provider_id') 
                ?: InsuranceProvider::where('code', $request->input('insurance_provider_code') ?: 'NHIF')->value('id') 
                ?: 1;

            InsuranceClaim::create([
                'transaction_id' => $transaction->id,
                'insurance_provider_id' => $providerId,
                'provider_id' => $providerId,
                'claim_number' => 'CLAIM-' . date('Ymd') . '-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
                'amount' => $transaction->total_amount,
                'total_amount' => $transaction->total_amount,
                'member_number' => $request->input('member_number') ?: ($request->input('card_number') ?: 'N/A'),
                'member_name' => $request->input('member_name') ?: ($transaction->patient?->name ?? 'Insurance Patient'),
                'scheme_name' => $request->input('scheme_name') ?: 'Standard Scheme',
                'authorization_code' => $request->input('authorization_code') ?: ('AUTH-' . strtoupper(substr(md5($transaction->id . time()), 0, 8))),
                'referral_number' => $request->input('referral_number') ?: null,
                'status' => 'Pending',
                'tenant_id' => $tenant_id,
                'notes' => 'Auto-generated from Point of Sale verification'
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'Payment Approved by Cashier',
            'details' => "Cashier approved payment of TZS {$transaction->total_amount} via {$validated['payment_method']} for Order #{$transaction->id}",
            'ip_address' => $request->ip(),
            'tenant_id' => $tenant_id,
        ]);

        $transaction->load(['items.drug', 'customer', 'pharmacist']);

        return response()->json([
            'success' => true,
            'status' => 'paid',
            'transaction_id' => $transaction->id,
            'total_amount' => $transaction->total_amount,
            'payment_method' => $transaction->payment_method,
            'transaction' => $transaction,
            'message' => 'Payment received & approved! Pharmacist notified to release medicines.',
        ]);
    }

    /**
     * Get ready to dispense queue for Pharmacist
     */
    public function getReadyToDispense(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $orders = Transaction::with(['items.drug', 'pharmacist', 'cashier', 'patient'])
            ->where('tenant_id', $tenant_id)
            ->where('status', 'paid')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Get Receipt Details for View & Reprint
     */
    public function getReceiptDetails($id)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $transaction = Transaction::with(['items.drug', 'pharmacist', 'cashier', 'patient', 'customer'])
            ->where('tenant_id', $tenant_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'receipt' => $transaction,
        ]);
    }

    /**
     * Step 3: Pharmacist confirms dispensing & hands medicines to patient.
     * At this exact step, stock is officially deducted from batches & drug stock in strict FEFO/FIFO order.
     * Cashiers are strictly blocked from finalizing dispensation.
     */
    public function confirmDispense(Request $request, $id)
    {
        $activeRole = session('role') ?? auth()->user()?->role?->name ?? 'Manager';
        if ($activeRole === 'Cashier') {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: As a Cashier, you cannot dispense medicines. Only a certified Pharmacist is authorized to finalize dispensing.'
            ], 403);
        }

        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $transaction = Transaction::with('items.drug')->where('tenant_id', $tenant_id)->findOrFail($id);

        if ($transaction->status !== 'paid') {
            return response()->json(['success' => false, 'message' => 'Order must be paid before dispensing.'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($transaction->items as $item) {
                $drug = $item->drug;
                if (!$drug) continue;

                $needed = (int)$item->quantity;
                $matchedDrugIds = $this->getMatchingDrugIds($drug, $tenant_id);

                // Deduct from batches across all matching drug records using FIFO/FEFO
                $batches = $this->getFifoBatchesForDrugs($matchedDrugIds, $tenant_id);

                foreach ($batches as $b) {
                    if ($needed <= 0) break;

                    $take = min($b->quantity, $needed);
                    $b->decrement('quantity', $take);
                    $needed -= $take;
                }

                // If any residual needed (fallback batch)
                if ($needed > 0) {
                    Batch::firstOrCreate(
                        ['drug_id' => $drug->id, 'batch_number' => 'STD-' . date('Ymd')],
                        [
                            'quantity' => 0,
                            'expiry_date' => now()->addYears(2),
                            'tenant_id' => $tenant_id
                        ]
                    );
                }

                // Synchronize quantities on all matched drug records
                foreach ($matchedDrugIds as $mId) {
                    $mDrug = Drug::find($mId);
                    if ($mDrug) {
                        $mDrug->update(['quantity' => $mDrug->batches()->sum('quantity')]);
                    }
                }
            }

            $transaction->update([
                'status' => 'dispensed',
                'dispensed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Medicine Dispensed & Stock Deducted',
                'details' => "Order #{$transaction->id} handed to patient. FIFO stock decremented across batches.",
                'ip_address' => $request->ip(),
                'tenant_id' => $tenant_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => 'dispensed',
                'transaction_id' => $transaction->id,
                'message' => 'Medicines successfully handed to patient. Stock officially deducted from inventory via FIFO.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Direct one-step transaction (Manager / single-operator mode) with FIFO batch allocation
     */
    public function createTransaction(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        // If user is pharmacist and tenant has an active cashier on premise, or explicit send_to_cashier action is requested
        $action = $request->input('action');
        $activeRole = session('role') ?? auth()->user()?->role?->name;

        $hasCashier = User::where('tenant_id', $tenant_id)
            ->whereHas('role', function($q) {
                $q->where('name', 'Cashier');
            })
            ->where(function($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['Inactive', 'Suspended']);
            })
            ->exists();

        if ($action === 'send_to_cashier' || ($activeRole === 'Pharmacist' && $hasCashier)) {
            return $this->sendToCashier($request);
        }

        $items = $this->parseCartItems($request, $tenant_id);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:Cash,Mobile Money,Card,Insurance,Wallet',
            'patient_id' => 'nullable|exists:patients,id',
            'customer_id' => 'nullable|exists:patients,id',
        ]);

        if (!PaymentMethod::isMethodEnabled($validated['payment_method'])) {
            return response()->json([
                'success' => false,
                'message' => "Njia ya malipo ya '{$validated['payment_method']}' imezimwa na Super Admin. Tafadhali tumia Cash au njia nyingine inayoruhusiwa.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsToProcess = [];

            foreach ($items as $item) {
                $drug = Drug::findOrFail($item['drug_id']);
                $matchedDrugIds = $item['matched_drug_ids'] ?? $this->getMatchingDrugIds($drug, $tenant_id);
                $requestedQty = (int)$item['quantity'];

                $availableBatches = $this->getFifoBatchesForDrugs($matchedDrugIds, $tenant_id);
                $availableStock = (int)$availableBatches->sum('quantity');

                if ($availableStock < $requestedQty) {
                    throw new \Exception("Insufficient stock for {$drug->name}. Available: {$availableStock}, requested: {$requestedQty}");
                }

                $price = (float)$drug->price;
                if ($price <= 0) {
                    $altDrug = Drug::whereIn('id', $matchedDrugIds)->where('price', '>', 0)->first();
                    if ($altDrug) $price = (float)$altDrug->price;
                }

                $subtotal = $requestedQty * $price;
                $totalAmount += $subtotal;

                $itemsToProcess[] = [
                    'drug' => $drug,
                    'matched_drug_ids' => $matchedDrugIds,
                    'quantity' => $requestedQty,
                    'price' => $price,
                    'dosage' => $item['dosage'] ?? null,
                ];
            }

            $transaction = Transaction::create([
                'user_id' => auth()->id() ?? 1,
                'customer_id' => $validated['patient_id'] ?? $validated['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'dispensed',
                'dispensed_at' => now(),
                'tenant_id' => $tenant_id,
            ]);

            foreach ($itemsToProcess as $proc) {
                $drug = $proc['drug'];
                $matchedDrugIds = $proc['matched_drug_ids'];
                $needed = $proc['quantity'];

                $batches = $this->getFifoBatchesForDrugs($matchedDrugIds, $tenant_id);

                foreach ($batches as $b) {
                    if ($needed <= 0) break;

                    $take = min($b->quantity, $needed);
                    $b->decrement('quantity', $take);

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'batch_id' => $b->id,
                        'drug_id' => $b->drug_id,
                        'quantity' => $take,
                        'dosage' => $proc['dosage'] ?? null,
                        'price' => $proc['price'],
                        'tenant_id' => $tenant_id,
                    ]);

                    $needed -= $take;
                }

                // If any residual needed (fallback)
                if ($needed > 0) {
                    $fallbackBatch = Batch::firstOrCreate(
                        ['drug_id' => $drug->id, 'batch_number' => 'STD-' . date('Ymd')],
                        ['quantity' => 0, 'expiry_date' => now()->addYears(2), 'tenant_id' => $tenant_id]
                    );
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'batch_id' => $fallbackBatch->id,
                        'drug_id' => $drug->id,
                        'quantity' => $needed,
                        'dosage' => $proc['dosage'] ?? null,
                        'price' => $proc['price'],
                        'tenant_id' => $tenant_id,
                    ]);
                }

                // Synchronize quantities on all matched drug records
                foreach ($matchedDrugIds as $mId) {
                    $mDrug = Drug::find($mId);
                    if ($mDrug) {
                        $mDrug->update(['quantity' => $mDrug->batches()->sum('quantity')]);
                    }
                }
            }

            if ($validated['payment_method'] === 'Insurance') {
                $providerId = $request->input('insurance_provider_id') 
                    ?: InsuranceProvider::where('code', $request->input('insurance_provider_code') ?: 'NHIF')->value('id') 
                    ?: 1;

                InsuranceClaim::create([
                    'transaction_id' => $transaction->id,
                    'insurance_provider_id' => $providerId,
                    'provider_id' => $providerId,
                    'claim_number' => 'CLAIM-' . date('Ymd') . '-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
                    'amount' => $transaction->total_amount,
                    'total_amount' => $transaction->total_amount,
                    'member_number' => $request->input('member_number') ?: ($request->input('card_number') ?: 'N/A'),
                    'member_name' => $request->input('member_name') ?: ($transaction->patient?->name ?? 'Insurance Patient'),
                    'scheme_name' => $request->input('scheme_name') ?: 'Standard Scheme',
                    'authorization_code' => $request->input('authorization_code') ?: ('AUTH-' . strtoupper(substr(md5($transaction->id . time()), 0, 8))),
                    'referral_number' => $request->input('referral_number') ?: null,
                    'status' => 'Pending',
                    'tenant_id' => $tenant_id,
                    'notes' => 'Auto-generated from Point of Sale direct dispensation'
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Direct POS Sale & FIFO Dispense',
                'details' => "Sale #{$transaction->id} completed (TZS {$totalAmount}) via {$validated['payment_method']}. FIFO batches deducted.",
                'ip_address' => $request->ip(),
                'tenant_id' => $tenant_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'total_amount' => $transaction->total_amount,
                'status' => 'dispensed',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
