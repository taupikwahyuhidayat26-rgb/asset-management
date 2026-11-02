@extends('layouts.app')

@section('title', 'Transaksi Barang Keluar - VeloAssets')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Transaksi Barang Keluar</h3>
            <p class="text-muted small m-0 mt-1">Scan/Input S/N barang untuk memindahkan dari Gudang IT.</p>
        </div>
        <a href="{{ route('barang_keluar.index') }}" class="btn btn-light border shadow-sm rounded-3 px-3 py-2 fw-semibold" style="font-size: 0.9rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('barang_keluar.store') }}" method="POST" id="formBarangKeluar">
        @csrf
        
        <div class="card bg-white shadow-sm mb-4 border-0" style="border-radius: 16px;">
            <div class="card-header bg-white py-3 px-4 border-bottom fw-bold text-dark">
                <i class="fa-solid fa-file-invoice me-2 text-primary"></i> Informasi Transaksi
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-bold text-muted">Kode Transaksi</label>
                        <input type="text" name="kode_transaksi" class="form-control bg-light fw-bold text-primary" value="{{ $kodeOtomatis }}" readonly style="border-radius: 10px; border: 1px solid #e2e8f0; height: 40px;">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-bold text-muted">Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; height: 40px;">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-bold text-muted">Drop Point Tujuan</label>
                        <select name="droppoint_id" class="form-select" required style="border-radius: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; height: 40px;">
                            <option value="">-- Pilih Drop Point Tujuan --</option>
                            @foreach($droppoints as $dp)
                                <option value="{{ $dp->id }}">{{ $dp->kode_dp }} - {{ $dp->nama_dp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-0">
                        <label class="form-label small fw-bold text-muted">Keterangan / Nama Pembawa (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Masukkan catatan pengiriman..." style="border-radius: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.9rem;"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-white shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-barcode me-2 text-primary"></i> Scan / Input Unit Fisik
                </span>
            </div>
            
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Cari berdasarkan Serial Number (S/N) atau SKU</label>
                    <div class="input-group input-group-lg shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary px-4"><i class="fa-solid fa-barcode fs-5"></i></span>
                        <input type="text" id="inputScan" class="form-control border-start-0 ps-0 fw-bold" placeholder="Klik disini & arahkan scanner / Ketik manual lalu tekan Enter" autofocus style="font-size: 1rem;">
                    </div>
                    <small class="text-muted mt-2 d-block">* Pastikan kursor berada di dalam kotak teks di atas sebelum melakukan scan barcode.</small>
                </div>

                <div class="table-responsive border rounded-3" style="min-height: 200px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="text-center" width="10%">Aksi</th>
                                <th width="20%">SKU</th>
                                <th width="30%">Serial Number</th>
                                <th width="40%">Nama / Model Barang</th>
                            </tr>
                        </thead>
                        <tbody id="stagingTableBody">
                            <tr id="emptyState">
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="fa-solid fa-clipboard-list fs-2 text-secondary opacity-30"></i></div>
                                    <span class="fw-medium text-secondary">Daftar unit masih kosong. Silakan scan barang terlebih dahulu.</span>
                                </td>
                            </tr>
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-top p-4 text-end">
                <button type="submit" class="btn btn-primary fw-bold px-5 py-2 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; border: none;">
                    <i class="fa-solid fa-paper-plane me-1"></i> Simpan Transaksi Keluar
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Ambil data database dari controller dan ubah menjadi objek JavaScript
            const availableAssets = @json($assetItems);
            
            const inputScan = document.getElementById('inputScan');
            const stagingTableBody = document.getElementById('stagingTableBody');
            const emptyState = document.getElementById('emptyState');
            
            // Array untuk menyimpan ID barang yang sudah masuk list
            let selectedItemIds = [];

            // 2. Fungsi mendengarkan inputan (Saat menekan tombol ENTER)
            inputScan.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Cegah halaman reload karena form submit otomatis
                    
                    let keyword = this.value.trim().toLowerCase();
                    if (!keyword) return;

                    // 3. Cari barang di dalam array availableAssets berdasarkan S/N atau SKU
                    let foundItem = availableAssets.find(item => 
                        (item.serial_number && item.serial_number.toLowerCase() === keyword) ||
                        (item.sku && item.sku.toLowerCase() === keyword)
                    );

                    if (foundItem) {
                        // 4. Cek apakah barang sudah di-scan sebelumnya
                        if (selectedItemIds.includes(foundItem.id)) {
                            alert('⚠️ Barang dengan S/N atau SKU ini sudah ada di dalam daftar!');
                        } else {
                            // Masukkan ke array penampung
                            selectedItemIds.push(foundItem.id);
                            
                            // Sembunyikan tulisan "Kosong"
                            emptyState.style.display = 'none';

                            // 5. Buat baris tabel baru secara dinamis
                            let row = document.createElement('tr');
                            row.id = `row-${foundItem.id}`;
                            
                            let sku = foundItem.sku ? foundItem.sku : '-';
                            let namaBarang = foundItem.nama_barang ? foundItem.nama_barang : (foundItem.catalog ? foundItem.catalog.spesifikasi : '-');
                            let sn = foundItem.serial_number ? foundItem.serial_number : '-';

                            row.innerHTML = `
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${foundItem.id})" title="Hapus dari daftar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <input type="hidden" name="asset_items[]" value="${foundItem.id}">
                                </td>
                                <td class="fw-bold text-secondary">${sku}</td>
                                <td class="text-muted font-monospace">${sn}</td>
                                <td class="fw-semibold text-dark">${namaBarang}</td>
                            `;
                            
                            // Tambahkan baris ke dalam tabel
                            stagingTableBody.appendChild(row);
                        }
                    } else {
                        alert('❌ Barang tidak ditemukan! Pastikan barang tersebut ada di Gudang IT atau periksa ketikan Anda.');
                    }

                    // Bersihkan form input agar siap untuk di-scan barang berikutnya
                    this.value = '';
                }
            });

            // 6. Fungsi untuk menghapus baris jika ada salah scan
            window.removeItem = function(id) {
                // Hapus dari array
                selectedItemIds = selectedItemIds.filter(itemId => itemId !== id);
                
                // Hapus elemen HTML baris tersebut
                document.getElementById(`row-${id}`).remove();
                
                // Tampilkan kembali tulisan "Kosong" jika tidak ada barang yang tersisa
                if(selectedItemIds.length === 0) {
                    emptyState.style.display = 'table-row';
                }
            };
        });
    </script>
@endsection