@extends('layouts.app')

@section('title', $title.' - VeloAssets')

@section('content')
    @php
        $oldAssetItems = old('asset_items', []);
        $oldAssetItems = is_array($oldAssetItems) ? array_map('intval', $oldAssetItems) : [];
        $scanItems = $assetItems->map(fn ($item) => [
            'id' => $item->id,
            'sku' => $item->sku,
            'serial_number' => $item->serial_number,
            'name' => $item->catalog?->nama ?? 'Tanpa Model',
            'status' => $item->status,
            'droppoint_id' => $item->droppoint_id,
        ])->values();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">{{ $title }}</h2>
            <p class="text-muted small m-0 mt-1">
                @if($movementType === 'return')
                    Kembalikan unit dari Drop Point ke gudang dan tentukan status setelah diperiksa.
                @else
                    Pindahkan unit berstatus Deployed ke Drop Point lain.
                @endif
            </p>
        </div>
        <a href="{{ route($indexRoute) }}" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route($storeRoute) }}" method="POST" id="movementForm">
        @csrf
        <div class="card card-premium bg-white shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Informasi Transaksi</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" name="{{ $movementType === 'return' ? 'return_code' : 'transfer_code' }}" class="form-control" value="{{ old($movementType === 'return' ? 'return_code' : 'transfer_code', $code) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="{{ $dateField }}" class="form-control" value="{{ old($dateField, now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="sourceDroppoint" class="form-label">{{ $sourceLabel }}</label>
                        <select id="sourceDroppoint" name="source_droppoint_id" class="form-select" required>
                            <option value="">-- Pilih Drop Point Asal --</option>
                            @foreach($droppoints as $droppoint)
                                <option value="{{ $droppoint->id }}" @selected((string) old('source_droppoint_id') === (string) $droppoint->id)>
                                    {{ $droppoint->kode_dp }} - {{ $droppoint->nama_dp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($movementType === 'return')
                        <div class="col-md-4">
                            <label for="destinationStatus" class="form-label">Status Setelah Diterima Gudang</label>
                            <select id="destinationStatus" name="destination_status" class="form-select" required>
                                @foreach(['Ready', 'Maintenance', 'Broken'] as $status)
                                    <option value="{{ $status }}" @selected(old('destination_status', 'Ready') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-4">
                            <label for="destinationDroppoint" class="form-label">Drop Point Tujuan</label>
                            <select id="destinationDroppoint" name="destination_droppoint_id" class="form-select" required>
                                <option value="">-- Pilih Drop Point Tujuan --</option>
                                @foreach($droppoints as $droppoint)
                                    <option value="{{ $droppoint->id }}" @selected((string) old('destination_droppoint_id') === (string) $droppoint->id)>
                                        {{ $droppoint->kode_dp }} - {{ $droppoint->nama_dp }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12">
                        <label for="movementNotes" class="form-label">Catatan</label>
                        <textarea id="movementNotes" name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-premium bg-white shadow-sm">
            <div class="card-header bg-white fw-bold">Pilih Unit dengan Scan SKU atau Nomor Seri</div>
            <div class="card-body">
                <label for="scanInput" class="form-label">Scan atau ketik kode unit, lalu tekan Enter</label>
                <input id="scanInput" type="text" class="form-control form-control-lg mb-3" autocomplete="off" placeholder="SKU / Serial Number">
                <div class="small text-muted mb-3" id="availabilityHint">Pilih Drop Point asal untuk melihat unit yang tersedia.</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Aksi</th>
                                <th>SKU</th>
                                <th>Serial Number</th>
                                <th>Model</th>
                                <th>Status saat ini</th>
                            </tr>
                        </thead>
                        <tbody id="selectedUnits">
                            <tr id="emptyState"><td colspan="5" class="text-center text-muted py-4">Belum ada unit dipilih.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="selectionError" class="text-danger small" role="alert"></div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan Transaksi</button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const units = @json($scanItems);
            const source = document.getElementById('sourceDroppoint');
            const destination = document.getElementById('destinationDroppoint');
            const input = document.getElementById('scanInput');
            const table = document.getElementById('selectedUnits');
            const emptyState = document.getElementById('emptyState');
            const hint = document.getElementById('availabilityHint');
            const error = document.getElementById('selectionError');
            const selectedIds = new Set();
            const oldIds = @json($oldAssetItems);

            function availableUnits() {
                return units.filter(unit => String(unit.droppoint_id) === source.value);
            }

            function updateHint() {
                const count = availableUnits().length;
                hint.textContent = source.value
                    ? `${count} unit tersedia di Drop Point asal.`
                    : 'Pilih Drop Point asal untuk melihat unit yang tersedia.';
            }

            function addUnit(unit) {
                if (selectedIds.has(unit.id)) {
                    error.textContent = 'Unit tersebut sudah ada di daftar.';
                    return;
                }

                selectedIds.add(unit.id);
                emptyState.hidden = true;
                const row = document.createElement('tr');
                const values = [null, unit.sku, unit.serial_number || '-', unit.name, unit.status];

                values.forEach((value, index) => {
                    const cell = document.createElement('td');
                    if (index === 0) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'btn btn-sm btn-outline-danger';
                        button.textContent = 'Hapus';
                        button.addEventListener('click', function () {
                            selectedIds.delete(unit.id);
                            row.remove();
                            emptyState.hidden = selectedIds.size > 0;
                        });
                        cell.appendChild(button);
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'asset_items[]';
                        hidden.value = unit.id;
                        cell.appendChild(hidden);
                    } else {
                        cell.textContent = value;
                    }
                    row.appendChild(cell);
                });

                table.appendChild(row);
                error.textContent = '';
            }

            function syncDestination() {
                if (!destination) return;
                destination.querySelectorAll('option').forEach(option => {
                    option.disabled = option.value !== '' && option.value === source.value;
                });
                if (destination.value === source.value) destination.value = '';
            }

            source.addEventListener('change', function () {
                selectedIds.clear();
                table.querySelectorAll('tr:not(#emptyState)').forEach(row => row.remove());
                emptyState.hidden = false;
                syncDestination();
                updateHint();
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                const code = input.value.trim().toLowerCase();
                if (!code) return;
                if (!source.value) {
                    error.textContent = 'Pilih Drop Point asal terlebih dahulu.';
                    return;
                }

                const unit = availableUnits().find(item =>
                    (item.sku && item.sku.toLowerCase() === code)
                    || (item.serial_number && item.serial_number.toLowerCase() === code)
                );
                if (!unit) {
                    error.textContent = 'Unit tidak tersedia di Drop Point asal atau tidak memenuhi status transaksi.';
                } else {
                    addUnit(unit);
                }
                input.value = '';
            });

            document.getElementById('movementForm').addEventListener('submit', function (event) {
                if (selectedIds.size === 0) {
                    event.preventDefault();
                    error.textContent = 'Pilih minimal satu unit untuk transaksi.';
                }
            });

            updateHint();
            syncDestination();
            if (source.value) {
                oldIds.forEach(id => {
                    const unit = availableUnits().find(item => item.id === id);
                    if (unit) addUnit(unit);
                });
            }
        });
    </script>
@endsection
