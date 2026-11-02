@extends('layouts.app')

@section('title', 'Detail Barang Keluar - VeloAssets')


@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-file-invoice-dollar text-success me-2"></i> Rincian Surat Jalan</h2>
            <p class="text-muted small m-0 mt-1">Informasi alokasi fisik unit</p>
        </div>
        <a href="{{ route('barang_keluar.index') }}" class="btn btn-light border shadow-sm rounded-3 px-3 py-2 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">No Surat Jalan: <span class="text-primary">{{ $transaksi->kode_transaksi }}</span></h6>
                    
                    <p class="text-muted small mb-1">Tanggal Keluar</p>
                    <p class="fw-bold">{{ \Carbon\Carbon::parse($transaksi->tanggal_keluar)->format('d M Y') }}</p>

                    <p class="text-muted small mb-1">Lokasi Tujuan</p>
                    <p class="fw-bold">{{ $transaksi->droppoint->kode_dp }} - {{ $transaksi->droppoint->nama_dp }}</p>

                    <p class="text-muted small mb-1">Keterangan</p>
                    <p class="fw-bold">{{ $transaksi->keterangan ?? 'Tidak ada keterangan' }}</p>
                </div>
            </div>    
        </div>

        <div class="col-md-8">
            
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom fw-bold">
            <i class="fa-solid fa-boxes-stacked me-2 text-success"></i> Daftar Unit yang Dikeluarkan
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="10%">No</th>
                        <th width="30%">SKU</th>
                        <th width="25%">S/N</th>
                        <th width="35%">Nama / Model Barang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi->details as $detail)
                    <tr>
                        <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $detail->assetItem->sku ?? '-' }}</td>
                        <td class="text-muted font-monospace">{{ $detail->assetItem->serial_number ?? '-' }}</td>
                        <td class="fw-semibold">{{ $detail->assetItem->catalog->spesifikasi ?? $detail->assetItem->nama_barang ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection