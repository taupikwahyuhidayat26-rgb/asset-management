@extends('layouts.app')

@section('title', 'Laporan Kartu Stok - Asset Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-clipboard-list text-primary me-2"></i> Laporan Kartu Stok</h2>
            <p class="text-muted small m-0 mt-1">Histori mutasi logistik keluar masuk barang berdasarkan spesifikasi atau kode unik unit</p>
        </div>
    </div>

    <div class="card card-premium p-4 bg-white mb-4 shadow-sm">
        <form action="" method="GET" class="row g-3 align-items-end" id="formKartuStok">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-secondary">Saring Berdasarkan Model Katalog</label>
                <select class="form-select form-control fw-bold" id="dropdownPilihKatalog" style="border-radius:10px;">
                    <option value="">-- Pilihan Rumpun Spesifikasi --</option>
                    @foreach($catalogs as $cat)
                        <option value="{{ $cat->id }}" {{ isset($asset) && $asset->id == $cat->id && !request()->filled('search') ? 'selected' : '' }}>
                            [{{ $cat->kategori }}] {{ $cat->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-5">
                <label class="form-label small fw-bold text-secondary">Cari Kode SKU atau Tembak Barcode Serial Number (S/N)</label>
                <div class="input-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="fas fa-barcode"></i></span>
                    <input type="text" class="form-control border-0 bg-light py-2 fw-bold text-primary" name="search" id="inputSearchSkuSn" value="{{ request('search') }}" placeholder="Masukkan SKU / Scan S/N unit di sini...">
                </div>
            </div>
            
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3 border-0" style="background:#4f46e5;"><i class="fas fa-search"></i> Cari</button>
                @if(request()->filled('search') || request()->route('id'))
                    <a href="{{ route('asset.kartu_stok') }}" class="btn btn-light border p-2 rounded-3" title="Reset Laporan"><i class="fas fa-sync-alt text-muted"></i></a>
                @endif
            </div>
        </form>
    </div>

    @if($mutasiData->isNotEmpty())
        @if($asset)
            <div class="card card-premium p-4 bg-white mb-4 shadow-sm border-start border-primary border-3">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block uppercase fw-bold" style="font-size:0.7rem;">Kategori rumpun</small>
                        <span class="badge bg-secondary px-3 py-1 mt-1">{{ $asset->kategori }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block uppercase fw-bold" style="font-size:0.7rem;">Nama Spesifikasi Model</small>
                        <b class="text-secondary d-block mt-1">{{ $asset->nama }}</b>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted d-block uppercase fw-bold" style="font-size:0.7rem;">Merek & Seri Resmi</small>
                        <small class="text-dark d-block mt-1 fw-semibold">{{ $asset->merk ?? '-' }} ({{ $asset->tipe ?? '-' }})</small>
                    </div>
                </div>
            </div>
        @endif

        <div class="card card-premium overflow-hidden bg-white shadow-sm">
            <div class="px-4 pt-4 pb-2 bg-light border-bottom">
                <h6 class="fw-bold text-dark m-0"><i class="fas fa-history text-secondary me-2"></i>Riwayat Mutasi Unit</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Unit</th>
                                <th>Jenis Mutasi</th>
                                <th>Dokumen</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Petugas / Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasiData as $log)
                                @php
                                    $movementLabels = [
                                        'receipt' => ['Barang Masuk', 'success', 'fa-sign-in-alt'],
                                        'issue' => ['Barang Keluar', 'primary', 'fa-sign-out-alt'],
                                        'return' => ['Barang Kembali', 'info', 'fa-rotate-left'],
                                        'transfer' => ['Transfer Drop Point', 'warning', 'fa-right-left'],
                                        'adjustment' => ['Penyesuaian', 'warning', 'fa-pen'],
                                    ];
                                    [$movementLabel, $movementColor, $movementIcon] = $movementLabels[$log->type] ?? ['Mutasi', 'secondary', 'fa-arrows-alt'];
                                    $item = $log->assetItem;
                                @endphp
                                <tr>
                                    <td class="fw-bold text-secondary">{{ $log->occurred_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="d-block fw-bold text-primary">{{ $item->sku }}</span>
                                        <small class="text-muted">{{ $item->serial_number ?? 'Tanpa S/N' }} · {{ $item->catalog->nama ?? 'Tanpa Model' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $movementColor }} bg-opacity-10 text-{{ $movementColor }} px-2 py-1 rounded-2">
                                            <i class="fas {{ $movementIcon }} me-1"></i>{{ $movementLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $log->source_reference ?? '-' }}</td>
                                    <td>
                                        {{ $log->fromDroppoint->kode_dp ?? 'Awal' }}
                                        <i class="fas fa-arrow-right text-muted mx-1"></i>
                                        {{ $log->toDroppoint->kode_dp ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $log->from_status ?? 'Awal' }}
                                        <i class="fas fa-arrow-right text-muted mx-1"></i>
                                        {{ $log->to_status ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="d-block">{{ $log->user->name ?? 'Sistem / riwayat lama' }}</span>
                                        @if($log->notes)<small class="text-muted">{{ $log->notes }}</small>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card card-premium p-5 text-center bg-white shadow-sm text-muted">
            <i class="fas fa-search-minus fs-1 mb-3 text-warning opacity-40"></i>
            <h5 class="fw-bold text-dark">Belum Ada Riwayat Mutasi</h5>
            <p class="small text-muted mx-auto mb-0" style="max-width: 450px;">Pilih model katalog atau cari SKU / Serial Number unit untuk melihat riwayat penerimaan, pengeluaran, dan penyesuaian lokasi maupun status.</p>
        </div>
    @endif

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdown = document.getElementById('dropdownPilihKatalog');
        const inputSearch = document.getElementById('inputSearchSkuSn');
        
        // KONTROL 1: Jika dropdown dirubah, langsung redirect bersihkan query search text
        dropdown.addEventListener('change', function() {
            const idKatalog = this.value;
            if (idKatalog) {
                window.location.href = `/asset/kartu-stok/${idKatalog}`;
            } else {
                window.location.href = `/asset/kartu-stok`;
            }
        });

        // KONTROL 2: Otomatis standby-kan kursor di kotak search agar admin bisa langsung scan barcode
        inputSearch.focus();
    });
</script>
@endsection