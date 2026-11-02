@extends('layouts.app')

@section('title', 'Dashboard - Overview Workspace')

@section('content')
    <div class="mb-5">
        <h3 class="fw-bold text-dark m-0" style="letter-spacing: -0.5px;">Overview Workspace</h3>
        <p class="text-muted m-0 small mt-1">Sistem Pemantauan Operasional Unit Korporat</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-premium p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div><span class="metric-title" style="font-size: 0.85rem; font-weight:600; color:#64748b;">Total Inventaris</span><div class="metric-value" style="font-size:1.75rem; font-weight:700; color:#1e293b;">{{ $totalAsset }}</div></div>
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary"><i class="fas fa-cubes fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div><span class="metric-title" style="font-size: 0.85rem; font-weight:600; color:#64748b;">Tersedia di Gudang</span><div class="metric-value text-success" style="font-size:1.75rem; font-weight:700;">{{ $assetReady }}</div></div>
                    <div class="icon-shape bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div><span class="metric-title" style="font-size: 0.85rem; font-weight:600; color:#64748b;">Sedang Terpasang</span><div class="metric-value text-info" style="font-size:1.75rem; font-weight:700;">{{ $assetDeployed }}</div></div>
                    <div class="icon-shape bg-info bg-opacity-10 text-info"><i class="fas fa-map-marker-alt fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-premium p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div><span class="metric-title" style="font-size: 0.85rem; font-weight:600; color:#64748b;">Mitra Vendor</span><div class="metric-value text-dark" style="font-size:1.75rem; font-weight:700;">{{ $totalSupplier }}</div></div>
                    <div class="icon-shape bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-handshake fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-5"><div class="card card-premium p-4"><h6 class="fw-bold mb-4 text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Proporsi Kategori Asset</h6><div style="height: 260px;" class="d-flex justify-content-center align-items-center"><canvas id="chartKategoriPremium"></canvas></div></div></div>
        <div class="col-lg-7"><div class="card card-premium p-4"><h6 class="fw-bold mb-4 text-dark"><i class="fas fa-chart-bar text-success me-2"></i>Kondisi & Status Operasional</h6><div style="height: 260px;"><canvas id="chartStatusPremium"></canvas></div></div></div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-premium overflow-hidden">
                <div class="px-4 pt-4 pb-2 d-flex justify-content-between align-items-center bg-white"><h6 class="fw-bold text-dark m-0"><i class="fas fa-clock text-secondary me-2"></i>Registrasi Perangkat Terbaru</h6><span class="badge bg-light text-secondary border px-3 py-2 rounded-2" style="font-size: 0.75rem;">5 Data Terakhir</span></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-premium align-middle mb-0" style="width:100%;">
                            <thead class="table-light" style="background:#f8fafc; font-size:0.75rem; color:#64748b; text-transform:uppercase;">
                                <tr><th>SKU / Kode</th><th>Nama Perangkat</th><th>Kategori</th><th>Lokasi Penempatan</th><th>Status Operasional</th></tr>
                            </thead>
                            <tbody style="font-size:0.9rem;">
                                @foreach($latestAssets as $last)
                                    <tr>
                                        <td class="fw-bold text-dark" style="padding:18px 24px;">{{ $last->sku }}</td>
                                        <td class="fw-semibold text-secondary" style="padding:18px 24px;">{{ $last->nama }}</td>
                                        <td style="padding:18px 24px;"><span class="badge bg-light text-dark border px-2 py-1">{{ $last->kategori }}</span></td>
                                        <td style="padding:18px 24px;"><i class="fas fa-map-marker-alt text-danger me-2 opacity-50"></i>{{ $last->lokasi ?? 'Gudang Utama' }}</td>
                                        <td style="padding:18px 24px;"><span class="badge bg-{{ $last->status == 'Ready' ? 'success' : 'info' }}">{{ $last->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // (Isi script kode Chart Kategori & Status kamu tetap sama persis seperti kemarin)
        new Chart(document.getElementById('chartKategoriPremium').getContext('2d'), { type: 'doughnut', data: { labels: ['CCTV', 'PC/Komputer'], datasets: [{ data: [{{ $cctvCount }}, {{ $pcCount }}], backgroundColor: ['#6366f1', '#94a3b8'], borderHeight: 4, borderColor: '#ffffff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', weight: 500 }, padding: 20 } } } } });
        new Chart(document.getElementById('chartStatusPremium').getContext('2d'), { type: 'bar', data: { labels: ['Ready', 'Deployed', 'Maintenance', 'Broken'], datasets: [{ data: [{{ $assetReady }}, {{ $assetDeployed }}, {{ $statusMaintenance }}, {{ $statusBroken }}], backgroundColor: ['#22c55e', '#0ea5e9', '#eab308', '#ef4444'], borderRadius: 8, barThickness: 32 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans' } } }, x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', weight: 500 } } } } } });
    </script>
@endsection