@extends('layouts.app')

@section('title', 'Dashboard - VeloAssets')

@section('header')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <span class="text-primary text-uppercase fw-bold" style="font-size: .72rem; letter-spacing: .12em;">Ringkasan operasional</span>
            <h1 class="fw-bold text-dark mt-1 mb-1" style="font-size: clamp(1.5rem, 3vw, 2rem);">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="text-muted mb-0">Pantau kondisi aset dan aktivitas inventory dari satu tempat.</p>
        </div>
        <div class="bg-white border rounded-3 px-3 py-2 text-secondary small fw-semibold">
            <i class="fa-regular fa-calendar text-primary me-2"></i>{{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl">
            <a href="{{ route('asset.index') }}" class="card card-premium h-100 p-3 p-lg-4 bg-white text-decoration-none">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="d-block text-muted fw-bold text-uppercase mb-2" style="font-size: .68rem; letter-spacing: .06em;">Model katalog</span>
                        <strong class="d-block text-dark" style="font-size: 1.8rem; line-height: 1;">{{ number_format($totalCatalogs) }}</strong>
                    </div>
                    <span class="d-flex align-items-center justify-content-center rounded-3 text-primary bg-primary bg-opacity-10" style="width: 44px; height: 44px;"><i class="fa-solid fa-boxes-stacked"></i></span>
                </div>
                <small class="text-muted mt-3">Jenis aset terdaftar <i class="fa-solid fa-arrow-right ms-1"></i></small>
            </a>
        </div>
        <div class="col-6 col-xl">
            <a href="{{ route('asset_item.index') }}" class="card card-premium h-100 p-3 p-lg-4 bg-white text-decoration-none">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="d-block text-muted fw-bold text-uppercase mb-2" style="font-size: .68rem; letter-spacing: .06em;">Total unit fisik</span>
                        <strong class="d-block text-dark" style="font-size: 1.8rem; line-height: 1;">{{ number_format($totalUnits) }}</strong>
                    </div>
                    <span class="d-flex align-items-center justify-content-center rounded-3 text-success bg-success bg-opacity-10" style="width: 44px; height: 44px;"><i class="fa-solid fa-barcode"></i></span>
                </div>
                <small class="text-muted mt-3">Unit inventory terdaftar <i class="fa-solid fa-arrow-right ms-1"></i></small>
            </a>
        </div>
        <div class="col-6 col-xl">
            <a href="{{ route('asset_item.index', ['status' => 'Ready']) }}" class="card card-premium h-100 p-3 p-lg-4 bg-white text-decoration-none">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="d-block text-muted fw-bold text-uppercase mb-2" style="font-size: .68rem; letter-spacing: .06em;">Siap digunakan</span>
                        <strong class="d-block text-dark" style="font-size: 1.8rem; line-height: 1;">{{ number_format($assetReady) }}</strong>
                    </div>
                    <span class="d-flex align-items-center justify-content-center rounded-3 text-info bg-info bg-opacity-10" style="width: 44px; height: 44px;"><i class="fa-solid fa-circle-check"></i></span>
                </div>
                <small class="text-muted mt-3">Unit berstatus Ready <i class="fa-solid fa-arrow-right ms-1"></i></small>
            </a>
        </div>
        <div class="col-6 col-xl">
            <a href="{{ route('asset_item.index', ['status' => 'Deployed']) }}" class="card card-premium h-100 p-3 p-lg-4 bg-white text-decoration-none">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="d-block text-muted fw-bold text-uppercase mb-2" style="font-size: .68rem; letter-spacing: .06em;">Sedang digunakan</span>
                        <strong class="d-block text-dark" style="font-size: 1.8rem; line-height: 1;">{{ number_format($assetDeployed) }}</strong>
                    </div>
                    <span class="d-flex align-items-center justify-content-center rounded-3 text-warning bg-warning bg-opacity-10" style="width: 44px; height: 44px;"><i class="fa-solid fa-location-dot"></i></span>
                </div>
                <small class="text-muted mt-3">Unit berstatus Deployed <i class="fa-solid fa-arrow-right ms-1"></i></small>
            </a>
        </div>
        <div class="col-6 col-xl">
            <a href="{{ route('asset_item.index') }}" class="card card-premium h-100 p-3 p-lg-4 bg-white text-decoration-none">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="d-block text-muted fw-bold text-uppercase mb-2" style="font-size: .68rem; letter-spacing: .06em;">Perlu perhatian</span>
                        <strong class="d-block text-dark" style="font-size: 1.8rem; line-height: 1;">{{ number_format($statusMaintenance + $statusBroken) }}</strong>
                    </div>
                    <span class="d-flex align-items-center justify-content-center rounded-3 text-danger bg-danger bg-opacity-10" style="width: 44px; height: 44px;"><i class="fa-solid fa-triangle-exclamation"></i></span>
                </div>
                <small class="text-muted mt-3">{{ number_format($statusMaintenance) }} Maintenance · {{ number_format($statusBroken) }} Broken</small>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card card-premium bg-white overflow-hidden">
                <div class="card-header bg-white px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h2 class="h6 fw-bold text-dark mb-1"><i class="fa-solid fa-bolt text-warning me-2"></i>Akses cepat</h2>
                        <small class="text-muted">Buka daftar transaksi atau mulai pencatatan baru.</small>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border">{{ number_format($totalSupplier) }} supplier</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @php
                            $quickActions = [
                                ['title' => 'Barang masuk', 'icon' => 'fa-arrow-down-to-bracket', 'color' => 'primary', 'index' => 'barang_masuk.index', 'create' => 'barang_masuk.create'],
                                ['title' => 'Barang keluar', 'icon' => 'fa-arrow-up-from-bracket', 'color' => 'success', 'index' => 'barang_keluar.index', 'create' => 'barang_keluar.create'],
                                ['title' => 'Barang kembali', 'icon' => 'fa-rotate-left', 'color' => 'info', 'index' => 'asset_returns.index', 'create' => 'asset_returns.create'],
                                ['title' => 'Transfer Drop Point', 'icon' => 'fa-right-left', 'color' => 'warning', 'index' => 'droppoint_transfers.index', 'create' => 'droppoint_transfers.create'],
                            ];
                        @endphp
                        @foreach($quickActions as $action)
                            <div class="col-6 col-lg-3">
                                <a href="{{ route(auth()->user()->canManageData() ? $action['create'] : $action['index']) }}" class="btn btn-light border w-100 h-100 text-start p-3 d-flex align-items-center gap-3">
                                    <span class="d-flex align-items-center justify-content-center rounded-3 text-{{ $action['color'] }} bg-{{ $action['color'] }} bg-opacity-10 flex-shrink-0" style="width: 42px; height: 42px;"><i class="fa-solid {{ $action['icon'] }}"></i></span>
                                    <span class="small fw-bold text-dark">{{ $action['title'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card card-premium h-100 bg-white overflow-hidden">
                <div class="card-header bg-white px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="h6 fw-bold text-dark mb-1"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Mutasi aset terbaru</h2>
                        <small class="text-muted">Pergerakan unit berdasarkan catatan riwayat mutasi.</small>
                    </div>
                    <a href="{{ route('asset.kartu_stok') }}" class="btn btn-sm btn-light border">Lihat kartu stok <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Unit</th>
                                <th>Aktivitas</th>
                                <th>Perpindahan</th>
                                <th class="text-end">Referensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestMutations as $mutation)
                                @php
                                    $activity = [
                                        'receipt' => ['Penerimaan', 'primary', 'fa-arrow-down-to-bracket'],
                                        'issue' => ['Pengeluaran', 'success', 'fa-arrow-up-from-bracket'],
                                        'return' => ['Barang kembali', 'info', 'fa-rotate-left'],
                                        'transfer' => ['Transfer lokasi', 'warning', 'fa-right-left'],
                                        'adjustment' => ['Penyesuaian', 'secondary', 'fa-pen-to-square'],
                                    ][$mutation->type] ?? [ucfirst($mutation->type), 'secondary', 'fa-arrows-rotate'];
                                    $detailRoutes = [
                                        'barang_masuk' => 'barang_masuk.show',
                                        'barang_keluar' => 'barang_keluar.show',
                                        'asset_return' => 'asset_returns.show',
                                        'droppoint_transfer' => 'droppoint_transfers.show',
                                    ];
                                    $detailRoute = $detailRoutes[$mutation->source_type] ?? null;
                                @endphp
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="d-block fw-semibold text-dark">{{ $mutation->occurred_at?->translatedFormat('d M Y') ?? '—' }}</span>
                                        <small class="text-muted">{{ $mutation->occurred_at?->format('H:i') ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="d-block fw-bold text-dark">{{ $mutation->assetItem?->sku ?? 'Unit tidak tersedia' }}</span>
                                        <small class="text-muted">{{ $mutation->assetItem?->catalog?->nama ?? 'Katalog tidak tersedia' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-{{ $activity[1] }} bg-opacity-10 text-{{ $activity[1] }}">
                                            <i class="fa-solid {{ $activity[2] }} me-1"></i>{{ $activity[0] }}
                                        </span>
                                    </td>
                                    <td class="small">
                                        <span class="text-muted">{{ $mutation->fromDroppoint?->nama_dp ?? '—' }}</span>
                                        <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
                                        <span class="fw-semibold text-dark">{{ $mutation->toDroppoint?->nama_dp ?? '—' }}</span>
                                        <small class="d-block text-muted mt-1">{{ $mutation->from_status ?? '—' }} → {{ $mutation->to_status ?? '—' }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if($detailRoute && $mutation->source_id && \Illuminate\Support\Facades\Route::has($detailRoute))
                                            <a href="{{ route($detailRoute, $mutation->source_id) }}" class="btn btn-sm btn-light border text-primary">{{ $mutation->source_reference ?? 'Detail' }}</a>
                                        @else
                                            <span class="text-muted small">{{ $mutation->source_reference ?? '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted mb-3" style="width: 52px; height: 52px;"><i class="fa-solid fa-clock-rotate-left"></i></span>
                                        <p class="fw-semibold text-dark mb-1">Belum ada riwayat mutasi</p>
                                        <small class="text-muted">Aktivitas unit akan muncul setelah transaksi dicatat.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-premium h-100 bg-white overflow-hidden">
                <div class="card-header bg-white px-4 py-3">
                    <h2 class="h6 fw-bold text-dark mb-1"><i class="fa-solid fa-ranking-star text-warning me-2"></i>Katalog dengan unit terbanyak</h2>
                    <small class="text-muted">Jumlah unit fisik per model.</small>
                </div>
                <div class="card-body p-0">
                    @forelse($topCatalogs as $catalog)
                        <div class="d-flex align-items-center justify-content-between gap-3 px-4 py-3 border-bottom">
                            <div class="min-w-0">
                                <span class="d-block fw-bold text-dark text-truncate">{{ $catalog->nama }}</span>
                                <small class="text-muted">{{ $catalog->kategori }} · {{ $catalog->merk ?? 'Merek tidak dicantumkan' }}</small>
                            </div>
                            <span class="badge rounded-pill bg-light text-primary border flex-shrink-0">{{ number_format($catalog->items_count) }} unit</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="fa-solid fa-box-open fs-4 mb-2"></i>
                            <p class="mb-0">Belum ada katalog terdaftar.</p>
                        </div>
                    @endforelse
                    <div class="p-3">
                        <a href="{{ route('asset.index') }}" class="btn btn-light border w-100">Buka master katalog <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
