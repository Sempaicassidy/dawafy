<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Supplier;
use App\Models\Batch;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $categories = Category::all();
        $subCategories = SubCategory::all();

        // Retrieve suppliers: for this tenant or parent tenant, fallback to all suppliers if empty
        $suppliersQuery = Supplier::query();
        if ($tenant_id) {
            $tenant = \App\Models\Tenant::find($tenant_id);
            $parent_id = $tenant?->parent_id;
            $suppliersQuery->where(function($q) use ($tenant_id, $parent_id) {
                $q->where('tenant_id', $tenant_id);
                if ($parent_id) {
                    $q->orWhere('tenant_id', $parent_id);
                }
            });
        }
        $suppliers = $suppliersQuery->orderBy('name', 'asc')->get();

        if ($suppliers->isEmpty()) {
            $suppliers = Supplier::orderBy('name', 'asc')->get();
        }

        // Active drugs with stock
        $drugs = Drug::with(['category', 'subCategory', 'batches'])
            ->where('tenant_id', $tenant_id)
            ->whereHas('batches', function($query) {
                $query->where('quantity', '>', 0)
                      ->where(function($q) {
                          $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now());
                      });
            })
            ->get()
            ->map(function($drug) {
                $activeBatch = $drug->batches
                    ->where('quantity', '>', 0)
                    ->where(function($b) {
                        return $b->expiry_date === null || $b->expiry_date >= now();
                    })
                    ->sortBy('expiry_date')
                    ->first();

                return [
                    'id' => $drug->id,
                    'name' => $drug->name,
                    'brand_name' => $drug->brand_name,
                    'reg_no' => $drug->reg_no,
                    'manufacturer' => $drug->manufacturer,
                    'strength' => $drug->strength,
                    'dosage_form' => $drug->dosage_form ?? 'Tablet',
                    'barcode' => $drug->barcode,
                    'category' => $drug->category->name ?? null,
                    'sub_category' => $drug->subCategory->name ?? null,
                    'total_stock' => $drug->stock,
                    'price' => $drug->price,
                    'nearest_expiry' => $activeBatch->expiry_date ?? null,
                    'batch_number' => $activeBatch->batch_number ?? null,
                    'batch_id' => $activeBatch->id ?? null,
                ];
            });

        // Out of stock drugs
        $outOfStockDrugs = Drug::with(['category', 'subCategory', 'batches'])
            ->where('tenant_id', $tenant_id)
            ->get()
            ->filter(fn($drug) => $drug->stock <= 0)
            ->map(function($drug) {
                return [
                    'id' => $drug->id,
                    'name' => $drug->name,
                    'brand_name' => $drug->brand_name,
                    'strength' => $drug->strength,
                    'dosage_form' => $drug->dosage_form ?? 'Tablet',
                    'category' => $drug->category->name ?? null,
                    'sub_category' => $drug->subCategory->name ?? null,
                    'total_stock' => $drug->stock,
                    'price' => $drug->price,
                    'batch_number' => $drug->batches->first()?->batch_number ?? null,
                ];
            });

        // Expired drugs
        $expiredDrugs = Drug::with(['category', 'subCategory', 'batches'])
            ->where('tenant_id', $tenant_id)
            ->whereHas('batches', function($query) {
                $query->where('expiry_date', '<', now());
            })
            ->get()
            ->map(function($drug) {
                $expiredBatch = $drug->batches
                    ->where('expiry_date', '<', now())
                    ->sortByDesc('expiry_date')
                    ->first();

                return [
                    'id' => $drug->id,
                    'name' => $drug->name,
                    'brand_name' => $drug->brand_name,
                    'strength' => $drug->strength,
                    'dosage_form' => $drug->dosage_form ?? 'Tablet',
                    'category' => $drug->category->name ?? null,
                    'sub_category' => $drug->subCategory->name ?? null,
                    'total_stock' => $expiredBatch?->quantity ?? 0,
                    'price' => $drug->price,
                    'nearest_expiry' => $expiredBatch?->expiry_date ?? null,
                    'batch_number' => $expiredBatch?->batch_number ?? null,
                    'batch_id' => $expiredBatch?->id ?? null,
                ];
            });

        $data = [
            'drugs' => $drugs,
            'out_of_stock_drugs' => $outOfStockDrugs,
            'expired_drugs' => $expiredDrugs,
            'categories' => $categories,
            'sub_categories' => $subCategories,
            'suppliers' => $suppliers
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return view('inventory', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'brand_name' => 'nullable|string',
            'reg_no' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'manufacture_lic_no' => 'nullable|string',
            'strength' => 'nullable|string',
            'dosage_form' => 'nullable|string',
            'barcode' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        $drug = Drug::create([
            'name' => $validated['name'],
            'brand_name' => $validated['brand_name'] ?? null,
            'reg_no' => $validated['reg_no'] ?? null,
            'manufacturer' => $validated['manufacturer'] ?? null,
            'manufacture_lic_no' => $validated['manufacture_lic_no'] ?? null,
            'strength' => $validated['strength'] ?? null,
            'dosage_form' => $validated['dosage_form'] ?? 'Tablet',
            'barcode' => $validated['barcode'] ?? null,
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'tenant_id' => $tenant_id,
        ]);

        $batchNumber = 'B-' . date('Ymd') . rand(10, 99);

        Batch::create([
            'drug_id' => $drug->id,
            'batch_number' => $batchNumber,
            'quantity' => $validated['quantity'],
            'manufacture_date' => $validated['manufacture_date'] ?? null,
            'expiry_date' => $validated['expiry_date'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'tenant_id' => $tenant_id,
        ]);

        $drug->update(['quantity' => $drug->batches()->sum('quantity')]);

        return redirect()->route('inventory.index')->with('success', 'Drug added successfully');
    }

    public function update(Request $request, $id = null)
    {
        $drugId = $id ?? $request->input('drug_id');
        $drug = Drug::findOrFail($drugId);

        $validated = $request->validate([
            'name' => 'required|string',
            'brand_name' => 'nullable|string',
            'strength' => 'nullable|string',
            'dosage_form' => 'nullable|string',
            'barcode' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'price' => 'required|numeric',
        ]);

        $drug->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Drug updated successfully');
    }

    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'required|date',
            'batch_number' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;
        $batchNumber = $validated['batch_number'] ?? ('B-' . date('Ymd') . rand(100, 999));

        Batch::create([
            'drug_id' => $validated['drug_id'],
            'batch_number' => $batchNumber,
            'quantity' => $validated['quantity'],
            'expiry_date' => $validated['expiry_date'],
            'supplier_id' => $validated['supplier_id'] ?? null,
            'tenant_id' => $tenant_id,
        ]);

        $drug = Drug::findOrFail($validated['drug_id']);
        $drug->update(['quantity' => $drug->batches()->sum('quantity')]);

        return redirect()->route('inventory.index')->with('success', 'Stock batch added successfully');
    }
}
