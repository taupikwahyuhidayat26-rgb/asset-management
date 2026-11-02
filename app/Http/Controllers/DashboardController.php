<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\StockMutation;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $statusCounts = AssetItem::query()
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalUnits = (int) $statusCounts->sum();
        $assetReady = (int) ($statusCounts['Ready'] ?? 0);
        $assetDeployed = (int) ($statusCounts['Deployed'] ?? 0);
        $statusMaintenance = (int) ($statusCounts['Maintenance'] ?? 0);
        $statusBroken = (int) ($statusCounts['Broken'] ?? 0);

        $latestMutations = StockMutation::with([
            'assetItem.catalog',
            'fromDroppoint',
            'toDroppoint',
        ])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $topCatalogs = Asset::withCount('items')
            ->orderByDesc('items_count')
            ->orderBy('nama')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'totalCatalogs' => Asset::count(),
            'totalUnits' => $totalUnits,
            'assetReady' => $assetReady,
            'assetDeployed' => $assetDeployed,
            'statusMaintenance' => $statusMaintenance,
            'statusBroken' => $statusBroken,
            'totalSupplier' => Supplier::count(),
            'latestMutations' => $latestMutations,
            'topCatalogs' => $topCatalogs,
        ]);
    }
}
