@extends('layouts.app')

@section('title', 'Rincian Nota Barang Masuk - Asset Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-file-invoice-dollar text-success me-2"></i> Rincian Nota Penerimaan</h2>
            <p class="text-muted small m-0 mt-1">Informasi legalitas invoice vendor dan daftar alokasi fisik unit</p>
        </div>
        <a href="{{ route('barang_masuk.index') }}" class="btn btn-light border fw-semibold rounded-3 text-secondary small">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Log
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card card-premium p-4 bg-white shadow-sm border-start border-primary border-3 h-100">
                <span class="text-uppercase fw-bold text-muted d-block mb-3" style="font-size: 0.72rem; letter-spacing: 0.5px;">Referensi Dokumen</span>
                <table class="table table-sm table-borderless mb-0 style-info" style="font-size: 0.9rem;">
                    <tr><td width="35%" class="text-muted">No. Penerimaan (BM)</td><td>: <b class="text-success">{{ $barangMasuk->no_barang_masuk }}</b></td></tr>
                    <tr><td class="text-muted">No. Invoice Vendor</td><td>: <b class="text-primary"><u>{{ $barangMasuk->no_invoice }}</u></b></td></tr>
                    <tr><td class="text-muted">No. Purchase Order</td><td>: <span>{{ $barangMasuk->no_po ?? '-' }}</span></td></tr>
                    <tr><td class="text-muted">Tanggal Kedatangan</td><td>: <span>{{ date('d F Y', strtotime($barangMasuk->tanggal_masuk)) }}</span></td></tr>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-premium p-4 bg-white shadow-sm border-start border-warning border-3 h-100">
                <span class="text-uppercase fw-bold text-muted d-block mb-3" style="font-size: 0.72rem; letter-spacing: 0.5px;">Asal Mitra Supplier</span>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background-color: rgba(234,179,8,0.1) !important;">
                        <i class="fas fa-handshake fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 1rem;">{{ $barangMasuk->supplier->nama_supplier }}</h6>
                        <small class="text-muted"><i class="fas fa-phone-alt me-1 small"></i> Kontak: {{ $barangMasuk->supplier->kontak ?? '-' }}</small>
                    </div>
                </div>
                <div class="mt-2 p-2 bg-light rounded-3 small text-secondary">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i> <b>Alamat:</b> {{ $barangMasuk->supplier->alamat ?? 'Alamat belum dilengkapi.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="card card-premium overflow-hidden bg-white shadow-sm">
        <div class="px-4 pt-4 pb-2 bg-light border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark m-0"><i class="fas fa-boxes text-secondary me-2"></i>Daftar Fisik Unit Yang Berhasil Didaftarkan</h6>
            <span class="badge bg-success px-3 py-2 rounded-2" style="font-size: 0.78rem;">{{ $barangMasuk->details->count() }} Unit Terverifikasi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="8%">No</th>
                            <th width="15%">Kategori</th>
                            <th width="25%">Nama Model Perangkat</th>
                            <th width="20%">Merek / Tipe</th>
                            <th width="17%">Kode SKU Unit</th>
                            <th width="15%">Nomor Seri Pabrik (S/N)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangMasuk->details as $index => $detail)
                            @if($detail->assetItem)
                                <tr>
                                    <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td><span class="badge-soft-secondary">{{ $detail->assetItem->catalog->kategori ?? '-' }}</span></td>
                                    <td class="fw-bold text-secondary">
                                        {{ $detail->assetItem->catalog->nama ?? 'Unit Tanpa Katalog' }}
                                        <small class="text-muted d-block fw-normal text-truncate" style="max-width: 250px; font-size: 0.75rem;">{{ $detail->assetItem->catalog->spesifikasi ?? '-' }}</small>
                                    </td>
                                    <td>{{ $detail->assetItem->catalog->merk ?? '-' }} <div class="small text-muted" style="font-size: 0.75rem;">{{ $detail->assetItem->catalog->tipe ?? '-' }}</div></td>
                                    <td><b class="text-primary"><i class="fas fa-tag me-1 small opacity-50"></i>{{ $detail->assetItem->sku }}</b></td>
                                    <td><code>{{ $detail->assetItem->serial_number }}</code></td>
                                </tr>
                            @else
                                <tr><td colspan="6" class="text-center text-danger small py-2">Terjadi anomali: Data fisik unit terhapus dari sistem.</td></tr>
                            @endif
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ditemukan item fisik di dalam nota ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection