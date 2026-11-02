@extends('layouts.app')

@section('title', 'Kategori Aset - VeloAssets')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1"><i class="fa-solid fa-tags text-primary me-2"></i>Kategori Aset</h1>
            <p class="text-muted mb-0">Kelola kategori yang tersedia untuk katalog inventory.</p>
        </div>
        <a href="{{ route('asset.index') }}" class="btn btn-light border"><i class="fa-solid fa-arrow-left me-1"></i> Master Inventory</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="row g-4">
        @if(auth()->user()->canManageData())
            <div class="col-lg-4">
                <div class="card card-premium bg-white p-4">
                    <h2 class="h6 fw-bold text-dark mb-1">Tambah kategori</h2>
                    <p class="small text-muted mb-3">Nama kategori akan tersedia di pilihan Master Inventory.</p>
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <label for="categoryName" class="form-label small fw-semibold">Nama kategori</label>
                        <input id="categoryName" type="text" name="name" class="form-control mb-3" value="{{ old('name') }}" maxlength="255" placeholder="Contoh: Laptop" required>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-1"></i> Tambah kategori</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="{{ auth()->user()->canManageData() ? 'col-lg-8' : 'col-12' }}">
            <div class="card card-premium bg-white overflow-hidden">
                <div class="card-header bg-white px-4 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold text-dark mb-1">Daftar kategori</h2>
                        <small class="text-muted">{{ $categories->count() }} kategori terdaftar</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama kategori</th>
                                <th class="text-center">Model katalog</th>
                                <th class="text-center">Unit fisik</th>
                                @if(auth()->user()->canManageData())
                                    <th class="text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td><span class="badge-soft-secondary">{{ $category->name }}</span></td>
                                    <td class="text-center">{{ number_format($category->catalogs_count) }}</td>
                                    <td class="text-center">{{ number_format($category->units_count) }}</td>
                                    @if(auth()->user()->canManageData())
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-light border text-warning" title="Ubah kategori" data-bs-toggle="modal" data-bs-target="#editCategory{{ $category->id }}">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </button>
                                                @if($category->catalogs_count === 0)
                                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus kategori"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-light border text-muted" title="Kategori masih digunakan oleh katalog" disabled><i class="fa-solid fa-lock"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->canManageData() ? 4 : 3 }}" class="text-center text-muted py-5">
                                        Belum ada kategori aset. Tambahkan kategori pertama untuk digunakan pada katalog.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->canManageData())
        @foreach($categories as $category)
            <div class="modal fade" id="editCategory{{ $category->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-header">
                            <h2 class="modal-title h5 fw-bold">Ubah kategori</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <form action="{{ route('categories.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <label for="editCategoryName{{ $category->id }}" class="form-label small fw-semibold">Nama kategori</label>
                                <input id="editCategoryName{{ $category->id }}" type="text" name="name" class="form-control" value="{{ $category->name }}" maxlength="255" required>
                                @if($category->catalogs_count > 0)
                                    <small class="text-muted d-block mt-2">Perubahan nama juga memperbarui kategori pada {{ $category->catalogs_count }} model katalog.</small>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection
