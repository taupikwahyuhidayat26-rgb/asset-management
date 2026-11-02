@extends('layouts.app')

@section('title', 'Master Inventory - Katalog Asset')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-boxes text-primary me-2"></i> Master Inventory (Katalog)</h2>
            <p class="text-muted small m-0 mt-1">Kelola spesifikasi, merek, dan model cetakan perangkat elekronik</p>
        </div>
        <a href="{{ route('categories.index') }}" class="btn btn-light border fw-semibold">
            <i class="fa-solid fa-tags text-primary me-1"></i> Kelola Kategori
        </a>
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
            @if(auth()->user()->canManageData())
                <button type="button" class="btn btn-primary fw-bold px-3 py-2 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); font-size: 0.88rem;" data-bs-toggle="modal" data-bs-target="#modalTambahKatalog">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Spesifikasi Model
                </button>
            @endif
            <span class="text-muted small fw-medium">Total Model Terdaftar: <b class="text-dark">{{ $assets->count() }} Model</b></span>
        </div>

        <form action="{{ route('asset.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-secondary">Cari Nama Model / Merek</label>
                <input type="text" class="form-control bg-light py-2" name="search" value="{{ request('search') }}" placeholder="Ketik nama aset, merek, atau tipe...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Kategori</label>
                <select class="form-select form-control bg-light" name="kategori">
                    <option value="">-- Semua --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->name }}" @selected(request('kategori') === $category->name)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100 fw-bold py-2 rounded-3 text-white border-0" style="background: #475569;"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="card card-premium overflow-hidden bg-white shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0" style="width:100%;">
                    <thead>
                        <tr>
                            <th width="8%">ID Model</th>
                            <th width="15%">Kategori</th>
                            <th width="25%">Nama Model Katalog</th>
                            <th width="15%">Merek</th>
                            <th width="15%">Tipe / Seri</th>
                            <th width="12%" class="text-center">Jumlah Unit Fisik</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $cat)
                            <tr>
                                <td class="fw-bold text-dark">#{{ $cat->id }}</td>
                                <td><span class="badge-soft-secondary">{{ $cat->kategori }}</span></td>
                                <td class="fw-bold text-secondary">
                                    {{ $cat->nama }} 
                                    <small class="text-muted d-block fw-normal text-truncate" style="max-width:250px;">{{ $cat->spesifikasi ?? '-' }}</small>
                                </td>
                                <td>{{ $cat->merk ?? '-' }}</td>
                                <td><code>{{ $cat->tipe ?? '-' }}</code></td>
                                <td class="text-center"><span class="badge bg-primary rounded-pill px-3 py-1">{{ $cat->items_count }} Unit</span></td>
                                <td class="text-center">
                                    @if(auth()->user()->canManageData())
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-light border text-warning p-1 px-2 rounded-2" title="Edit Model" data-bs-toggle="modal" data-bs-target="#editKatalog{{ $cat->id }}">
                                            <i class="fas fa-pencil-alt small"></i>
                                        </button>
                                        @if($cat->items_count === 0)
                                            <form action="{{ route('asset.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus model katalog {{ $cat->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger p-1 px-2 rounded-2" title="Hapus Model">
                                                    <i class="fas fa-trash-alt small"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-light border text-muted p-1 px-2 rounded-2" title="Model tidak dapat dihapus karena masih memiliki {{ $cat->items_count }} unit" disabled>
                                                <i class="fas fa-lock small"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada spesifikasi katalog model.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(auth()->user()->canManageData())
    @foreach($assets as $cat)
    <div class="modal fade" id="editKatalog{{ $cat->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-light px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-pencil-alt text-warning me-2"></i>Edit Model Katalog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('asset.update', $cat) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="kategori{{ $cat->id }}" class="form-label small fw-bold text-secondary">Kategori Asset</label>
                            <select id="kategori{{ $cat->id }}" class="form-select" name="kategori" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}" @selected($cat->kategori === $category->name)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nama{{ $cat->id }}" class="form-label small fw-bold text-secondary">Nama Model Katalog</label>
                            <input id="nama{{ $cat->id }}" type="text" class="form-control" name="nama" value="{{ $cat->nama }}" maxlength="255" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="merk{{ $cat->id }}" class="form-label small fw-bold text-secondary">Merek</label>
                                <input id="merk{{ $cat->id }}" type="text" class="form-control" name="merk" value="{{ $cat->merk }}" maxlength="255">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="tipe{{ $cat->id }}" class="form-label small fw-bold text-secondary">Tipe / Seri</label>
                                <input id="tipe{{ $cat->id }}" type="text" class="form-control" name="tipe" value="{{ $cat->tipe }}" maxlength="255">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="spesifikasi{{ $cat->id }}" class="form-label small fw-bold text-secondary">Spesifikasi Detail Hardware</label>
                            <textarea id="spesifikasi{{ $cat->id }}" class="form-control" name="spesifikasi" rows="3" maxlength="5000">{{ $cat->spesifikasi }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-light px-4 py-3">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <div class="modal fade" id="modalTambahKatalog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-light px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Buat Model Spesifikasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('asset.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Kategori Asset</label>
                            <select class="form-select form-control" name="kategori" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}" @selected(old('kategori') === $category->name)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Nama Model Katalog</label>
                            <input type="text" class="form-control" name="nama" required placeholder="Contoh: PC Server Core i5, CCTV Dome Outdoor">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label small fw-bold text-secondary">Merek</label><input type="text" class="form-control" name="merk" placeholder="Asus / Lenovo"></div>
                            <div class="col-6 mb-3"><label class="form-label small fw-bold text-secondary">Tipe / Seri</label><input type="text" class="form-control" name="tipe" placeholder="ThinkPad L13"></div>
                        </div>
                        <div class="mb-2"><label class="form-label small fw-bold text-secondary">Spesifikasi Detail Hardware</label><textarea class="form-control" name="spesifikasi" rows="3" placeholder="RAM 16GB, SSD 512GB, Resolusi 4MP..."></textarea></div>
                    </div>
                    <div class="modal-footer border-light px-4 py-3 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light border fw-semibold px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border:none;">Simpan Katalog</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection