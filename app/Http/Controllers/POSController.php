<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use App\Models\SubCategory;
use App\Models\Drug;
use App\Models\Batch;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

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
            'sub_categories' => $subCategories
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return view('pos', $data);
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
            ->get()
            ->map(function($drug) use ($quarantinedBatchIds) {
                $validBatches = $drug->batches
                    ->where('quantity', '>', 0)
                    ->whereNotIn('id', $quarantinedBatchIds)
                    ->filter(function($b) {
                        return $b->expiry_date === null || $b->expiry_date >= now()->startOfDay();
                    })
                    ->sortBy('expiry_date');

                $earliestBatch = $validBatches->first();
                $stock = (int)$validBatches->sum('quantity');
                $batchId = $earliestBatch?->id;

                return [
                    'id' => $drug->id,
                    'drug_id' => $drug->id,
                    'name' => $drug->name,
                    'brand_name' => $drug->brand_name ?? '',
                    'generic_name' => $drug->generic_name ?? '',
                    'dosage_form' => $drug->dosage_form ?? 'Tablet',
                    'barcode' => $drug->barcode ?? '',
                    'category' => $drug->category->name ?? ($drug->category ?? 'POM'),
                    'sub_category' => $drug->subCategory->name ?? null,
                    'price' => (float)$drug->price,
                    'stock' => (int)$stock,
                    'batch_id' => $batchId,
                    'batch_number' => $earliestBatch?->batch_number ?? null,
                    'expiry_date' => $earliestBatch?->expiry_date ? $earliestBatch->expiry_date->format('Y-m-d') : null,
                ];
            })
            ->values();

        return response()->json($drugs);
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

        if ($action === 'checkout' || $request->isMethod('post')) {
            return $this->createTransaction($request);
        }

        return $this->searchDrugs($request);
    }

    public function createTransaction(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        // Support both json array payload and legacy FormData cart string
        if ($request->has('cart') && is_string($request->input('cart'))) {
            $cartItems = json_decode($request->input('cart'), true) ?: [];
            $mappedItems = [];
            foreach ($cartItems as $c) {
                $batchId = $c['batch_id'] ?? null;
                $drugId = $c['drug_id'] ?? $c['id'] ?? null;

                if (!$batchId && $drugId) {
                    $foundBatch = Batch::where('drug_id', $drugId)
                        ->where('quantity', '>', 0)
                        ->where(function($q) {
                            $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay());
                        })
                        ->orderBy('expiry_date')
                        ->first();
                    $batchId = $foundBatch?->id;
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

                if ($batchId) {
                    $mappedItems[] = [
                        'batch_id' => $batchId,
                        'quantity' => (int)($c['qty'] ?? 1),
                    ];
                }
            }

            $request->merge([
                'items' => $mappedItems,
                'patient_id' => $request->input('customer_id') ?: $request->input('patient_id'),
            ]);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:Cash,Mobile Money,Card,Insurance,Wallet',
            'patient_id' => 'nullable|exists:patients,id',
            'discount' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $quarantinedBatchIds = \App\Models\Quarantine::pluck('batch_id')->toArray();
            $totalAmount = 0;
            $itemsToProcess = [];

            foreach ($validated['items'] as $item) {
                $batch = Batch::findOrFail($item['batch_id']);
                $drug = $batch->drug;
                $requestedQty = (int)$item['quantity'];

                // Check total available stock across all valid, non-expired batches for this drug
                $availableStock = Batch::where('drug_id', $drug->id)
                    ->whereNotIn('id', $quarantinedBatchIds)
                    ->where(function($q) {
                        $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay());
                    })
                    ->sum('quantity');

                if ($availableStock < $requestedQty) {
                    throw new \Exception("Insufficient stock for {$drug->name}. Available: {$availableStock}, requested: {$requestedQty}");
                }

                $subtotal = $requestedQty * $drug->price;
                $totalAmount += $subtotal;

                $itemsToProcess[] = [
                    'drug' => $drug,
                    'quantity' => $requestedQty,
                    'dosage' => $item['dosage'] ?? null,
                ];
            }

            $transaction = Transaction::create([
                'user_id' => auth()->id() ?? 1,
                'customer_id' => $validated['patient_id'] ?? $request->input('customer_id') ?? null,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'tenant_id' => $tenant_id,
            ]);

            foreach ($itemsToProcess as $proc) {
                $drug = $proc['drug'];
                $needed = $proc['quantity'];

                // Fetch batches ordered by earliest expiry (FIFO)
                $batches = Batch::where('drug_id', $drug->id)
                    ->where('quantity', '>', 0)
                    ->whereNotIn('id', $quarantinedBatchIds)
                    ->where(function($q) {
                        $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay());
                    })
                    ->orderByRaw('expiry_date IS NULL, expiry_date ASC, id ASC')
                    ->get();

                foreach ($batches as $b) {
                    if ($needed <= 0) break;

                    $take = min($b->quantity, $needed);
                    $b->decrement('quantity', $take);

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'batch_id' => $b->id,
                        'drug_id' => $drug->id,
                        'quantity' => $take,
                        'dosage' => $proc['dosage'] ?? null,
                        'price' => $drug->price,
                        'tenant_id' => $tenant_id,
                    ]);

                    $needed -= $take;
                }

                // Always synchronize drug.quantity with total valid batch stock
                $drug->update(['quantity' => $drug->batches()->sum('quantity')]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'total_amount' => $transaction->total_amount,
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
