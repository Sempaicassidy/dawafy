<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $main_categories = Category::orderBy('name', 'asc')->get();

        $sub_categories = SubCategory::with('category')
            ->withCount('drugs')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($sc) {
                return [
                    'id' => $sc->id,
                    'name' => $sc->name,
                    'description' => $sc->description ?? '',
                    'category_id' => $sc->category_id,
                    'main_category_name' => $sc->category->name ?? null,
                    'drug_count' => $sc->drugs_count ?? 0,
                ];
            })
            ->toArray();

        if (request()->wantsJson()) {
            return response()->json([
                'main_categories' => $main_categories,
                'sub_categories' => $sub_categories
            ]);
        }

        return view('sub_categories', compact('main_categories', 'sub_categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        SubCategory::create($validated);

        return redirect()->route('sub_categories.index')->with('success', 'Sub-category created successfully.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $subCategory = SubCategory::findOrFail($validated['id']);
        $subCategory->update($validated);

        return redirect()->route('sub_categories.index')->with('success', 'Sub-category updated successfully.');
    }

    public function destroy($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->delete();

        return redirect()->route('sub_categories.index')->with('success', 'Sub-category deleted successfully.');
    }
}
