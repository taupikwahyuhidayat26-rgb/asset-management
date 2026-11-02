@extends('layouts.app')

@section('title', 'Input Penerimaan Massal - Asset Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0"><i class="fas fa-folder-plus text-success me-2"></i> Lembar Kerja Barang Masuk</h2>
            <p class="text-muted small m-0 mt-1">Batch registrasi nota invoice dan penomoran unit fisik secara simultan</p>
        </div>
        <a href="{{ route('barang_masuk.index') }}" class="btn btn-light border fw-semibold rounded-3 text-secondary small"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Transaksi belum tersimpan.</div>
            <ul class="mb-0 ps-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if($suppliers->isEmpty() || $catalogs->isEmpty())
        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Form belum siap digunakan:
            @if($suppliers->isEmpty())
                belum ada supplier terdaftar. <a href="{{ route('supplier.index') }}" class="alert-link">Tambahkan supplier</a>.
            @endif
            @if($catalogs->isEmpty())
                Belum ada model katalog. <a href="{{ route('asset.index') }}" class="alert-link">Tambahkan model katalog</a>.
            @endif
        </div>
    @endif

    <form action="{{ route('barang_masuk.store') }}" method="POST" id="formBatchBarangMasuk">
        @csrf
        
        <div class="card card-premium p-4 bg-white mb-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;"><i class="fas fa-file-invoice text-primary me-2"></i>A. Header Dokumen Transaksi</h5>
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Tanggal Terima</label>
                    <input type="date" class="form-control" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">No. Penerimaan (BM)</label>
                    <input type="text" class="form-control" name="no_barang_masuk" value="{{ old('no_barang_masuk') }}" placeholder="BM-{{ date('Ymd') }}-01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">No. PO (Purchase Order)</label>
                    <input type="text" class="form-control" name="no_po" value="{{ old('no_po') }}" placeholder="PO-2026-XXXX">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">No. Invoice Vendor <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_invoice" value="{{ old('no_invoice') }}" placeholder="INV-2026-XXXX" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Pilih Vendor Supplier</label>
                    <select class="form-select form-control" name="supplier_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card card-premium p-4 bg-white mb-4 shadow-sm">
                    <h5 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;"><i class="fas fa-cube text-primary me-2"></i>B. Detail 1: Pilih Model Spesifikasi</h5>
                    <div class="mb-3">
                        <select class="form-select form-control fw-bold border-primary" id="selectKatalog" style="border-radius:10px;">
                            <option value="">-- Pilih Spesifikasi Cetakan --</option>
                            @foreach($catalogs as $cat)
                                <option value="{{ $cat->id }}" data-kategori="{{ $cat->kategori }}" data-nama="{{ $cat->nama }}" data-merk="{{ $cat->merk }}" data-tipe="{{ $cat->tipe }}" data-spek="{{ $cat->spesifikasi }}">
                                    [{{ $cat->kategori }}] {{ $cat->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="p-3 bg-light rounded-3 text-secondary mb-0" style="font-size:0.85rem;" id="boxPreviewKatalog">
                        <div class="mb-1"><b>Kategori:</b> <span id="lblKategori">-</span></div>
                        <div class="mb-1"><b>Nama Model:</b> <span id="lblNama">-</span></div>
                        <div class="mb-1"><b>Merek Perangkat:</b> <span id="lblMerk">-</span></div>
                        <div class="mb-1"><b>Tipe / Seri:</b> <span id="lblTipe">-</span></div>
                        <div class="mb-0"><b>Spesifikasi:</b> <small class="d-block text-muted" id="lblSpek">-</small></div>
                    </div>
                </div>

                <div class="card card-premium p-4 bg-white shadow-sm" id="cardDetail2" style="opacity: 0.6; pointer-events: none;">
                    <h5 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;"><i class="fas fa-barcode text-primary me-2"></i>C. Detail 2: Registrasi Unit Fisik</h5>
                    <div class="row align-items-end g-2">
                        <div class="col-md-9">
                            <label class="form-label small fw-bold text-secondary">Nomor Seri Pabrik (S/N)</label>
                            <input type="text" class="form-control" id="inputSerialNumber" placeholder="Ketik S/N unit fisik barang...">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100 fw-bold py-2 rounded-3 border-0" id="btnTambahKeStaging" style="background:var(--accent-premium);">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-premium overflow-hidden bg-white shadow-sm h-100 d-flex flex-column">
                    <div class="px-4 pt-4 pb-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark m-0"><i class="fas fa-shopping-cart text-warning me-2"></i>D. Detail 3: Review Staging List Unit</h6>
                        <span class="badge bg-secondary" id="lblCountKeranjang">0 Unit</span>
                    </div>
                    
                    <div class="card-body p-0 flex-grow-1" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-premium align-middle mb-0" id="tabelStaging">
                            <thead>
                                <tr>
                                    <th>Merek / Tipe</th>
                                    <th>Kode SKU (Otomatis)</th>
                                    <th>Nomor Seri (S/N)</th>
                                    <th class="text-center" width="10%">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="bodyStaging">
                                <tr id="rowKosong"><td colspan="4" class="text-center py-5 text-muted small"><i class="fas fa-cart-plus fs-4 d-block mb-2 opacity-30"></i>Keranjang sementara kosong. Pilih model di kiri & input S/N untuk memasukkan ke list.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-premium p-3 bg-white mt-4 shadow-sm border-top border-success border-3 d-flex flex-row justify-content-between align-items-center">
            <div>
                <span class="text-muted small fw-medium"><i class="fas fa-exclamation-circle text-warning me-1"></i>Periksa teliti seluruh keranjang review sebelum melakukan penyimpanan database.</span>
            </div>
            <div>
                <button type="submit" class="btn btn-success fw-bold px-5 py-2 rounded-3 border-0 shadow-sm" id="btnSimpanUtama" disabled style="background:#22c55e;">
                    <i class="fas fa-save me-2"></i> SIMPAN TRANSAKSI BARANG MASUK
                </button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil penanda urutan awal nomor urut dari controller (Next Sequence)
        let runningCounter = parseInt('{{ $nextSequence }}');

        // Seleksi Element DOM
        const selectKatalog        = document.getElementById('selectKatalog');
        const boxPreviewKatalog    = document.getElementById('boxPreviewKatalog');
        const cardDetail2          = document.getElementById('cardDetail2');
        const inputSerialNumber    = document.getElementById('inputSerialNumber');
        const btnTambahKeStaging   = document.getElementById('btnTambahKeStaging');
        const bodyStaging          = document.getElementById('bodyStaging');
        const rowKosong            = document.getElementById('rowKosong');
        const lblCountKeranjang    = document.getElementById('lblCountKeranjang');
        const btnSimpanUtama       = document.getElementById('btnSimpanUtama');

        // Element label preview Detail 1
        const lblKategori = document.getElementById('lblKategori');
        const lblNama     = document.getElementById('lblNama');
        const lblMerk     = document.getElementById('lblMerk');
        const lblTipe     = document.getElementById('lblTipe');
        const lblSpek     = document.getElementById('lblSpek');

        inputSerialNumber.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnTambahKeStaging.click();
            }
        });

        // --- EVEN 1: AKSI SAAT SELEKSI DROP-DOWN DETAIL 1 BERUBAH ---
        selectKatalog.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            
            if (this.value === "") {
                // Reset Preview & Kunci Detail 2
                boxPreviewKatalog.classList.add('text-secondary');
                lblKategori.innerText = lblNama.innerText = lblMerk.innerText = lblTipe.innerText = lblSpek.innerText = "-";
                cardDetail2.style.opacity = "0.6";
                cardDetail2.style.pointerEvents = "none";
            } else {
                // Isi Label Detail 1 Secara Real-time
                lblKategori.innerText = selectedOpt.getAttribute('data-kategori');
                lblNama.innerText     = selectedOpt.getAttribute('data-nama');
                lblMerk.innerText     = selectedOpt.getAttribute('data-merk') || '-';
                lblTipe.innerText     = selectedOpt.getAttribute('data-tipe') || '-';
                lblSpek.innerText     = selectedOpt.getAttribute('data-spek') || '-';
                
                // Buka Kunci Box Input Detail 2 (Tanpa reset form)
                cardDetail2.style.opacity = "1";
                cardDetail2.style.pointerEvents = "auto";
                inputSerialNumber.focus();
            }
        });

        // --- EVENT 2: KLIK TOMBOL TAMBAH (+) DI DETAIL 2 UNTUK GENERATE SKU & MASUK KERANJANG ---
        btnTambahKeStaging.addEventListener('click', function() {            
            const snValue = inputSerialNumber.value.trim();

            // --- VERIFIKASI LAPIS 1: CEK DUPLIKASI DI DALAM KERANJANG SEMENTARA ---
            const keranjangSN = Array.from(bodyStaging.querySelectorAll('input[name="serial_number[]"]'))
                                    .map(input => input.value.trim().toLowerCase());
            
            if (keranjangSN.includes(snValue.toLowerCase())) {
                alert(`⚠️ Peringatan: Serial Number "${snValue}" sudah ada di dalam keranjang! Jangan di-scan dua kali.`);
                inputSerialNumber.value = "";
                inputSerialNumber.focus();
                return; // Batalkan proses, keluar dari fungsi
            }
            

            if (snValue === "") {
                alert("Harap masukkan Nomor Seri (S/N) perangkat terlebih dahulu!");
                return;
            }

            // A. Ambil data katalog yang sedang aktif dipilih di Detail 1
            const selectedOpt = selectKatalog.options[selectKatalog.selectedIndex];
            const assetId     = selectKatalog.value;
            const merk        = selectedOpt.getAttribute('data-merk') || '-';
            const tipe        = selectedOpt.getAttribute('data-tipe') || '-';

            // B. PROSES GENERATE KODE SKU DINAMIS FORMAL (IT-YYYYMMDD-XXXX)
            const skrg     = new Date();
            const tahun    = skrg.getFullYear();
            const bulan    = String(skrg.getMonth() + 1).padStart(2, '0');
            const tanggal  = String(skrg.getDate()).padStart(2, '0');
            const noUrut   = String(runningCounter).padStart(4, '0'); // Pad empat digit (0001)
            
            const generatedSku = `IT-${tahun}${bulan}${tanggal}-${noUrut}`;

            // Hilangkan baris tulisan kosong jika ini barang pertama
            if (rowKosong) { rowKosong.remove(); }

            // C. CETAK BARIS BARU KE TABEL STAGING DETAIL 3 (SUNTIK HIDDEN INPUT UNTUK FORM ARRAY)
            const barisBaru = document.createElement('tr');
            barisBaru.innerHTML = `
                <td><b>${merk}</b> <div class="small text-muted">${tipe}</div></td>
                <td><code class="text-primary fw-bold">${generatedSku}</code></td>
                <td><span class="fw-semibold text-secondary">${snValue}</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light border text-danger p-1 px-2 btn-hapus-row"><i class="fas fa-trash-alt small"></i></button>
                    
                    <input type="hidden" name="asset_id[]" value="${assetId}">
                    <input type="hidden" name="sku[]" value="${generatedSku}">
                    <input type="hidden" name="serial_number[]" value="${snValue}">
                </td>
            `;

            bodyStaging.appendChild(barisBaru);

            // D. UPDATE COUNTER & RE-SET FIELD INPUT
            runningCounter++; // Naikkan urutan sku otomatis untuk barang selanjutnya
            inputSerialNumber.value = ""; // Bersihkan kolom S/N saja
            inputSerialNumber.focus(); // Fokus standby kembali di S/N
            
            updateMetadataKeranjang();
        });

        // --- EVENT 3: KONTROL TOMBOL HAPUS UNIT DI DALAM KERANJANG ---
        bodyStaging.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-hapus-row') || e.target.closest('.btn-hapus-row')) {
                const row = e.target.closest('tr');
                row.remove();
                updateMetadataKeranjang();
            }
        });

        // --- FUNGSI AUXILIARY: HITUNG COUNTER TOTAL & LOCK/UNLOCK BUTTON SIMPAN ---
        function updateMetadataKeranjang() {
            const totalBaris = bodyStaging.querySelectorAll('tr').length;
            
            if (totalBaris === 0) {
                bodyStaging.appendChild(rowKosong);
                lblCountKeranjang.innerText = "0 Unit";
                btnSimpanUtama.disabled = true;
            } else {
                lblCountKeranjang.innerText = `${totalBaris} Unit`;
                btnSimpanUtama.disabled = false;
            }
        }
    });
</script>
@endsection