<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // 1. HALAMAN DAFTAR UTAMA + LIVE FILTER
    public function index(Request $request)
    {
        $query = Supplier::query();

        // LIVE FILTER: Pencarian berdasarkan Nama, Email, atau No. Telepon
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_supplier', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('no_telepon', 'like', "%$search%");
            });
        }

        $suppliers = $query->latest()->get();

        return view('supplier.index', compact('suppliers'));
    }

    // 2. FUNGSI SIMPAN DATA (KITA KEMBALIKAN KE SINI)
    public function store(Request $request)
    {
        Supplier::create($this->validatedSupplier($request));

        return back()->with('success', 'Mitra Supplier baru berhasil terdaftar!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validatedSupplier($request));

        return back()->with('success', 'Data mitra supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if (BarangMasuk::where('supplier_id', $supplier->id)->exists()) {
            return back()->withErrors([
                'supplier' => 'Supplier tidak dapat dihapus karena sudah digunakan pada transaksi barang masuk. Data ini dilindungi agar riwayat transaksi tetap utuh.',
            ]);
        }

        $supplier->delete();

        return back()->with('success', 'Data mitra supplier berhasil dihapus.');
    }

    private function validatedSupplier(Request $request): array
    {
        return $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat_supplier' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
