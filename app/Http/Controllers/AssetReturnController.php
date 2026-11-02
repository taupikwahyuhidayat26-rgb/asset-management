<?php

namespace App\Http\Controllers;

use App\Models\AssetItem;
use App\Models\AssetReturn;
use App\Models\Droppoint;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetReturnController extends Controller
{
    private const RETURNABLE_STATUSES = ['Deployed', 'Maintenance', 'Broken'];

    public function index()
    {
        $transactions = AssetReturn::with('sourceDroppoint')
            ->withCount('details')
            ->latest('return_date')
            ->latest('id')
            ->paginate(10);

        return view('inventory_movements.index', [
            'transactions' => $transactions,
            'movementType' => 'return',
            'title' => 'Transaksi Barang Kembali',
            'createRoute' => 'asset_returns.create',
            'showRoute' => 'asset_returns.show',
            'codeField' => 'return_code',
            'dateField' => 'return_date',
        ]);
    }

    public function create()
    {
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $droppoints = Droppoint::where('id', '!=', $warehouseId)->orderBy('kode_dp')->get();
        $assetItems = AssetItem::with('catalog')
            ->where('droppoint_id', '!=', $warehouseId)
            ->whereIn('status', self::RETURNABLE_STATUSES)
            ->orderBy('sku')
            ->get();

        return view('inventory_movements.create', [
            'movementType' => 'return',
            'title' => 'Transaksi Barang Kembali',
            'indexRoute' => 'asset_returns.index',
            'storeRoute' => 'asset_returns.store',
            'code' => $this->nextCode('RT'),
            'dateField' => 'return_date',
            'sourceLabel' => 'Drop Point Asal',
            'droppoints' => $droppoints,
            'assetItems' => $assetItems,
        ]);
    }

    public function store(Request $request)
    {
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $validated = $request->validate([
            'return_code' => 'required|string|max:255|unique:asset_returns,return_code',
            'return_date' => 'required|date',
            'source_droppoint_id' => ['required', 'integer', 'exists:droppoint,id', Rule::notIn([$warehouseId])],
            'destination_status' => ['required', Rule::in(['Ready', 'Maintenance', 'Broken'])],
            'asset_items' => 'required|array|min:1',
            'asset_items.*' => 'required|integer|distinct|exists:asset_items,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $occurredAt = Carbon::parse($validated['return_date'])->startOfDay();

        DB::transaction(function () use ($validated, $warehouseId, $occurredAt): void {
            $units = AssetItem::query()
                ->whereIn('id', $validated['asset_items'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($units->count() !== count($validated['asset_items'])) {
                throw ValidationException::withMessages([
                    'asset_items' => 'Sebagian unit tidak ditemukan. Muat ulang halaman dan coba kembali.',
                ]);
            }

            foreach ($units as $unit) {
                if ((int) $unit->droppoint_id !== (int) $validated['source_droppoint_id']
                    || ! in_array($unit->status, self::RETURNABLE_STATUSES, true)) {
                    throw ValidationException::withMessages([
                        'asset_items' => "Unit {$unit->sku} tidak lagi tersedia di Drop Point asal untuk diretur.",
                    ]);
                }
            }

            $transaction = AssetReturn::create([
                'return_code' => $validated['return_code'],
                'return_date' => $validated['return_date'],
                'source_droppoint_id' => $validated['source_droppoint_id'],
                'destination_status' => $validated['destination_status'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($units as $unit) {
                $fromStatus = $unit->status;

                $transaction->details()->create(['asset_item_id' => $unit->id]);
                $unit->update([
                    'droppoint_id' => $warehouseId,
                    'status' => $validated['destination_status'],
                ]);

                StockMutation::create([
                    'asset_item_id' => $unit->id,
                    'type' => 'return',
                    'from_droppoint_id' => $validated['source_droppoint_id'],
                    'to_droppoint_id' => $warehouseId,
                    'from_status' => $fromStatus,
                    'to_status' => $validated['destination_status'],
                    'source_type' => 'asset_return',
                    'source_id' => $transaction->id,
                    'source_reference' => $transaction->return_code,
                    'notes' => $transaction->notes,
                    'user_id' => auth()->id(),
                    'occurred_at' => $occurredAt,
                ]);
            }
        });

        return redirect()->route('asset_returns.index')->with('success', 'Transaksi barang kembali berhasil disimpan.');
    }

    public function show(AssetReturn $assetReturn)
    {
        $transaction = $assetReturn->load(['sourceDroppoint', 'details.assetItem.catalog', 'user']);

        return view('inventory_movements.show', [
            'transaction' => $transaction,
            'movementType' => 'return',
            'title' => 'Detail Barang Kembali',
            'indexRoute' => 'asset_returns.index',
        ]);
    }

    private function nextCode(string $prefix): string
    {
        $date = now()->format('Ymd');
        $count = AssetReturn::whereDate('created_at', today())->count() + 1;

        return $prefix.'-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
