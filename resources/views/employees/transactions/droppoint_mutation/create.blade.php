@extends('layouts.app')

@section('title', 'Buat Mutasi Drop Point - VeloAssets')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Buat Mutasi Drop Point</h3>
            <p class="text-muted small mt-1">Pilih karyawan, Drop Point tujuan, dan tanggal mutasi.</p>
        </div>
        <a href="{{ route('employee_transactions.droppoint_mutation') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card card-premium bg-white">
        <div class="card-header bg-white fw-bold">Form Mutasi Drop Point</div>
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
                        <label for="droppoint_baru_id" class="form-label">Drop Point Baru <span class="text-danger">*</span></label>
                        <select id="droppoint_baru_id" name="droppoint_baru_id" class="form-select" required>
                            <option value="">Pilih Drop Point</option>
                            @foreach($droppoints as $dp)
                                <option value="{{ $dp->id }}" @selected((string) old('droppoint_baru_id') === (string) $dp->id)>{{ $dp->kode_dp }} - {{ $dp->nama_dp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_mutasi_dp" class="form-label">Tanggal Mutasi DP <span class="text-danger">*</span></label>
                        <input id="tanggal_mutasi_dp" name="tanggal_mutasi_dp" type="date" value="{{ old('tanggal_mutasi_dp') }}" class="form-control" required>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('employee_transactions.droppoint_mutation') }}" class="btn btn-light border">Batal</a>
                    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
@endsection
