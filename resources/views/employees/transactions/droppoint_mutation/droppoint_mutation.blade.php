@extends('layouts.app')

@section('title', 'Mutasi Drop Point - VeloAssets')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
        <h3 class="fw-bold text-dark m-0">Mutasi Drop Point</h3>
        <p class="text-muted small mt-1">Catat perpindahan karyawan ke Drop Point baru.</p>
        </div>
        @if(auth()->user()->canManageData())
            <a href="{{ route('employee_transactions.droppoint_mutation.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i>Buat Transaksi
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @php($columns = $definition['columns'])
    @include('employees.transactions._list')
@endsection
