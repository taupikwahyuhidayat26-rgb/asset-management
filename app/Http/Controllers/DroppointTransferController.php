<?php

namespace App\Http\Controllers;

use App\Models\AssetItem;
use App\Models\Droppoint;
use App\Models\DroppointTransfer;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DroppointTransferController extends Controller
{
    public function index()
    {
        $transactions = DroppointTransfer::with(['sourceDroppoint', 'destinationDroppoint'])
            ->withCount('details')
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(10);

        return view('inventory_movements.index', [
            'transactions' => $transactions,
            'movementType' => 'transfer',
            'title' => 'Transfer Antar Drop Point',
            'createRoute' => 'droppoint_transfers.create',
            'showRoute' => 'droppoint_transfers.show',
            'codeField' => 'transfer_code',
            'dateField' => 'transfer_date',
        ]);
    }

    public function create()
    {
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $droppoints = Droppoint::where('id', '!=', $warehouseId)->orderBy('kode_dp')->get();
        $assetItems = AssetItem::with('catalog')
            ->where('droppoint_id', '!=', $warehouseId)
            ->where('status', 'Deployed')
            ->orderBy('sku')
            ->get();

        return view('inventory_movements.create', [
            'movementType' => 'transfer',
            'title' => 'Transfer Antar Drop Point',
            'indexRoute' => 'droppoint_transfers.index',
            'storeRoute' => 'droppoint_transfers.store',
            'code' => $this->nextCode(),
            'dateField' => 'transfer_date',
            'sourceLabel' => 'Drop Point Asal',
            'droppoints' => $droppoints,
            'assetItems' => $assetItems,
        ]);
    }

    public function store(Request $request)
    {
        $warehouseId = (int) config('inventory.warehouse_droppoint_id', 1);
        $validated = $request->validate([
            'transfer_code' => 'required|string|max:255|unique:droppoint_transfers,transfer_code',
            'transfer_date' => 'required|date',
            'source_droppoint_id' => ['required', 'integer', 'exists:droppoint,id', Rule::notIn([$warehouseId])],
            'destination_droppoint_id' => [
                'required',
                'integer',
                'exists:droppoint,id',
                Rule::notIn([$warehouseId]),
                Rule::notIn([$request->input('source_droppoint_id')]),
            ],
            'asset_items' => 'required|array|min:1',
            'asset_items.*' => 'required|integer|distinct|exists:asset_items,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $occurredAt = Carbon::parse($validated['transfer_date'])->startOfDay();

        DB::transaction(function () use ($validated, $occurredAt): void {
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
                    || $unit->status !== 'Deployed') {
                    throw ValidationException::withMessages([
                        'asset_items' => "Unit {$unit->sku} tidak lagi berstatus Deployed di Drop Point asal.",
                    ]);
                }
            }

            $transaction = DroppointTransfer::create([
                'transfer_code' => $validated['transfer_code'],
                'transfer_date' => $validated['transfer_date'],
                'source_droppoint_id' => $validated['source_droppoint_id'],
                'destination_droppoint_id' => $validated['destination_droppoint_id'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($units as $unit) {
                $transaction->details()->create(['asset_item_id' => $unit->id]);
                $unit->update(['droppoint_id' => $validated['destination_droppoint_id']]);

                StockMutation::create([
                    'asset_item_id' => $unit->id,
                    'type' => 'transfer',
                    'from_droppoint_id' => $validated['source_droppoint_id'],
                    'to_droppoint_id' => $validated['destination_droppoint_id'],
                    'from_status' => 'Deployed',
                    'to_status' => 'Deployed',
                    'source_type' => 'droppoint_transfer',
                    'source_id' => $transaction->id,
                    'source_reference' => $transaction->transfer_code,
                    'notes' => $transaction->notes,
                    'user_id' => auth()->id(),
                    'occurred_at' => $occurredAt,
                ]);
            }
        });

        return redirect()->route('droppoint_transfers.index')->with('success', 'Transfer antar Drop Point berhasil disimpan.');
    }

    public function show(DroppointTransfer $droppointTransfer)
    {
        $transaction = $droppointTransfer->load([
            'sourceDroppoint',
            'destinationDroppoint',
            'details.assetItem.catalog',
            'user',
        ]);

        return view('inventory_movements.show', [
            'transaction' => $transaction,
            'movementType' => 'transfer',
            'title' => 'Detail Transfer Drop Point',
            'indexRoute' => 'droppoint_transfers.index',
        ]);
    }

    private function nextCode(): string
    {
        $date = now()->format('Ymd');
        $count = DroppointTransfer::whereDate('created_at', today())->count() + 1;

        return 'TF-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
