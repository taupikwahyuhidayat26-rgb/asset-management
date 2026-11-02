@extends('layouts.app')

@section('title', 'Tambah Karyawan - VeloAssets')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark m-0">Tambah Karyawan</h3>
            <p class="text-muted small m-0 mt-1">Isi informasi karyawan untuk menambah data ke master karyawan.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Periksa kembali data yang dimasukkan:</div>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div class="card card-premium bg-white mb-4">
            <div class="card-header bg-white py-3 px-4">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-address-card text-primary me-2"></i>Informasi Karyawan</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($fields as $name => [$label, $inputType, $required])
                        <div class="col-md-6 col-lg-4">
                            <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}@if($required) <span class="text-danger">*</span>@endif</label>
                            <input
                                type="{{ $inputType }}"
                                id="{{ $name }}"
                                name="{{ $name }}"
                                value="{{ old($name) }}"
                                class="form-control @error($name) is-invalid @enderror"
                                @if($required) required @endif
                            >
                            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach

                    <div class="col-md-6 col-lg-4">
                        <label for="status_kerja" class="form-label fw-semibold">Status Kerja <span class="text-danger">*</span></label>
                        <select id="status_kerja" name="status_kerja" class="form-select @error('status_kerja') is-invalid @enderror" required>
                            <option value="Aktif" @selected(old('status_kerja', 'Aktif') === 'Aktif')>Aktif</option>
                            <option value="Resign" @selected(old('status_kerja') === 'Resign')>Resign</option>
                        </select>
                        @error('status_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <label for="droppoint_id" class="form-label fw-semibold">Kode DP</label>
                        <select id="droppoint_id" name="droppoint_id" class="form-select @error('droppoint_id') is-invalid @enderror">
                            <option value="">- Pilih Drop Point -</option>
                            @foreach($droppoints as $dp)
                                <option value="{{ $dp->id }}" @selected((string) old('droppoint_id') === (string) $dp->id)>
                                    {{ $dp->kode_dp }} - {{ $dp->nama_dp }}
                                </option>
                            @endforeach
                        </select>
                        @error('droppoint_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-premium bg-white mb-4">
            <div class="card-header bg-white py-3 px-4">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-computer text-primary me-2"></i>Informasi Master Sistem</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="id_sebelumnya" class="form-label fw-semibold">ID Sebelumnya</label>
                        <input type="text" id="id_sebelumnya" name="id_sebelumnya" value="{{ old('id_sebelumnya') }}" class="form-control @error('id_sebelumnya') is-invalid @enderror">
                        @error('id_sebelumnya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nama_id_sebelumnya" class="form-label fw-semibold">Nama ID Sebelumnya</label>
                        <input type="text" id="nama_id_sebelumnya" name="nama_id_sebelumnya" value="{{ old('nama_id_sebelumnya') }}" class="form-control @error('nama_id_sebelumnya') is-invalid @enderror">
                        @error('nama_id_sebelumnya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="alert alert-info py-2 mt-3 mb-0 small">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Permintaan pembuatan ID baru akan dibuat otomatis dengan status Menunggu.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('employees.index') }}" class="btn btn-light border">Batal</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa-solid fa-floppy-disk me-1"></i>Simpan Karyawan
            </button>
        </div>
    </form>
@endsection
