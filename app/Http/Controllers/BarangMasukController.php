<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\BarangMasuk;
use App\Models\Droppoint;
use App\Models\StockMutation;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BarangMasukController extends Controller
{
    // 1. HALAMAN DAFTAR UTAMA + LIVE FILTER
    public function index(Request $request)
    {
        $query = BarangMasuk::with(['supplier', 'details.assetItem.catalog']);

        // Filter Berdasarkan Tanggal Masuk
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_masuk', $request->tanggal);
        }

        // Filter Bebas (No Invoice, No PO, No BM)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_barang_masuk', 'like', "%$search%")
                    ->orWhere('no_po', 'like', "%$search%")
                    ->orWhere('no_invoice', 'like', "%$search%");
            });
        }

        // Filter Berdasarkan Supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $barangMasuks = $query->latest()->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('barang_masuk.index', compact('barangMasuks', 'suppliers'));
    }

    // 2. HALAMAN FORM INPUT KHUSUS (PINDAH HALAMAN)
    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $catalogs = Asset::orderBy('nama')->get();
        // Ambil data droppoint untuk pilihan lokasi di form barang masuk
        // $droppoints = Droppoint::orderBy('kode_dp', 'asc')->get();

        // Hitung jumlah item yang terdaftar HARI INI untuk generate nomor urut SKU (XXXX)
        $todayCount = AssetItem::whereDate('created_at', today())->count();
        $nextSequence = $todayCount + 1; // Angka urut berikutnya untuk JavaScript

        return view('barang_masuk.create', compact('suppliers', 'catalogs', 'nextSequence'));
    }

    // 3. PROSES SIMPAN MASSAL (FOOTER BUTTON ACTION)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_masuk' => ['required', 'date'],
            'no_barang_masuk' => ['required', 'string', 'max:255', 'unique:barang_masuks,no_barang_masuk'],
            'no_po' => ['nullable', 'string', 'max:255', 'unique:barang_masuks,no_po'],
            'no_invoice' => ['required', 'string', 'max:255', 'unique:barang_masuks,no_invoice'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'asset_id' => ['required', 'array', 'min:1'],
            'asset_id.*' => ['required', 'integer', 'exists:assets,id'],
            'sku' => ['required', 'array', 'min:1'],
            'sku.*' => ['required', 'string', 'max:255', 'distinct', 'unique:asset_items,sku'],
            'serial_number' => ['required', 'array', 'min:1'],
            'serial_number.*' => ['required', 'string', 'max:255', 'distinct', 'unique:asset_items,serial_number'],
        ], [
            'no_barang_masuk.unique' => 'Nomor penerimaan tersebut sudah digunakan. Gunakan nomor yang berbeda.',
            'no_po.unique' => 'Nomor PO tersebut sudah tercatat pada transaksi lain.',
            'no_invoice.unique' => 'Nomor invoice tersebut sudah tercatat. Periksa kembali nomor invoice.',
            'supplier_id.required' => 'Pilih vendor supplier terlebih dahulu.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak ditemukan. Muat ulang halaman dan pilih supplier yang tersedia.',
            'asset_id.required' => 'Tambahkan minimal satu unit ke daftar sebelum menyimpan.',
            'asset_id.min' => 'Tambahkan minimal satu unit ke daftar sebelum menyimpan.',
            'asset_id.*.exists' => 'Salah satu model katalog tidak lagi tersedia. Muat ulang halaman dan pilih ulang model.',
            'sku.*.distinct' => 'Ada kode SKU yang sama pada daftar unit. Hapus unit duplikat sebelum menyimpan.',
            'sku.*.unique' => 'Salah satu kode SKU sudah digunakan. Muat ulang halaman untuk membuat kode baru.',
            'serial_number.*.distinct' => 'Ada nomor seri yang sama pada daftar unit. Hapus unit duplikat sebelum menyimpan.',
            'serial_number.*.unique' => 'Salah satu nomor seri sudah terdaftar pada inventory.',
        ], [
            'tanggal_masuk' => 'tanggal penerimaan',
            'no_barang_masuk' => 'nomor penerimaan',
            'no_po' => 'nomor PO',
            'no_invoice' => 'nomor invoice',
            'supplier_id' => 'supplier',
            'asset_id' => 'model katalog',
            'sku.*' => 'kode SKU unit',
            'serial_number.*' => 'nomor seri unit',
        ]);

        $itemCount = count($validated['sku']);
        if (count($validated['asset_id']) !== $itemCount || count($validated['serial_number']) !== $itemCount) {
            throw ValidationException::withMessages([
                'asset_id' => 'Data model, SKU, dan nomor seri harus memiliki jumlah unit yang sama.',
            ]);
        }

        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        if (! Droppoint::whereKey($warehouseId)->exists()) {
            throw ValidationException::withMessages([
                'warehouse' => 'Lokasi gudang belum tersedia atau konfigurasi INVENTORY_WAREHOUSE_DROPPOINT_ID tidak sesuai. Hubungi administrator sebelum mencatat barang masuk.',
            ]);
        }

        $occurredAt = Carbon::parse($validated['tanggal_masuk'])->startOfDay();

        DB::transaction(function () use ($validated, $warehouseId, $occurredAt): void {
            $nota = BarangMasuk::create([
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'no_barang_masuk' => $validated['no_barang_masuk'],
                'no_po' => $validated['no_po'] ?? null,
                'no_invoice' => $validated['no_invoice'],
                'supplier_id' => $validated['supplier_id'],
            ]);

            foreach ($validated['sku'] as $index => $skuCode) {
                $unit = AssetItem::create([
                    'asset_id' => $validated['asset_id'][$index],
                    'sku' => $skuCode,
                    'serial_number' => $validated['serial_number'][$index],
                    'droppoint_id' => $warehouseId,
                    'status' => 'Ready',
                ]);

                $nota->details()->create(['asset_item_id' => $unit->id]);

                StockMutation::create([
                    'asset_item_id' => $unit->id,
                    'type' => 'receipt',
                    'to_droppoint_id' => $warehouseId,
                    'to_status' => 'Ready',
                    'source_type' => 'barang_masuk',
                    'source_id' => $nota->id,
                    'source_reference' => $nota->no_barang_masuk,
                    'user_id' => auth()->id(),
                    'occurred_at' => $occurredAt,
                ]);
            }
        });

        return redirect()->route('barang_masuk.index')->with('success', 'Batch Transaksi Barang Masuk berhasil disimpan ke dalam sistem!');
    }

    // 4. HALAMAN DETAIL RINCIAN UNIT PER NOTA INVOICE
    public function show($id)
    {
        // Tarik data nota tunggal beserta supplier, detail item fisik, dan katalog spesifikasinya
        $barangMasuk = BarangMasuk::with(['supplier', 'details.assetItem.catalog'])->findOrFail($id);

        return view('barang_masuk.show', compact('barangMasuk'));
    }
}
