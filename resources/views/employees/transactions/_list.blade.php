<div class="card card-premium bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @foreach($columns as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                    <th>Status Pemrosesan</th>
                    <th class="text-end pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        @foreach($columns as $key => $label)
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
                    <tr>
                        <td colspan="{{ count($columns) + 2 }}" class="text-center py-5 text-muted">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $transactions->links() }}</div>
</div>
