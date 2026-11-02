<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Daftar Detail Item</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { color: #4b5563; font-size: 12px; margin-bottom: 18px; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; }
        th, td { border: 1px solid #9ca3af; padding: 7px; text-align: left; }
        th { background: #e5e7eb; }
        .actions { margin-bottom: 16px; }
        button { padding: 8px 14px; cursor: pointer; }
        @media print {
            body { margin: 0; }
            .actions { display: none; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Cetak</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>
    <h1>Daftar Detail Item Unit Fisik</h1>
    <div class="meta">
        Dicetak {{ now()->format('d-m-Y H:i') }} |
        Jumlah {{ $assetItems->count() }} unit
        @if(request('search')) | Pencarian: {{ request('search') }} @endif
        @if(request('status')) | Status: {{ request('status') }} @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>SKU / Kode Unit</th>
                <th>Nama Perangkat</th>
                <th>Kategori</th>
                <th>Nomor Seri</th>
                <th>Drop Point</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assetItems as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->catalog?->nama ?? 'Tanpa Model' }}</td>
                    <td>{{ $item->catalog?->kategori ?? '-' }}</td>
                    <td>{{ $item->serial_number ?? '-' }}</td>
                    <td>{{ $item->droppoint?->kode_dp ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada item yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
