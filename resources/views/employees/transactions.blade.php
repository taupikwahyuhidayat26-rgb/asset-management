@extends('layouts.app')

@section('title', $definition['title'] . ' - VeloAssets')

@section('header')
    <div>
        <h3 class="fw-bold text-dark m-0">{{ $definition['title'] }}</h3>
        <p class="text-muted small m-0 mt-1">Kelola transaksi dan status pemrosesan karyawan.</p>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if(count($definition['fields']) && auth()->user()->canManageData())
        <div class="card card-premium bg-white mb-4">
            <div class="card-header bg-white fw-bold">Buat Transaksi</div>
            <div class="card-body">
                <form action="{{ route($definition['routes']['store']) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        @foreach($definition['fields'] as $name => [$label, $inputType])
                            <div class="col-md-4">
                                <label class="form-label">{{ $label }}</label>
                                @if($inputType === 'employee')
                                    <select name="{{ $name }}" class="form-select" required>
                                        <option value="">Pilih karyawan</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->no_karyawan }} - {{ $employee->nama_karyawan }}</option>
                                        @endforeach
                                    </select>
                                @elseif($inputType === 'droppoint')
                                    <select name="{{ $name }}" class="form-select" required>
                                        <option value="">Pilih drop point</option>
                                        @foreach($droppoints as $dp)
                                            <option value="{{ $dp->id }}">{{ $dp->kode_dp }} - {{ $dp->nama_dp }}</option>
                                        @endforeach
                                    </select>
                                @elseif($inputType === 'textarea')
                                    <textarea name="{{ $name }}" class="form-control" rows="2"></textarea>
                                @else
                                    <input type="{{ $inputType }}" name="{{ $name }}" class="form-control" required>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary mt-3">Simpan Transaksi</button>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-info">Permintaan pembuatan ID dibuat otomatis saat data karyawan baru ditambahkan.</div>
    @endif

    <div class="card card-premium bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach($definition['columns'] as $label)<th>{{ $label }}</th>@endforeach
                        <th>Status Pemrosesan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            @foreach($definition['columns'] as $key => $label)
                                @php($cell = data_get($transaction, $key))
                                <td>
                                    @if($cell instanceof \Illuminate\Support\Carbon)
                                        {{ $cell->format('d-m-Y') }}
                                    @else
                                        {{ $cell ?: '-' }}
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if(auth()->user()->canManageData())
                                <form action="{{ route($definition['routes']['status'], $transaction->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="status_pemrosesan" class="form-select form-select-sm">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" @selected($transaction->status_pemrosesan === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Simpan</button>
                                </form>
                                @else
                                    {{ $transaction->status_pemrosesan }}
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if(auth()->user()->canManageData())
                                <form action="{{ route($definition['routes']['destroy'], $transaction->id) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($definition['columns']) + 2 }}" class="text-center py-5 text-muted">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $transactions->links() }}</div>
    </div>
@endsection
