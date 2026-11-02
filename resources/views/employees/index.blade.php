@extends('layouts.app')

@section('title', 'Master Karyawan - VeloAssets')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold text-dark m-0">Master Karyawan</h3>
                <p class="text-muted small m-0 mt-1">Kelola data karyawan dan informasi sistem.</p>
            </div>
            @if(auth()->user()->canManageData())
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
                    <i class="fa-solid fa-plus me-1"></i>Tambah Karyawan
                </button>
            @endif
        </div>
        <form action="{{ route('employees.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label for="employee-search" class="form-label small fw-semibold mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input
                        type="search"
                        id="employee-search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Cari no. karyawan, nama, email, atau HP"
                    >
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label for="employee-category" class="form-label small fw-semibold mb-1">Kategori</label>
                <select id="employee-category" name="kategori" class="form-select">
                    <option value="">Semua status</option>
                    <option value="Aktif" @selected(($filters['kategori'] ?? '') === 'Aktif')>Aktif</option>
                    <option value="Resign" @selected(($filters['kategori'] ?? '') === 'Resign')>Resign</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="employee-droppoint" class="form-label small fw-semibold mb-1">Filter Drop Point</label>
                <select id="employee-droppoint" name="droppoint_id" class="form-select">
                    <option value="">Semua Drop Point</option>
                    @foreach($droppoints as $dp)
                        <option value="{{ $dp->id }}" @selected((string) ($filters['droppoint_id'] ?? '') === (string) $dp->id)>
                            {{ $dp->kode_dp }} - {{ $dp->nama_dp }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-light border" title="Reset filter" aria-label="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Periksa kembali data yang dimasukkan:</div>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if(auth()->user()->canManageData())
        <div class="modal fade" id="modalTambahKaryawan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('employees.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="form_context" value="create">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Karyawan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                @foreach($fields as $name => [$label, $inputType, $required])
                                    <div class="col-md-6 col-lg-4">
                                        <label for="new_{{ $name }}" class="form-label fw-semibold">
                                            {{ $label }}@if($required) <span class="text-danger">*</span>@endif
                                        </label>
                                        <input
                                            type="{{ $inputType }}"
                                            id="new_{{ $name }}"
                                            name="{{ $name }}"
                                            value="{{ old($name) }}"
                                            class="form-control @error($name) is-invalid @enderror"
                                            @if($required) required @endif
                                        >
                                        @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach

                                <div class="col-md-6 col-lg-4">
                                    <label for="new_status_kerja" class="form-label fw-semibold">Status Kerja <span class="text-danger">*</span></label>
                                    <select id="new_status_kerja" name="status_kerja" class="form-select @error('status_kerja') is-invalid @enderror" required>
                                        <option value="Aktif" @selected(old('status_kerja', 'Aktif') === 'Aktif')>Aktif</option>
                                        <option value="Resign" @selected(old('status_kerja') === 'Resign')>Resign</option>
                                    </select>
                                    @error('status_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="new_droppoint_id" class="form-label fw-semibold">Drop Point</label>
                                    <select id="new_droppoint_id" name="droppoint_id" class="form-select @error('droppoint_id') is-invalid @enderror">
                                        <option value="">- Pilih Drop Point -</option>
                                        @foreach($droppoints as $dp)
                                            <option value="{{ $dp->id }}" @selected((string) old('droppoint_id') === (string) $dp->id)>
                                                {{ $dp->kode_dp }} - {{ $dp->nama_dp }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('droppoint_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="new_id_sebelumnya" class="form-label fw-semibold">ID Sebelumnya</label>
                                    <input type="text" id="new_id_sebelumnya" name="id_sebelumnya" value="{{ old('id_sebelumnya') }}" class="form-control @error('id_sebelumnya') is-invalid @enderror">
                                    @error('id_sebelumnya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="new_nama_id_sebelumnya" class="form-label fw-semibold">Nama ID Sebelumnya</label>
                                    <input type="text" id="new_nama_id_sebelumnya" name="nama_id_sebelumnya" value="{{ old('nama_id_sebelumnya') }}" class="form-control @error('nama_id_sebelumnya') is-invalid @enderror">
                                    @error('nama_id_sebelumnya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="alert alert-info py-2 mt-3 mb-0 small">
                                Permintaan pembuatan ID baru akan dibuat otomatis dengan status Menunggu.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Simpan Karyawan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card card-premium bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No Karyawan</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Drop Point</th>
                        <th>Status Kerja</th>
                        <th>No. HP</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $employee->no_karyawan }}</td>
                            <td>{{ $employee->nama_karyawan }}</td>
                            <td>{{ collect([$employee->posisi_1, $employee->posisi_2])->filter()->implode(' / ') ?: '-' }}</td>
                            <td>{{ $employee->droppoint?->kode_dp ?? '-' }}</td>
                            <td><span class="badge {{ $employee->status_kerja === 'Aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $employee->status_kerja }}</span></td>
                            <td>{{ $employee->no_hp ?: '-' }}</td>
                            <td class="text-end pe-4">
                                @if(auth()->user()->canManageData())
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $employee->id }}">Edit</button>
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @if(auth()->user()->canManageData())
                        <div class="modal fade" id="modalEdit{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="{{ route('employees.update', $employee) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title">Edit Karyawan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                @foreach($fields as $name => [$label, $inputType, $required])
                                                    @php
                                                        $value = $employee->{$name};
                                                        if ($inputType === 'date' && $value) $value = $value->format('Y-m-d');
                                                    @endphp
                                                    <div class="col-md-6">
                                                        <label class="form-label">{{ $label }}</label>
                                                        <input type="{{ $inputType }}" name="{{ $name }}" value="{{ $value }}" class="form-control" @if($required) required @endif>
                                                    </div>
                                                @endforeach
                                                <div class="col-md-6">
                                                    <label class="form-label">Status Kerja</label>
                                                    <select name="status_kerja" class="form-select" required>
                                                        <option value="Aktif" @selected($employee->status_kerja === 'Aktif')>Aktif</option>
                                                        <option value="Resign" @selected($employee->status_kerja === 'Resign')>Resign</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Drop Point</label>
                                                    <select name="droppoint_id" class="form-select">
                                                        <option value="">- Tidak dipilih -</option>
                                                        @foreach($droppoints as $dp)
                                                            <option value="{{ $dp->id }}" @selected($employee->droppoint_id === $dp->id)>{{ $dp->kode_dp }} - {{ $dp->nama_dp }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">ID Sebelumnya</label><input name="id_sebelumnya" class="form-control" value="{{ $employee->system?->id_sebelumnya }}"></div>
                                                <div class="col-md-6"><label class="form-label">Nama ID Sebelumnya</label><input name="nama_id_sebelumnya" class="form-control" value="{{ $employee->system?->nama_id_sebelumnya }}"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data karyawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $employees->links() }}</div>
    </div>

@endsection

@section('scripts')
    @if($errors->any() && old('form_context') === 'create' && auth()->user()->canManageData())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambahKaryawan')).show();
            });
        </script>
    @endif
@endsection
