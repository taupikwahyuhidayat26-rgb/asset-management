<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetItem;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    // 1. HALAMAN UTAMA MASTER INVENTORY (KATALOG)
    public function index(Request $request)
    {
        $query = Asset::withCount('items');
        $categories = AssetCategory::orderBy('name')->get();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%$search%")
                ->orWhere('merk', 'like', "%$search%")
                ->orWhere('tipe', 'like', "%$search%");
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $assets = $query->latest()->get();

        return view('asset.index', compact('assets', 'categories'));
    }

    // 2. PROSES SIMPAN TEMPLATE KATALOG BARU
    public function store(Request $request)
    {
        Asset::create($this->validatedCatalog($request));

        return back()->with('success', 'Model Katalog spesifikasi baru berhasil ditambahkan!');
    }

    public function update(Request $request, Asset $asset)
    {
        $asset->update($this->validatedCatalog($request));

        return back()->with('success', 'Model katalog berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->items()->exists()) {
            return back()->withErrors([
                'asset' => 'Model katalog tidak dapat dihapus karena masih digunakan oleh unit fisik. Pindahkan unit ke model lain terlebih dahulu.',
            ]);
        }

        $asset->delete();

        return back()->with('success', 'Model katalog berhasil dihapus.');
    }

    private function validatedCatalog(Request $request): array
    {
        return $request->validate([
            'kategori' => ['required', 'string', Rule::exists('asset_categories', 'name')],
            'nama' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'spesifikasi' => 'nullable|string|max:5000',
        ]);
    }

    // 3. HALAMAN LAPORAN KARTU STOK (DENGAN PENCARIAN SKU / SERIAL NUMBER)
    public function kartuStok(Request $request, $id = null)
    {
        $catalogs = Asset::orderBy('nama')->get();
        $asset = null;
        $mutasiData = collect();
        $itemQuery = AssetItem::query();
        $hasFilter = $id || $request->filled('search');

        if ($id) {
            $asset = Asset::with('items')->findOrFail($id);
            $itemQuery->where('asset_id', $id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $itemQuery->where(function ($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($hasFilter) {
            $itemIds = $itemQuery->pluck('id');

            if (! $id && $itemIds->isNotEmpty()) {
                $asset = AssetItem::with('catalog')->whereIn('id', $itemIds)->first()?->catalog;
            }

            $mutasiData = StockMutation::with([
                'assetItem.catalog',
                'fromDroppoint',
                'toDroppoint',
                'user',
            ])
                ->whereIn('asset_item_id', $itemIds)
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->get();
        }

        return view('asset.kartu_stok', compact('asset', 'mutasiData', 'catalogs'));
    }
}
