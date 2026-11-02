@extends('layouts.app')

@section('title', 'Backup Data - VeloAssets')

@section('content')
    <div class="mb-4">
        <span class="text-primary text-uppercase fw-bold" style="font-size: .72rem; letter-spacing: .12em;">Administrasi</span>
        <h1 class="h3 fw-bold text-dark mt-1 mb-1">Backup Data</h1>
        <p class="text-muted mb-0">Buat salinan database dan foto aset untuk disimpan dengan aman.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-premium bg-white p-4 p-lg-5">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <span class="d-flex align-items-center justify-content-center rounded-4 bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-database fs-4"></i>
                    </span>
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-1">Unduh backup lengkap</h2>
                        <p class="text-muted mb-0">Satu arsip ZIP akan dibuat saat diminta dan langsung diunduh.</p>
                    </div>
                </div>

                <ul class="list-unstyled d-grid gap-3 mb-4">
                    <li><i class="fa-solid fa-circle-check text-success me-2"></i>Dump SQL database, termasuk data akun dan transaksi.</li>
                    <li><i class="fa-solid fa-circle-check text-success me-2"></i>Foto unit dari penyimpanan privat, dengan struktur folder dipertahankan.</li>
                    <li><i class="fa-solid fa-circle-check text-success me-2"></i>Petunjuk pemulihan ada di dalam arsip sebagai README.txt.</li>
                    <li><i class="fa-solid fa-shield-halved text-warning me-2"></i>File backup berisi data sensitif. Simpan di tempat yang aman.</li>
                </ul>

                <form action="{{ route('backups.download') }}" method="POST" onsubmit="return confirm('Buat dan unduh backup database serta foto aset sekarang? Simpan file tersebut dengan aman.')">
                    @csrf
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fa-solid fa-download me-2"></i>Buat & unduh backup
                    </button>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-premium bg-white p-4 h-100">
                <h2 class="h6 fw-bold text-dark"><i class="fa-solid fa-circle-info text-primary me-2"></i>Catatan</h2>
                <p class="small text-muted">Backup tidak disimpan otomatis di server. Setelah unduhan selesai, pindahkan arsip ke penyimpanan aman seperti drive backup yang aksesnya dibatasi.</p>
                <p class="small text-muted mb-0">Lakukan backup sebelum migrasi besar dan uji pemulihan secara berkala pada database terpisah.</p>
            </div>
        </div>
    </div>
@endsection
