@extends('layouts.app')

@section('title', 'Detail Item - Unit Fisik Asset')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-barcode text-primary me-2"></i> Detail Item Unit Fisik</h2>
            <p class="text-muted small m-0 mt-1">Daftar penomoran SKU dan pelacakan posisi fisik aset perangkat di lapangan</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-premium p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom border-light">
            <div>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-2" style="font-size: 0.8rem;">
                    <i class="fas fa-info-circle me-1 text-primary"></i> Penambahan unit hanya dapat dilakukan melalui menu Transaksi Barang Masuk.
                </span>
            </div>
            <div>
                <span class="text-muted small fw-medium">Total Fisik Unit: <b class="text-dark">{{ $assetItems->total() }} Item</b></span>
            </div>
        </div>

        <form action="{{ route('asset_item.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-secondary">Cari SKU / Serial Number / Model</label>
                <input type="text" class="form-control bg-light py-2" name="search" value="{{ request('search') }}" placeholder="Masukkan kode unik barang...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Status Operasional</label>
                <select class="form-select form-control bg-light" name="status">
                    <option value="">-- Semua --</option>
                    <option value="Ready" {{ request('status') == 'Ready' ? 'selected' : '' }}>Ready</option>
                    <option value="Deployed" {{ request('status') == 'Deployed' ? 'selected' : '' }}>Deployed</option>
                    <option value="Maintenance" {{ request('status') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="Broken" {{ request('status') == 'Broken' ? 'selected' : '' }}>Broken</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100 fw-bold py-2 rounded-3 text-white border-0" style="background: #475569;"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                @if(auth()->user()->canManageData())
                    <a href="{{ route('asset_item.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-print me-1"></i> Cetak Daftar
                    </a>
                    <a href="{{ route('asset_item.export', request()->query()) }}" class="btn btn-success text-white">
                        <i class="fa-solid fa-file-excel me-1"></i> Export Excel (CSV)
                    </a>
                @endif
            </div>
        </form>

        @if(auth()->user()->canManageData())
            <div class="border-top pt-3 mt-3">
                <form action="{{ route('asset_item.import') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12 col-lg-5">
                        <label for="asset-item-import-file" class="form-label small fw-bold text-secondary">Import Detail Item (CSV)</label>
                        <input id="asset-item-import-file" type="file" name="file" accept=".csv,text/csv" class="form-control" required>
                    </div>
                    <div class="col-12 col-lg-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-file-arrow-up me-1"></i>Import Data
                        </button>
                    </div>
                    <div class="col-12 col-lg-auto">
                        <a href="{{ route('asset_item.import.template') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-download me-1"></i>Download Template CSV
                        </a>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">
                            Hanya menambahkan data baru. Kolom wajib: <code>nama_model</code>, <code>sku</code>, <code>kode_dp</code>, dan <code>status</code>.
                            Status yang diterima: Ready, Deployed, Maintenance, atau Broken. Maksimal 5.000 baris.
                        </small>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <div class="card card-premium overflow-hidden bg-white shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: calc(100vh - 570px); overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-secondary sticky-top" style="z-index: 1;">
                        <tr>
                            <th width="15%">SKU / Kode Unit</th>
                            <th width="25%">Nama Perangkat</th>
                            <th width="15%">Nomor Seri (S/N)</th>
                            <th width="15%">Posisi Lokasi Kini</th>
                            <th width="12%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assetItems as $item)
                            <tr>
                                <td class="fw-bold text-dark"><i class="fas fa-tag text-muted me-1 small"></i>{{ $item->sku }}</td>
                                <td>
                                    <span class="d-block fw-bold text-secondary">{{ $item->catalog->nama ?? 'Tanpa Model' }}</span>
                                    <small class="badge bg-light text-dark border px-2 py-0" style="font-size:0.7rem;">{{ $item->catalog->kategori ?? '-' }}</small>
                                </td>
                                <td><code>{{ $item->serial_number ?? '-' }}</code></td>
                                <td><i class="fas fa-map-marker-alt text-danger me-1 opacity-60"></i><b>{{ $item->droppoint->kode_dp ?? '-' }}</b></td>
                                <td class="text-center"><b>{{ $item->status }}</b></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        
                                        <a href="{{ $item->asset_id ? route('asset.kartu_stok', $item->asset_id) : '#' }}" 
                                           class="btn btn-sm btn-light border text-primary p-1 px-2 rounded-2" 
                                           title="{{ $item->asset_id ? 'Lihat Kartu Stok' : 'Katalog tidak ditemukan' }}">
                                            <i class="fas fa-clipboard-list small"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-sm btn-light border text-primary p-1 px-2 rounded-2" title="Foto Unit ({{ $item->images->count() }})" data-bs-toggle="modal" data-bs-target="#assetItemImages{{ $item->id }}">
                                            <i class="fas fa-images small"></i> {{ $item->images->count() }}
                                        </button>
                                        @if(auth()->user()->canManageData())
                                            <button type="button" class="btn btn-sm btn-light border text-warning p-1 px-2 rounded-2" title="Edit Unit" data-bs-toggle="modal" data-bs-target="#editAssetItem{{ $item->id }}">
                                                <i class="fas fa-pencil-alt small"></i>
                                            </button>
                                            <form action="{{ route('asset_item.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus unit {{ $item->sku }}? Unit yang memiliki riwayat transaksi atau mutasi tidak dapat dihapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger p-1 px-2 rounded-2" title="Hapus Unit">
                                                    <i class="fas fa-trash-alt small"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('asset_items.print_barcode', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                                <i class="fa-solid fa-barcode"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada unit fisik asset yang terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <small class="text-muted fw-medium">
                Menampilkan <b class="text-dark">{{ $assetItems->firstItem() ?? 0 }}</b> 
                sampai <b class="text-dark">{{ $assetItems->lastItem() ?? 0 }}</b> 
                dari total <b class="text-dark">{{ $assetItems->total() }}</b> Unit Fisik
            </small>
            
            <div class="pagination-wrapper m-0">
                {{ $assetItems->links() }}
            </div>
        </div>
        </div>
    </div>

    @if(auth()->user()->canManageData())
    @foreach($assetItems as $item)
        <div class="modal fade" id="editAssetItem{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('asset_item.update', $item) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Detail Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="asset_id{{ $item->id }}" class="form-label">Model Perangkat</label>
                                    <select id="asset_id{{ $item->id }}" name="asset_id" class="form-select" required>
                                        @foreach($catalogs as $catalog)
                                            <option value="{{ $catalog->id }}" @selected($item->asset_id === $catalog->id)>{{ $catalog->nama }} ({{ $catalog->kategori }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="sku{{ $item->id }}" class="form-label">SKU / Kode Unit</label>
                                    <input id="sku{{ $item->id }}" type="text" name="sku" value="{{ $item->sku }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="serial{{ $item->id }}" class="form-label">Nomor Seri (S/N)</label>
                                    <input id="serial{{ $item->id }}" type="text" name="serial_number" value="{{ $item->serial_number }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="droppoint{{ $item->id }}" class="form-label">Drop Point</label>
                                    <select id="droppoint{{ $item->id }}" name="droppoint_id" class="form-select" required>
                                        @foreach($droppoints as $droppoint)
                                            <option value="{{ $droppoint->id }}" @selected($item->droppoint_id === $droppoint->id)>{{ $droppoint->kode_dp }} - {{ $droppoint->nama_dp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status{{ $item->id }}" class="form-label">Status Operasional</label>
                                    <select id="status{{ $item->id }}" name="status" class="form-select" required>
                                        @foreach(['Ready', 'Deployed', 'Maintenance', 'Broken'] as $status)
                                            <option value="{{ $status }}" @selected($item->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="keterangan{{ $item->id }}" class="form-label">Keterangan</label>
                                    <textarea id="keterangan{{ $item->id }}" name="keterangan" class="form-control" rows="3">{{ $item->keterangan }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="mutation_note{{ $item->id }}" class="form-label">Alasan Perubahan Status / Lokasi</label>
                                    <textarea id="mutation_note{{ $item->id }}" name="mutation_note" class="form-control" rows="2" maxlength="1000" placeholder="Opsional, dicatat pada riwayat mutasi"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    @endif

    @foreach($assetItems as $item)
        <div class="modal fade" id="assetItemImages{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold">Foto Unit</h5>
                            <small class="text-muted">{{ $item->sku }} · {{ $item->catalog->nama ?? 'Tanpa Model' }}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @if($item->images->isNotEmpty())
                            <div class="row g-3 mb-4">
                                @foreach($item->images as $image)
                                    <div class="col-6 col-md-4">
                                        <div class="border rounded-3 p-2 h-100">
                                            <a href="{{ route('asset_item.images.show', [$item, $image]) }}" target="_blank" rel="noopener">
                                                <img src="{{ route('asset_item.images.show', [$item, $image]) }}" alt="Foto {{ $item->sku }}" class="img-fluid rounded w-100" style="height: 160px; object-fit: cover;">
                                            </a>
                                            @if(auth()->user()->canManageData())
                                                <form action="{{ route('asset_item.images.destroy', [$item, $image]) }}" method="POST" class="mt-2" onsubmit="return confirm('Hapus foto ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="fas fa-trash-alt me-1"></i>Hapus Foto
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-4">Belum ada foto untuk unit ini.</p>
                        @endif

                        @if(auth()->user()->canManageData())
                            <form action="{{ route('asset_item.images.store', $item) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="image_modal" value="{{ $item->id }}">
                                <label for="images{{ $item->id }}" class="form-label fw-semibold">Tambah foto</label>
                                <input id="images{{ $item->id }}" type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required>
                                <div class="form-text">Pilih hingga 10 foto JPG, PNG, atau WebP. Maksimal 5 MB per foto.</div>
                                @if(old('image_modal') == $item->id)
                                    @foreach($errors->all() as $error)
                                        <div class="text-danger small mt-1">{{ $error }}</div>
                                    @endforeach
                                @endif
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="fas fa-upload me-1"></i>Unggah Foto
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if(auth()->user()->canManageData() && old('image_modal'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('assetItemImages{{ old('image_modal') }}');
                if (modal) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endif
    
@endsection