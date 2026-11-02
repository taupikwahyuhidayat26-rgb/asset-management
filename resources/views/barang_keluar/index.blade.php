@extends('layouts.app')

@section('title', 'Data Barang Keluar - VeloAssets')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-arrow-alt-circle-up text-success me-2"></i>Transaksi Barang Keluar</h3>
            <p class="text-muted small m-0 mt-1">Riwayat mutasi aset dari Gudang Utama ke Drop Point.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-premium p-4 bg-white mb-4 shadow-sm"">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom border-light">
            @if(auth()->user()->canManageData())
                <a href="{{ route('barang_keluar.create') }}" class="btn btn-primary fw-bold px-3 py-2 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); font-size: 0.88rem;">
                    <i class="fas fa-plus-circle me-2"></i> Buat Surat Jalan
                </a>
            @endif
            <span class="text-muted small fw-medium">Total Surat Jalan: <b class="text-dark">{{ $barangKeluars->count() }} Dokumen</b></span>
        </div>

        <form action="{{ route('barang_keluar.index') }}" method="GET">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1">Cari Transaksi / Lokasi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Ketik OUT-2026... atau nama drop point" value="{{ request('search') }}" style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control bg-light" value="{{ request('start_date') }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control bg-light" value="{{ request('end_date') }}" style="border-radius: 10px;">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="border-radius: 10px; height: 38px;">Cari</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-premium bg-white shadow-sm overflow-hidden" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3" width="5%">No</th>
                            <th width="15%">Tanggal</th>
                            <th width="20%">Kode Transaksi</th>
                            <th width="25%">Tujuan (Drop Point)</th>
                            <th width="15%" class="text-center">Total Item</th>
                            <th class="text-center pe-4" width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangKeluars as $bk)
                        <tr>
                            <td class="ps-4 text-muted">{{ ($barangKeluars->currentPage() - 1) * $barangKeluars->perPage() + $loop->iteration }}</td>
                            <td class="fw-medium">{{ \Carbon\Carbon::parse($bk->tanggal_keluar)->format('d M Y') }}</td>
                            <td class="fw-bold text-primary">{{ $bk->kode_transaksi }}</td>
                            <td class="fw-semibold">{{ $bk->droppoint->nama_dp ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill px-3">{{ $bk->details->count() }} Unit</span>
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('barang_keluar.show', $bk->id) }}" class="btn btn-sm btn-light border text-primary px-3 rounded-pill">
                                    <i class="fa-solid fa-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada transaksi barang keluar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            {{ $barangKeluars->links() }}
        </div>
    </div>
@endsection