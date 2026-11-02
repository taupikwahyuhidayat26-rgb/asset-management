@extends('layouts.app')

@section('title', $title.' - VeloAssets')

@section('content')
    @php
        $isReturn = $movementType === 'return';
        $code = $isReturn ? $transaction->return_code : $transaction->transfer_code;
        $date = $isReturn ? $transaction->return_date : $transaction->transfer_date;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">{{ $title }}</h2>
            <p class="text-muted small m-0 mt-1">{{ $code }}</p>
        </div>
        <a href="{{ route($indexRoute) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
    </div>

    <div class="card card-premium bg-white shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><small class="text-muted d-block">Tanggal</small><strong>{{ $date->format('d M Y') }}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">Drop Point Asal</small><strong>{{ $transaction->sourceDroppoint->kode_dp }} - {{ $transaction->sourceDroppoint->nama_dp }}</strong></div>
                @if($isReturn)
                    <div class="col-md-3"><small class="text-muted d-block">Tujuan</small><strong>Gudang IT · {{ $transaction->destination_status }}</strong></div>
                @else
                    <div class="col-md-3"><small class="text-muted d-block">Drop Point Tujuan</small><strong>{{ $transaction->destinationDroppoint->kode_dp }} - {{ $transaction->destinationDroppoint->nama_dp }}</strong></div>
                @endif
                <div class="col-md-3"><small class="text-muted d-block">Petugas</small><strong>{{ $transaction->user->name ?? '-' }}</strong></div>
                <div class="col-12"><small class="text-muted d-block">Catatan</small>{{ $transaction->notes ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="card card-premium bg-white shadow-sm overflow-hidden">
        <div class="card-header bg-white fw-bold">Unit dalam transaksi ({{ $transaction->details->count() }})</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th class="ps-4">SKU</th><th>Serial Number</th><th>Model</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $detail)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $detail->assetItem->sku }}</td>
                            <td>{{ $detail->assetItem->serial_number ?? '-' }}</td>
                            <td>{{ $detail->assetItem->catalog->nama ?? 'Tanpa Model' }}</td>
                            <td>{{ $isReturn ? $transaction->destination_status : 'Deployed' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
