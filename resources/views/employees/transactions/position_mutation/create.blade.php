@extends('layouts.app')

@section('title', 'Buat Mutasi Posisi - VeloAssets')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Buat Mutasi Posisi</h3>
            <p class="text-muted small mt-1">Masukkan posisi dan status karyawan yang baru.</p>
        </div>
        <a href="{{ route('employee_transactions.position_mutation') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card card-premium bg-white">
        <div class="card-header bg-white fw-bold">Form Mutasi Posisi</div>
        <div class="card-body">
            <form action="{{ route($definition['routes']['store']) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Karyawan <span class="text-danger">*</span></label>
                        <select id="employee_id" name="employee_id" class="form-select" required>
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>{{ $employee->no_karyawan }} - {{ $employee->nama_karyawan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="posisi_baru" class="form-label">Posisi Baru <span class="text-danger">*</span></label>
                        <input id="posisi_baru" name="posisi_baru" type="text" value="{{ old('posisi_baru') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="status_karyawan_baru" class="form-label">Status Karyawan Baru</label>
                        <input id="status_karyawan_baru" name="status_karyawan_baru" type="text" value="{{ old('status_karyawan_baru') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_mutasi" class="form-label">Tanggal Mutasi <span class="text-danger">*</span></label>
                        <input id="tanggal_mutasi" name="tanggal_mutasi" type="date" value="{{ old('tanggal_mutasi') }}" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('employee_transactions.position_mutation') }}" class="btn btn-light border">Batal</a>
                    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
@endsection
