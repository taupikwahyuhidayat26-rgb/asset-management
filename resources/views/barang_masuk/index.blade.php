@extends('layouts.app')

@section('title', 'Log Barang Masuk - Asset Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-arrow-alt-circle-down text-success me-2"></i> Transaksi Barang Masuk</h2>
            <p class="text-muted small m-0 mt-1">Daftar arsip nota penerimaan aset elektronik masuk dari vendor</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-premium p-4 bg-white mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom border-light">
            @if(auth()->user()->canManageData())
                <a href="{{ route('barang_masuk.create') }}" class="btn btn-primary fw-bold px-3 py-2 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); font-size: 0.88rem;">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Penerimaan Baru
                </a>
            @endif
            <span class="text-muted small fw-medium">Total Dokumen Nota: <b class="text-dark">{{ $barangMasuks->count() }} Dokumen</b></span>
        </div>

        <form action="{{ route('barang_masuk.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Filter Tanggal</label>
                <input type="date" class="form-control bg-light text-secondary" name="tanggal" value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-secondary">Arsip Nomor Dokumen</label>
                <input type="text" class="form-control bg-light" name="search" value="{{ request('search') }}" placeholder="Ketik No. Invoice, No. PO, atau No. BM...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Supplier</label>
                <select class="form-select form-control bg-light" name="supplier_id">
                    <option value="">-- Semua Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100 fw-bold py-2 rounded-3 text-white border-0" style="background: #475569;"><i class="fas fa-filter me-1"></i> Saring</button>
            </div>
        </form>
    </div>

    <div class="card card-premium overflow-hidden bg-white shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="20%">Tanggal / No. BM</th>
                            <th width="30%">Dokumen Referensi Notasi</th>
                            <th width="25%">Asal Mitra Supplier</th>
                            <th width="13%" class="text-center">Total Muatan</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangMasuks as $bm)
                            <tr>
                                <td>
                                    <span class="d-block fw-bold text-dark" style="font-size:0.85rem;">{{ date('d M Y', strtotime($bm->tanggal_masuk)) }}</span>
                                    <small class="text-success fw-bold">{{ $bm->no_barang_masuk }}</small>
                                </td>
                                <td>
                                    <div class="small"><b>PO:</b> {{ $bm->no_po ?? '-' }}</div>
                                    <div class="small text-primary fw-medium"><b>INV:</b> <u>{{ $bm->no_invoice }}</u></div>
                                </td>
                                <td class="fw-semibold text-secondary" style="font-size: 0.85rem;">{{ $bm->supplier->nama_supplier }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3 py-1" style="font-size: 0.78rem; font-weight: 600;">
                                        {{ $bm->details->count() }} Item
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('barang_masuk.show', $bm->id) }}" class="btn btn-sm btn-light border text-primary fw-bold py-1 px-3 rounded-2" style="font-size: 0.8rem;">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-folder-open d-block fs-3 mb-2 opacity-40"></i> Belum ada rekaman data transaksi barang masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection