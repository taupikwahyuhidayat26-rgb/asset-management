<?php

namespace App\Http\Controllers;

use App\Models\Droppoint;
use Illuminate\Http\Request;

class DroppointController extends Controller
{
    public function index()
    {
        // KUNCI PERBAIKAN: Ganti latest() menjadi orderBy('kode_dp', 'asc')
        $droppoints = Droppoint::orderBy('kode_dp', 'asc')->paginate(10);
        
        return view('droppoint.index', compact('droppoints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_dp' => 'required|string|max:50|unique:droppoint,kode_dp',
            'nama_dp' => 'required|string|max:255',
            'nama_cps' => 'nullable|string|max:255',
            'posisi'  => 'required|string|max:255',
            'rm' => 'nullable|string|max:255',
            'spv' => 'nullable|string|max:255',
            'area_cover' => 'nullable|string|max:255',
            'status_dp' => 'required|in:Aktif,Nonaktif',
        ]);

        Droppoint::create($validated);

        return redirect()->back()->with('success', 'Lokasi Drop Point berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $dp = Droppoint::findOrFail($id);
        $validated = $request->validate([
            'kode_dp' => 'required|string|max:50|unique:droppoint,kode_dp,' . $dp->id,
            'nama_dp' => 'required|string|max:255',
            'nama_cps' => 'nullable|string|max:255',
            'posisi'  => 'required|string|max:255',
            'rm' => 'nullable|string|max:255',
            'spv' => 'nullable|string|max:255',
            'area_cover' => 'nullable|string|max:255',
            'status_dp' => 'required|in:Aktif,Nonaktif',
        ]);

        $dp->update($validated);

        return redirect()->back()->with('success', 'Data Drop Point berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $dp = Droppoint::findOrFail($id);
        $dp->delete();

        return redirect()->back()->with('success', 'Drop Point berhasil dihapus!');
    }
}