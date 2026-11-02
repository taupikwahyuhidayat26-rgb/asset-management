@extends('layouts.app')

@section('title', $title.' - VeloAssets')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">{{ $title }}</h2>
            <p class="text-muted small m-0 mt-1">
                {{ $movementType === 'return' ? 'Catat unit dari Drop Point kembali ke gudang.' : 'Pindahkan unit deployed dari satu Drop Point ke Drop Point lain.' }}
            </p>
        </div>
        @if(auth()->user()->canManageData())
            <a href="{{ route($createRoute) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Buat Transaksi
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card card-premium bg-white shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Kode Transaksi</th>
                        @if($movementType === 'return')
                            <th>Drop Point Asal</th>
                            <th>Status di Gudang</th>
                        @else
                            <th>Dari</th>
                            <th>Ke</th>
                        @endif
                        <th class="text-center">Jumlah Unit</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="ps-4">{{ $transaction->{$dateField}->format('d M Y') }}</td>
                            <td class="fw-bold text-primary">{{ $transaction->{$codeField} }}</td>
                            @if($movementType === 'return')
                                <td>{{ $transaction->sourceDroppoint->kode_dp }} - {{ $transaction->sourceDroppoint->nama_dp }}</td>
                                <td>{{ $transaction->destination_status }}</td>
                            @else
                                <td>{{ $transaction->sourceDroppoint->kode_dp }} - {{ $transaction->sourceDroppoint->nama_dp }}</td>
                                <td>{{ $transaction->destinationDroppoint->kode_dp }} - {{ $transaction->destinationDroppoint->nama_dp }}</td>
                            @endif
                            <td class="text-center">{{ $transaction->details_count ?? $transaction->details->count() }}</td>
                            <td class="text-center">
                                <a href="{{ route($showRoute, $transaction) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $transactions->links() }}</div>
    </div>
@endsection
