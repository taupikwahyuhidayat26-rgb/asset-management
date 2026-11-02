<?php

namespace App\Http\Controllers;

use App\Models\AssetItem;
use App\Models\BarangKeluar;
use App\Models\Droppoint;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BarangKeluarController extends Controller
{
    // 1. Tampilkan Halaman Utama (Index)
    // Jangan lupa pastikan ada 'use Illuminate\Http\Request;' di bagian atas file

    public function index(Request $request)
    {
        // 1. Inisialisasi query dasar beserta relasinya
        $query = BarangKeluar::with('droppoint');

        // 2. Logika Filter Pencarian (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Mencari berdasarkan Kode Transaksi
                $q->where('kode_transaksi', 'like', "%$search%")
                  // Atau mencari berdasarkan Nama / Kode Drop Point di tabel relasi
                    ->orWhereHas('droppoint', function ($dp) use ($search) {
                        $dp->where('nama_dp', 'like', "%$search%")
                            ->orWhere('kode_dp', 'like', "%$search%");
                    });
            });
        }

        // 3. Logika Filter Rentang Tanggal (Date Range)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Filter berdasarkan rentang kolom 'tanggal_keluar'
            $query->whereBetween('tanggal_keluar', [$request->start_date, $request->end_date]);
        }

        // 4. Eksekusi query dan ambil datanya dengan paginasi
        $barangKeluars = $query->latest()->paginate(10);

        return view('barang_keluar.index', compact('barangKeluars'));
    }

    // 2. Tampilkan Form Input (Create)
    public function create()
    {
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);

        // 1. Ambil data Drop Point tujuan (selain Gudang IT)
        $droppoints = Droppoint::where('id', '!=', $warehouseId)->orderBy('kode_dp', 'asc')->get();

        // 2. Hanya unit siap pakai di gudang yang dapat dikeluarkan.
        $assetItems = AssetItem::with('catalog')
            ->where('droppoint_id', $warehouseId)
            ->where('status', 'Ready')
            ->get();

        // 3. Logika Kode Transaksi: ITSJ-YYYYMMDDXXXX (Reset per tahun)
        $currentYear = now()->format('Y'); // Ambil tahun saat ini (contoh: 2026)
        $todayDate = now()->format('Ymd'); // Contoh: 20260607

        // Hitung ada berapa transaksi di TAHUN ini
        $lastTransaction = BarangKeluar::whereYear('created_at', $currentYear)->count();

        // Format urutan menjadi 4 digit (0001, 0002, dst)
        $sequence = str_pad($lastTransaction + 1, 4, '0', STR_PAD_LEFT);

        // Gabungkan semuanya
        $kodeOtomatis = 'ITSJ-'.$todayDate.$sequence;

        return view('barang_keluar.create', compact('droppoints', 'assetItems', 'kodeOtomatis'));
    }

    // 3. Simpan Transaksi (Store)
    public function store(Request $request)
    {
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $validated = $request->validate([
            'kode_transaksi' => 'required|unique:barang_keluars',
            'tanggal_keluar' => 'required|date',
            'droppoint_id' => ['required', 'exists:droppoint,id', Rule::notIn([$warehouseId])],
            'asset_items' => 'required|array|min:1',
            'asset_items.*' => 'required|integer|distinct|exists:asset_items,id',
            'keterangan' => 'nullable|string|max:2000',
        ]);

        $occurredAt = Carbon::parse($validated['tanggal_keluar'])->startOfDay();

        DB::transaction(function () use ($validated, $warehouseId, $occurredAt): void {
            $units = AssetItem::query()
                ->whereIn('id', $validated['asset_items'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($units->count() !== count($validated['asset_items'])) {
                throw ValidationException::withMessages([
                    'asset_items' => 'Sebagian unit tidak ditemukan. Muat ulang halaman dan coba kembali.',
                ]);
            }

            foreach ($units as $unit) {
                if ((int) $unit->droppoint_id !== $warehouseId || $unit->status !== 'Ready') {
                    throw ValidationException::withMessages([
                        'asset_items' => "Unit {$unit->sku} tidak lagi tersedia di gudang atau statusnya bukan Ready.",
                    ]);
                }
            }

            $barangKeluar = BarangKeluar::create([
                'kode_transaksi' => $validated['kode_transaksi'],
                'tanggal_keluar' => $validated['tanggal_keluar'],
                'droppoint_id' => $validated['droppoint_id'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            foreach ($units as $unit) {
                $barangKeluar->details()->create([
                    'asset_item_id' => $unit->id,
                ]);

                $unit->update([
                    'droppoint_id' => $validated['droppoint_id'],
                    'status' => 'Deployed',
                ]);

                StockMutation::create([
                    'asset_item_id' => $unit->id,
                    'type' => 'issue',
                    'from_droppoint_id' => $warehouseId,
                    'to_droppoint_id' => $validated['droppoint_id'],
                    'from_status' => 'Ready',
                    'to_status' => 'Deployed',
                    'source_type' => 'barang_keluar',
                    'source_id' => $barangKeluar->id,
                    'source_reference' => $barangKeluar->kode_transaksi,
                    'notes' => $barangKeluar->keterangan,
                    'user_id' => auth()->id(),
                    'occurred_at' => $occurredAt,
                ]);
            }
        });

        return redirect()->route('barang_keluar.index')->with('success', 'Transaksi Barang Keluar berhasil diproses!');
    }

    // 4. Tampilkan Detail Transaksi (Show)
    public function show($id)
    {
        $transaksi = BarangKeluar::with(['droppoint', 'details.assetItem.catalog'])->findOrFail($id);

        return view('barang_keluar.show', compact('transaksi'));
    }
}
