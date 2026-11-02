@extends('layouts.app')

@section('title', 'Laporan Inventory - VeloAssets')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <span class="text-primary text-uppercase fw-bold" style="font-size: .72rem; letter-spacing: .12em;">Laporan</span>
            <h1 class="h3 fw-bold text-dark mt-1 mb-1">Laporan Inventory</h1>
            <p class="text-muted mb-0">Filter kondisi, kategori, dan lokasi unit; lalu ekspor hasil ke CSV.</p>
        </div>
        <a href="{{ route('inventory_report.export', request()->query()) }}" class="btn btn-success text-white">
            <i class="fa-solid fa-file-csv me-1"></i> Ekspor CSV
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Total unit sesuai filter', 'value' => $totalUnits, 'icon' => 'fa-boxes-stacked', 'color' => 'primary'],
            ['label' => 'Ready', 'value' => $statusCounts['Ready'] ?? 0, 'icon' => 'fa-circle-check', 'color' => 'success'],
            ['label' => 'Deployed', 'value' => $statusCounts['Deployed'] ?? 0, 'icon' => 'fa-location-dot', 'color' => 'info'],
            ['label' => 'Maintenance / Broken', 'value' => ($statusCounts['Maintenance'] ?? 0) + ($statusCounts['Broken'] ?? 0), 'icon' => 'fa-triangle-exclamation', 'color' => 'warning'],
        ] as $stat)
            <div class="col-6 col-xl-3">
                <div class="card card-premium h-100 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="d-block text-muted fw-bold text-uppercase mb-2" style="font-size: .67rem;">{{ $stat['label'] }}</span>
                            <strong class="text-dark fs-4">{{ number_format((int) $stat['value']) }}</strong>
                        </div>
                        <span class="d-flex align-items-center justify-content-center rounded-3 bg-{{ $stat['color'] }} bg-opacity-10 text-{{ $stat['color'] }}" style="width: 40px; height: 40px;">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-premium bg-white p-3 p-lg-4 mb-4">
        <form action="{{ route('inventory_report.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="reportSearch" class="form-label small fw-semibold">Cari SKU, serial, model, merek</label>
                <input id="reportSearch" name="search" class="form-control" value="{{ request('search') }}" maxlength="255" placeholder="Ketik kata kunci">
            </div>
            <div class="col-md-3">
                <label for="reportCategory" class="form-label small fw-semibold">Kategori</label>
                <select id="reportCategory" name="kategori" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->name }}" @selected(request('kategori') === $category->name)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="reportStatus" class="form-label small fw-semibold">Status</label>
                <select id="reportStatus" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach(['Ready', 'Deployed', 'Maintenance', 'Broken'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="reportDroppoint" class="form-label small fw-semibold">Lokasi</label>
                <select id="reportDroppoint" name="droppoint_id" class="form-select">
                    <option value="">Semua lokasi</option>
                    @foreach($droppoints as $droppoint)
                        <option value="{{ $droppoint->id }}" @selected((string) request('droppoint_id') === (string) $droppoint->id)>{{ $droppoint->kode_dp }} — {{ $droppoint->nama_dp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('inventory_report.index') }}" class="btn btn-light border">Reset</a>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter me-1"></i> Terapkan filter</button>
            </div>
        </form>
    </div>

    <div class="card card-premium bg-white overflow-hidden">
        <div class="card-header bg-white px-4 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h6 fw-bold text-dark mb-1">Daftar unit</h2>
                <small class="text-muted">{{ number_format($assetItems->total()) }} hasil</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-premium align-middle mb-0">
                <thead>
                    <tr>
                        <th>SKU / Serial</th>
                        <th>Model / Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Didaftarkan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assetItems as $item)
                        <tr>
                            <td>
                                <span class="d-block fw-bold text-dark">{{ $item->sku }}</span>
                                <small class="text-muted">{{ $item->serial_number ?? 'Tanpa serial number' }}</small>
                            </td>
                            <td>
                                <span class="d-block fw-semibold">{{ $item->catalog?->nama ?? 'Katalog tidak tersedia' }}</span>
                                <small class="text-muted">{{ $item->catalog?->kategori ?? '—' }} · {{ $item->catalog?->merk ?? '—' }}</small>
                            </td>
                            <td>{{ $item->droppoint?->kode_dp ?? '—' }} <small class="text-muted">{{ $item->droppoint?->nama_dp }}</small></td>
                            <td><span class="badge rounded-pill bg-light text-dark border">{{ $item->status }}</span></td>
                            <td class="text-muted small">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Tidak ada unit yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assetItems->hasPages())
            <div class="card-footer bg-white px-4 py-3">{{ $assetItems->links() }}</div>
        @endif
    </div>
@endsection
