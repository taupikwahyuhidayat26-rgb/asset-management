<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\AssetItemImage;
use App\Models\Droppoint;
use App\Models\StockMutation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Milon\Barcode\Facades\DNS1DFacade;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetItemController extends Controller
{
    public function index(Request $request)
    {
        $assetItems = $this->filteredAssetItems($request)->latest()->paginate(10)->withQueryString();
        $catalogs = Asset::orderBy('nama')->get(); // Untuk dropdown pilihan cetakan
        $droppoints = Droppoint::orderBy('kode_dp')->get();

        return view('asset_item.index', compact('assetItems', 'catalogs', 'droppoints'));
    }

    public function print(Request $request)
    {
        $assetItems = $this->filteredAssetItems($request)->latest()->get();

        return view('asset_item.print', compact('assetItems'));
    }

    public function export(Request $request): StreamedResponse
    {
        $assetItems = $this->filteredAssetItems($request)->latest()->get();
        $filename = 'detail-item-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($assetItems) {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka output ekspor.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['SKU / Kode Unit', 'Nama Perangkat', 'Kategori', 'Nomor Seri', 'Drop Point', 'Status', 'Keterangan']);

            foreach ($assetItems as $item) {
                $values = [
                    $item->sku,
                    $item->catalog?->nama ?? 'Tanpa Model',
                    $item->catalog?->kategori ?? '-',
                    $item->serial_number ?? '-',
                    $item->droppoint?->kode_dp ?? '-',
                    $item->status,
                    $item->keterangan ?? '',
                ];

                fputcsv($output, array_map(fn ($value) => $this->safeSpreadsheetValue((string) $value), $values));
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Template import tidak dapat dibuat.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['nama_model', 'sku', 'serial_number', 'kode_dp', 'status', 'keterangan']);
            fclose($output);
        }, 'template-import-detail-item.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $handle = fopen($validated['file']->getRealPath(), 'rb');
        if ($handle === false) {
            throw new \RuntimeException('File CSV tidak dapat dibaca.');
        }

        $rows = [];
        $errors = [];
        $line = 1;
        $dataRowCount = 0;
        $expectedHeaders = ['nama_model', 'sku', 'serial_number', 'kode_dp', 'status', 'keterangan'];

        try {
            $headers = fgetcsv($handle, 0, ',', '"', '\\');
            if (! is_array($headers)) {
                throw ValidationException::withMessages(['file' => 'File CSV kosong atau tidak memiliki header.']);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
            if ($headers !== $expectedHeaders) {
                throw ValidationException::withMessages([
                    'file' => 'Header CSV tidak sesuai. Unduh template import agar nama dan urutan kolom benar.',
                ]);
            }

            while (($values = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $line++;
                if ($values === [null] || $values === []) {
                    continue;
                }

                $dataRowCount++;
                if ($dataRowCount > 5000) {
                    $errors[] = 'Maksimal 5.000 baris data dapat di-import sekaligus.';
                    break;
                }

                if (count($values) !== count($expectedHeaders)) {
                    $errors[] = "Baris {$line}: jumlah kolom tidak sesuai template.";

                    continue;
                }

                $row = array_combine($expectedHeaders, array_map(
                    fn ($value) => trim((string) $value),
                    $values
                ));

                foreach (['nama_model', 'sku', 'kode_dp', 'status'] as $required) {
                    if ($row[$required] === '') {
                        $errors[] = "Baris {$line}: kolom {$required} wajib diisi.";
                    }
                }
                foreach (['nama_model' => 255, 'sku' => 255, 'serial_number' => 255, 'kode_dp' => 50, 'status' => 255] as $column => $maxLength) {
                    $length = function_exists('mb_strlen') ? mb_strlen($row[$column], 'UTF-8') : strlen($row[$column]);
                    if ($length > $maxLength) {
                        $errors[] = "Baris {$line}: kolom {$column} maksimal {$maxLength} karakter.";
                    }
                }

                if (! in_array($row['status'], ['Ready', 'Deployed', 'Maintenance', 'Broken'], true)) {
                    $errors[] = "Baris {$line}: status harus Ready, Deployed, Maintenance, atau Broken.";
                }

                $row['_line'] = $line;
                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        if ($rows === [] && $errors === []) {
            $errors[] = 'File CSV tidak berisi data untuk di-import.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => array_slice($errors, 0, 100)]);
        }

        $catalogsByName = Asset::query()->get(['id', 'nama'])
            ->groupBy(fn (Asset $asset) => strtolower(trim($asset->nama)));
        $droppointIdsByCode = Droppoint::query()->pluck('id', 'kode_dp')
            ->mapWithKeys(fn ($id, $code) => [strtolower(trim((string) $code)) => (int) $id]);
        $existingSkus = AssetItem::query()->whereIn('sku', array_column($rows, 'sku'))->pluck('sku')
            ->map(fn ($sku) => strtolower((string) $sku))->flip();
        $seenSkus = [];
        $preparedRows = [];
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);

        foreach ($rows as $row) {
            $rowLine = $row['_line'];
            $skuKey = strtolower($row['sku']);
            $modelMatches = $catalogsByName->get(strtolower($row['nama_model']), collect());
            $droppointId = $droppointIdsByCode->get(strtolower($row['kode_dp']));

            if ($modelMatches->count() !== 1) {
                $errors[] = "Baris {$rowLine}: nama_model tidak ditemukan atau tidak unik pada Master Inventory.";
            }
            if ($droppointId === null) {
                $errors[] = "Baris {$rowLine}: kode_dp tidak ditemukan pada Master Drop Point.";
            }
            if (isset($existingSkus[$skuKey]) || isset($seenSkus[$skuKey])) {
                $errors[] = "Baris {$rowLine}: SKU {$row['sku']} sudah terdaftar atau duplikat di file.";
            }
            if ($row['status'] === 'Ready' && $droppointId !== $warehouseId) {
                $errors[] = "Baris {$rowLine}: status Ready harus menggunakan kode DP gudang.";
            }
            if ($row['status'] === 'Deployed' && $droppointId === $warehouseId) {
                $errors[] = "Baris {$rowLine}: status Deployed tidak boleh menggunakan kode DP gudang.";
            }

            if ($modelMatches->count() === 1 && $droppointId !== null) {
                $preparedRows[] = [
                    'asset_id' => $modelMatches->first()->id,
                    'sku' => $row['sku'],
                    'serial_number' => $row['serial_number'] !== '' ? $row['serial_number'] : null,
                    'droppoint_id' => $droppointId,
                    'status' => $row['status'],
                    'keterangan' => $row['keterangan'] !== '' ? $row['keterangan'] : null,
                ];
            }
            $seenSkus[$skuKey] = true;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => array_slice($errors, 0, 100)]);
        }

        DB::transaction(function () use ($preparedRows): void {
            foreach ($preparedRows as $row) {
                $assetItem = AssetItem::create($row);

                StockMutation::create([
                    'asset_item_id' => $assetItem->id,
                    'type' => 'adjustment',
                    'to_droppoint_id' => $assetItem->droppoint_id,
                    'to_status' => $assetItem->status,
                    'source_type' => 'csv_import',
                    'source_reference' => $assetItem->sku,
                    'notes' => 'Unit ditambahkan melalui import CSV.',
                    'user_id' => auth()->id(),
                    'occurred_at' => now(),
                ]);
            }
        });

        return redirect()->route('asset_item.index')
            ->with('success', count($preparedRows).' unit berhasil di-import.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'sku' => 'required|string|max:255|unique:asset_items,sku',
            'serial_number' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $validated['droppoint_id'] = $warehouseId;
        $validated['status'] = 'Ready';

        DB::transaction(function () use ($validated, $warehouseId): void {
            $assetItem = AssetItem::create($validated);

            StockMutation::create([
                'asset_item_id' => $assetItem->id,
                'type' => 'adjustment',
                'to_droppoint_id' => $warehouseId,
                'to_status' => 'Ready',
                'source_type' => 'manual',
                'source_reference' => $assetItem->sku,
                'notes' => 'Unit didaftarkan langsung ke inventaris.',
                'user_id' => auth()->id(),
                'occurred_at' => now(),
            ]);
        });

        return back()->with('success', 'Unit fisik perangkat berhasil didaftarkan ke sistem!');
    }

    public function update(Request $request, AssetItem $assetItem)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'sku' => ['required', 'string', 'max:255', Rule::unique('asset_items', 'sku')->ignore($assetItem->id)],
            'serial_number' => 'nullable|string|max:255',
            'droppoint_id' => 'required|exists:droppoint,id',
            'status' => 'required|in:Ready,Deployed,Maintenance,Broken',
            'keterangan' => 'nullable|string',
            'mutation_note' => 'nullable|string|max:1000',
        ]);

        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $droppointId = (int) $validated['droppoint_id'];
        if (($validated['status'] === 'Ready' && $droppointId !== $warehouseId)
            || ($validated['status'] === 'Deployed' && $droppointId === $warehouseId)) {
            throw ValidationException::withMessages([
                'status' => 'Status Ready hanya berlaku di gudang dan status Deployed harus berada di Drop Point.',
            ]);
        }

        DB::transaction(function () use ($validated, $assetItem): void {
            $lockedItem = AssetItem::query()->lockForUpdate()->findOrFail($assetItem->id);
            $fromDroppointId = $lockedItem->droppoint_id;
            $fromStatus = $lockedItem->status;

            $lockedItem->update(collect($validated)->except('mutation_note')->all());

            if ((int) $fromDroppointId !== (int) $lockedItem->droppoint_id || $fromStatus !== $lockedItem->status) {
                StockMutation::create([
                    'asset_item_id' => $lockedItem->id,
                    'type' => 'adjustment',
                    'from_droppoint_id' => $fromDroppointId,
                    'to_droppoint_id' => $lockedItem->droppoint_id,
                    'from_status' => $fromStatus,
                    'to_status' => $lockedItem->status,
                    'source_type' => 'manual',
                    'source_reference' => $lockedItem->sku,
                    'notes' => $validated['mutation_note'] ?? null,
                    'user_id' => auth()->id(),
                    'occurred_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Data unit berhasil diperbarui.');
    }

    public function destroy(AssetItem $assetItem)
    {
        foreach (['barang_masuk_details', 'barang_keluar_details', 'detail_barang_keluar'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('asset_item_id', $assetItem->id)->exists()) {
                return back()->withErrors([
                    'asset_item' => 'Unit tidak dapat dihapus karena sudah tercatat dalam riwayat transaksi.',
                ]);
            }
        }

        if ($assetItem->mutations()->exists()) {
            return back()->withErrors([
                'asset_item' => 'Unit tidak dapat dihapus karena memiliki riwayat mutasi.',
            ]);
        }

        $imagePaths = $assetItem->images()->pluck('path');
        $imageDisk = Storage::disk(config('filesystems.asset_images_disk'));
        if ($imagePaths->isNotEmpty() && ! $imageDisk->delete($imagePaths->all())) {
            throw new \RuntimeException('Foto unit tidak dapat dihapus dari penyimpanan.');
        }

        $assetItem->delete();

        return back()->with('success', 'Unit berhasil dihapus.');
    }

    public function storeImages(Request $request, AssetItem $assetItem)
    {
        $validated = $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'images.max' => 'Maksimal 10 foto dapat diunggah sekaligus.',
            'images.*.image' => 'Setiap file harus berupa gambar yang valid.',
            'images.*.mimes' => 'Format foto yang didukung: JPG, PNG, atau WebP.',
            'images.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $assetItem, &$storedPaths): void {
                foreach ($validated['images'] as $image) {
                    $path = $image->storeAs(
                        'asset-item-images/'.$assetItem->id,
                        $image->hashName(),
                        config('filesystems.asset_images_disk')
                    );

                    if ($path === false) {
                        throw new \RuntimeException('Foto unit gagal disimpan.');
                    }

                    $storedPaths[] = $path;
                    $assetItem->images()->create(['path' => $path]);
                }
            });
        } catch (\Throwable $exception) {
            $imageDisk = Storage::disk(config('filesystems.asset_images_disk'));
            if ($storedPaths !== [] && ! $imageDisk->delete($storedPaths)) {
                report(new \RuntimeException('Gagal membersihkan foto unit setelah proses penyimpanan dibatalkan.'));
            }

            throw $exception;
        }

        return back()->with('success', count($storedPaths).' foto berhasil ditambahkan ke unit '.$assetItem->sku.'.');
    }

    public function showImage(AssetItem $assetItem, AssetItemImage $image)
    {
        abort_unless($image->asset_item_id === $assetItem->id, 404);
        $imageDisk = Storage::disk(config('filesystems.asset_images_disk'));
        abort_unless($imageDisk->exists($image->path), 404);

        return $imageDisk->response($image->path);
    }

    public function destroyImage(AssetItem $assetItem, AssetItemImage $image)
    {
        abort_unless($image->asset_item_id === $assetItem->id, 404);

        if (! Storage::disk(config('filesystems.asset_images_disk'))->delete($image->path)) {
            throw new \RuntimeException('Foto unit tidak dapat dihapus dari penyimpanan.');
        }

        $image->delete();

        return back()->with('success', 'Foto unit berhasil dihapus.');
    }

    // Fungsi untuk mencetak Barcode SKU
    public function printBarcode($id)
    {
        $asset = AssetItem::with('catalog')->findOrFail($id);
        abort_if(blank($asset->sku), 422, 'SKU item belum tersedia sehingga barcode tidak dapat dibuat.');

        $barcodeSvg = DNS1DFacade::getBarcodeSVG($asset->sku, 'C128', 2, 60, 'black');

        return view('asset_item.print_barcode', compact('asset', 'barcodeSvg'));
    }

    private function filteredAssetItems(Request $request): Builder
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:Ready,Deployed,Maintenance,Broken',
        ]);

        $query = AssetItem::with(['catalog', 'droppoint', 'images']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($itemQuery) use ($search) {
                $itemQuery->where('sku', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('catalog', function ($catalogQuery) use ($search) {
                        $catalogQuery->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    private function safeSpreadsheetValue(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
