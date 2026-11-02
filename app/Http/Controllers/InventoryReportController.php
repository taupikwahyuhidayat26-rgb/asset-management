<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetItem;
use App\Models\Droppoint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = $this->filteredItems($filters);
        $statusCounts = (clone $query)
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $assetItems = $query->with(['catalog', 'droppoint'])->orderBy('sku')->paginate(25)->withQueryString();
        $categories = AssetCategory::orderBy('name')->get();
        $droppoints = Droppoint::orderBy('kode_dp')->get();

        return view('reports.inventory', [
            'assetItems' => $assetItems,
            'categories' => $categories,
            'droppoints' => $droppoints,
            'statusCounts' => $statusCounts,
            'totalUnits' => (int) $statusCounts->sum(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $query = $this->filteredItems($filters)->with(['catalog', 'droppoint'])->orderBy('sku');
        $filename = 'laporan-inventory-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka output ekspor inventory.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'SKU',
                'Serial Number',
                'Nama Model',
                'Kategori',
                'Merek',
                'Tipe',
                'Drop Point',
                'Status',
                'Keterangan',
                'Tanggal Dibuat',
            ]);

            $query->chunkById(500, function ($items) use ($output): void {
                foreach ($items as $item) {
                    $values = [
                        $item->sku,
                        $item->serial_number,
                        $item->catalog?->nama,
                        $item->catalog?->kategori,
                        $item->catalog?->merk,
                        $item->catalog?->tipe,
                        $item->droppoint?->nama_dp,
                        $item->status,
                        $item->keterangan,
                        $item->created_at?->toDateTimeString(),
                    ];

                    fputcsv($output, array_map(
                        fn ($value) => $this->safeSpreadsheetValue((string) ($value ?? '')),
                        $values
                    ));
                }
            }, 'asset_items.id', 'id');

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:Ready,Deployed,Maintenance,Broken'],
            'droppoint_id' => ['nullable', 'integer', 'exists:droppoint,id'],
        ]);
    }

    private function filteredItems(array $filters): Builder
    {
        $query = AssetItem::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $itemQuery) use ($search): void {
                $itemQuery->where('sku', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('catalog', function (Builder $catalogQuery) use ($search): void {
                        $catalogQuery->where('nama', 'like', "%{$search}%")
                            ->orWhere('merk', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['kategori'])) {
            $query->whereHas('catalog', fn (Builder $catalogQuery) => $catalogQuery->where('kategori', $filters['kategori']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['droppoint_id'])) {
            $query->where('droppoint_id', $filters['droppoint_id']);
        }

        return $query;
    }

    private function safeSpreadsheetValue(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
