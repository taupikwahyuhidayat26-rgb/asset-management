@extends('layouts.app')

@section('title', 'Vendor Supplier - Asset Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-handshake text-primary me-2"></i> Mitra Vendor & Supplier</h2>
            <p class="text-muted small m-0 mt-1">Kelola direktori legalitas, kontak, dan alamat perusahaan rekanan pengadaan barang</p>
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

    <div class="card card-premium p-4 bg-white mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom border-light">
            @if(auth()->user()->canManageData())
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary fw-bold px-3 py-2 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); font-size: 0.88rem;" data-bs-toggle="modal" data-bs-target="#modalTambahSupplier">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Vendor Baru
                </button>
            </div>
            @endif
            
            <div>
                <span class="text-muted small fw-medium">Total Mitra Terdaftar: <b class="text-dark">{{ $suppliers->count() }} Perusahaan</b></span>
            </div>
        </div>

        <form action="{{ route('supplier.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label small fw-bold text-secondary">Pencarian Cepat Vendor</label>
                <div class="input-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="fas fa-search small"></i></span>
                    <input type="text" class="form-control border-0 bg-light py-2" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama vendor, email, atau nomor telepon kantor...">
                </div>
            </div>
            
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100 fw-bold py-2 rounded-3 border-0 text-white" style="background: #475569; font-size: 0.88rem;">
                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('supplier.index') }}" class="btn btn-light border p-2 rounded-3" title="Reset Pencarian">
                        <i class="fas fa-sync-alt text-muted"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card card-premium overflow-hidden bg-white shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="8%">ID Mitra</th>
                            <th width="27%">Nama Perusahaan / Vendor</th>
                            <th width="18%">No. Telepon Kantor</th>
                            <th width="20%">Surat Elektronik (Email)</th>
                            <th width="22%">Alamat Operasional</th>
                            <th width="5%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="fw-bold text-dark">#{{ $supplier->id }}</td>
                                <td class="fw-bold text-secondary">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2 text-primary" style="width: 32px; height: 32px;">
                                            <i class="fas fa-building small"></i>
                                        </div>
                                        <span>{{ $supplier->nama_supplier }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($supplier->no_telepon)
                                        <i class="fas fa-phone-alt text-muted me-1 small"></i> <b>{{ $supplier->no_telepon }}</b>
                                    @else
                                        <span class="text-muted small italic">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->email)
                                        <i class="fas fa-envelope text-muted me-1 small"></i> <a href="mailto:{{ $supplier->email }}" class="text-decoration-none text-muted">{{ $supplier->email }}</a>
                                    @else
                                        <span class="text-muted small italic">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->alamat_supplier)
                                        <small class="text-secondary d-block text-truncate" style="max-width: 250px;" title="{{ $supplier->alamat_supplier }}">
                                            <i class="fas fa-map-marker-alt text-danger me-1 small opacity-70"></i>{{ $supplier->alamat_supplier }}
                                        </small>
                                    @else
                                        <span class="text-muted small italic">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(auth()->user()->canManageData())
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-light border text-warning p-1 px-2 rounded-2" title="Edit Data Vendor" data-bs-toggle="modal" data-bs-target="#editSupplier{{ $supplier->id }}">
                                            <i class="fas fa-pencil-alt small"></i>
                                        </button>
                                        <form action="{{ route('supplier.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus supplier {{ $supplier->nama_supplier }}? Supplier yang sudah dipakai transaksi tidak dapat dihapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger p-1 px-2 rounded-2" title="Hapus Data Vendor">
                                                <i class="fas fa-trash-alt small"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fs-3 d-block mb-2 opacity-40"></i> Tidak ada data mitra rekanan yang cocok dengan kata kunci tersebut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(auth()->user()->canManageData())
    <div class="modal fade" id="modalTambahSupplier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom border-light px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Registrasi Rekanan Vendor Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('supplier.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Nama Perusahaan / Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_supplier" required placeholder="Contoh: PT. Central Computindo" style="border-radius: 10px;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-secondary">No. Telepon Kantor</label>
                                <input type="text" class="form-control" name="no_telepon" placeholder="Contoh: 021-889xxxxx" style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-secondary">Surat Elektronik (Email)</label>
                                <input type="email" class="form-control" name="email" placeholder="Contoh: info@vendor.com" style="border-radius: 10px;">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary">Alamat Kantor Operasional</label>
                            <textarea class="form-control" name="alamat_supplier" rows="3" placeholder="Ketik nama jalan, gedung, blok, kota kantor supplier..." style="border-radius: 10px;"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top border-light px-4 py-3 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light border fw-semibold rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border:none;">
                            Simpan Rekanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->canManageData())
        @foreach($suppliers as $supplier)
            <div class="modal fade" id="editSupplier{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-header border-bottom border-light px-4 py-3">
                            <h5 class="modal-title fw-bold text-dark"><i class="fas fa-pencil-alt text-warning me-2"></i>Edit Mitra Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <form action="{{ route('supplier.update', $supplier) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label for="namaSupplier{{ $supplier->id }}" class="form-label small fw-bold text-secondary">Nama Perusahaan / Supplier <span class="text-danger">*</span></label>
                                    <input id="namaSupplier{{ $supplier->id }}" type="text" class="form-control" name="nama_supplier" value="{{ $supplier->nama_supplier }}" required maxlength="255">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="teleponSupplier{{ $supplier->id }}" class="form-label small fw-bold text-secondary">No. Telepon Kantor</label>
                                        <input id="teleponSupplier{{ $supplier->id }}" type="text" class="form-control" name="no_telepon" value="{{ $supplier->no_telepon }}" maxlength="255">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emailSupplier{{ $supplier->id }}" class="form-label small fw-bold text-secondary">Email</label>
                                        <input id="emailSupplier{{ $supplier->id }}" type="email" class="form-control" name="email" value="{{ $supplier->email }}" maxlength="255">
                                    </div>
                                </div>
                                <div>
                                    <label for="alamatSupplier{{ $supplier->id }}" class="form-label small fw-bold text-secondary">Alamat Kantor Operasional</label>
                                    <textarea id="alamatSupplier{{ $supplier->id }}" class="form-control" name="alamat_supplier" rows="3" maxlength="255">{{ $supplier->alamat_supplier }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top border-light px-4 py-3 bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-light border fw-semibold rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection