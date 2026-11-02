<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $assetCounts = Asset::query()
            ->select('kategori')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('kategori')
            ->pluck('aggregate', 'kategori');
        $unitCounts = DB::table('assets')
            ->join('asset_items', 'assets.id', '=', 'asset_items.asset_id')
            ->select('assets.kategori')
            ->selectRaw('COUNT(asset_items.id) as aggregate')
            ->groupBy('assets.kategori')
            ->pluck('aggregate', 'kategori');
        $categories = AssetCategory::orderBy('name')->get();
        foreach ($categories as $category) {
            $category->setAttribute('catalogs_count', (int) ($assetCounts[$category->name] ?? 0));
            $category->setAttribute('units_count', (int) ($unitCounts[$category->name] ?? 0));
        }

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        AssetCategory::create($this->validatedCategory($request));

        return back()->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function update(Request $request, AssetCategory $category)
    {
        $validated = $this->validatedCategory($request, $category);

        DB::transaction(function () use ($category, $validated): void {
            Asset::where('kategori', $category->name)->update(['kategori' => $validated['name']]);
            $category->update($validated);
        });

        return back()->with('success', 'Kategori aset berhasil diperbarui.');
    }

    public function destroy(AssetCategory $category)
    {
        if (Asset::where('kategori', $category->name)->exists()) {
            return back()->withErrors([
                'category' => 'Kategori tidak dapat dihapus karena masih digunakan oleh katalog aset.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Kategori aset berhasil dihapus.');
    }

    private function validatedCategory(Request $request, ?AssetCategory $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_categories', 'name')->ignore($category?->id),
            ],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori tersebut sudah terdaftar.',
        ]);
    }
}
