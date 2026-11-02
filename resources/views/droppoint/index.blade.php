@extends('layouts.app')

@section('title', 'Master Drop Point - VeloAssets')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark m-0">Master Data Lokasi</h3>
            <p class="text-muted small m-0 mt-1">Kelola daftar Drop Point dan titik distribusi aset.</p>
        </div>
        @if(auth()->user()->canManageData())
            <button class="btn btn-primary shadow-sm rounded-3 px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fa-solid fa-location-dot me-1"></i> Tambah Drop Point
            </button>
        @endif
    </div>
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-premium bg-white shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="fw-bold text-dark m-0">Daftar Drop Point Tersedia</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 58vh; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-secondary sticky-top" style="z-index: 10;">
                        <tr>
                            <th class="ps-4 py-3" width="5%">No</th>
                            <th width="15%">Kode DP</th>
                            <th width="25%">Nama Drop Point</th>
                            <th width="20%">Nama CPS</th>
                            <th width="15%">RM / SPV</th>
                            <th width="15%">Area Cover</th>
                            <th width="10%">Status</th>
                            <th class="text-center pe-4" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($droppoints as $dp)
                        <tr>
                            <td class="ps-4 text-muted">{{ ($droppoints->currentPage() - 1) * $droppoints->perPage() + $loop->iteration }}</td>
                            <td class="fw-bold text-primary">{{ $dp->kode_dp }}</td>
                            <td class="fw-semibold text-dark">{{ $dp->nama_dp }}</td>
                            <td class="text-secondary small italic"><i class="fa-solid fa-user-tie me-1 opacity-50"></i>{{ $dp->nama_cps ?? '-' }}</td>
                            <td class="small">{{ $dp->rm ?: '-' }} / {{ $dp->spv ?: '-' }}</td>
                            <td>{{ $dp->area_cover ?: $dp->posisi }}</td>
                            <td><span class="badge {{ $dp->status_dp === 'Aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $dp->status_dp ?? 'Aktif' }}</span></td>
                            <td class="text-center pe-4">
                                @if(auth()->user()->canManageData())
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border text-primary px-2" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $dp->id }}" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('droppoint.destroy', $dp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light border text-danger px-2" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>

                        @if(auth()->user()->canManageData())
                        <div class="modal fade" id="modalEdit{{ $dp->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                    <div class="modal-header border-bottom-0 pt-4 px-4">
                                        <h5 class="fw-bold m-0">Edit Drop Point</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    @endif
                                    <form action="{{ route('droppoint.update', $dp->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body px-4 pb-4">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Kode DP</label>
                                                <input type="text" name="kode_dp" class="form-control" value="{{ $dp->kode_dp }}" required style="border-radius: 10px; background-color: #f8fafc;">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Nama Drop Point</label>
                                                <input type="text" name="nama_dp" class="form-control" value="{{ $dp->nama_dp }}" required style="border-radius: 10px; background-color: #f8fafc;">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Nama CPS (Contact Person)</label>
                                                <input type="text" name="nama_cps" class="form-control" value="{{ $dp->nama_cps }}" style="border-radius: 10px; background-color: #f8fafc;">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label small fw-bold text-muted">Posisi / Lokasi</label>
                                                <input type="text" name="posisi" class="form-control" value="{{ $dp->posisi }}" required style="border-radius: 10px; background-color: #f8fafc;">
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">RM</label><input type="text" name="rm" class="form-control" value="{{ $dp->rm }}"></div>
                                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">SPV</label><input type="text" name="spv" class="form-control" value="{{ $dp->spv }}"></div>
                                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Area Cover</label><input type="text" name="area_cover" class="form-control" value="{{ $dp->area_cover }}"></div>
                                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Status DP</label><select name="status_dp" class="form-select"><option value="Aktif" @selected(($dp->status_dp ?? 'Aktif') === 'Aktif')>Aktif</option><option value="Nonaktif" @selected($dp->status_dp === 'Nonaktif')>Nonaktif</option></select></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="border-radius: 12px;">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Belum ada data lokasi drop point.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted fw-medium">Halaman {{ $droppoints->currentPage() }} dari {{ $droppoints->lastPage() }}</small>
            <div class="pagination-sm">{{ $droppoints->links() }}</div>
        </div>
    </div>

    @if(auth()->user()->canManageData())
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold m-0">Drop Point Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('droppoint.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pb-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Kode DP</label>
                            <input type="text" name="kode_dp" class="form-control" placeholder="Contoh: DP-JKT-01" required style="border-radius: 10px; background-color: #f8fafc;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nama Drop Point</label>
                            <input type="text" name="nama_dp" class="form-control" placeholder="Contoh: Kantor Cabang Sudirman" required style="border-radius: 10px; background-color: #f8fafc;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nama CPS (Contact Person)</label>
                            <input type="text" name="nama_cps" class="form-control" placeholder="Nama Penanggung Jawab" style="border-radius: 10px; background-color: #f8fafc;">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted">Posisi / Lokasi</label>
                            <input type="text" name="posisi" class="form-control" placeholder="Contoh: Lantai 2, Ruang IT" required style="border-radius: 10px; background-color: #f8fafc;">
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">RM</label><input type="text" name="rm" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">SPV</label><input type="text" name="spv" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Area Cover</label><input type="text" name="area_cover" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Status DP</label><select name="status_dp" class="form-select"><option value="Aktif">Aktif</option><option value="Nonaktif">Nonaktif</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" style="border-radius: 12px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; border:none;">Simpan Lokasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection