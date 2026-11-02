<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode - {{ $asset->sku }}</title>
    <style>
        /* Reset margin default */
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
        }

        /* Desain Kotak Stiker Barcode */
        .barcode-label {
            display: inline-block;
            padding: 15px 25px;
            border: 2px dashed #000;
            border-radius: 8px;
            background-color: #fff;
        }

        /* Teks Nama Barang di atas Barcode */
        .item-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* Teks SKU di bawah Barcode */
        .sku-text {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
            letter-spacing: 2px;
        }

        /* Tombol Aksi (Hanya tampil di layar, hilang saat diprint) */
        .action-buttons {
            margin-bottom: 30px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin: 0 5px;
        }
        .btn-print { background-color: #4f46e5; color: white; }
        .btn-close { background-color: #e2e8f0; color: #333; }

        /* CSS KHUSUS PRINTER */
        @media print {
            /* Sembunyikan tombol saat kertas keluar dari printer */
            .action-buttons { display: none !important; }
            /* Hilangkan border putus-putus saat diprint ke stiker asli */
            .barcode-label { border: none; padding: 0; }
            /* Hilangkan margin bawaan browser */
            @page { margin: 0; }
            body { padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="action-buttons">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak Sekarang</button>
        <button class="btn btn-close" onclick="window.close()">❌ Tutup Tab</button>
    </div>

    <div class="barcode-label">
        <div class="item-name">{{ Str::limit($asset->catalog?->nama ?? 'VeloAssets Item', 25) }}</div>
        
        <div>
            {!! $barcodeSvg !!}
        </div>
        
        <div class="sku-text">{{ $asset->sku }}</div>
    </div>

</body>
</html>