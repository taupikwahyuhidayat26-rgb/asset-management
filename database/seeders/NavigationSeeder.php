<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Group Utama
        $grupUtama = MenuGroup::create(['name' => 'Utama', 'sort_order' => 1]);
        $grupUtama->menus()->create([
            'name' => 'Dashboard Overview', 'route' => 'dashboard', 'icon' => 'fas fa-chart-pie', 'sort_order' => 1,
        ]);

        // 2. Group Master Data (Ditambahkan Menu Detail Item)
        $grupMaster = MenuGroup::create(['name' => 'Master Data', 'sort_order' => 2]);
        $grupMaster->menus()->createMany([
            ['name' => 'Master Inventory', 'route' => 'asset.index', 'icon' => 'fas fa-boxes', 'sort_order' => 1],
            ['name' => 'Detail Item', 'route' => 'asset_item.index', 'icon' => 'fas fa-barcode', 'sort_order' => 2], // Baru!
            ['name' => 'Kategori Aset', 'route' => 'categories.index', 'icon' => 'fas fa-tags', 'sort_order' => 3],
            ['name' => 'Vendor Supplier', 'route' => 'supplier.index', 'icon' => 'fas fa-handshake', 'sort_order' => 4],
        ]);

        // 3. Group Transaksi
        $grupTransaksi = MenuGroup::create(['name' => 'Transaksi', 'sort_order' => 3]);
        $grupTransaksi->menus()->createMany([
            ['name' => 'Barang Masuk', 'route' => 'barang_masuk.index', 'icon' => 'fas fa-arrow-down-left text-success', 'sort_order' => 1],
            ['name' => 'Barang Keluar', 'route' => 'barang_keluar.index', 'icon' => 'fas fa-arrow-up-right text-danger', 'sort_order' => 2],
            ['name' => 'Barang Kembali', 'route' => 'asset_returns.index', 'icon' => 'fas fa-rotate-left text-info', 'sort_order' => 3],
            ['name' => 'Transfer Drop Point', 'route' => 'droppoint_transfers.index', 'icon' => 'fas fa-right-left text-warning', 'sort_order' => 4],
        ]);

        // 4. Group Laporan
        $grupLaporan = MenuGroup::create(['name' => 'Laporan', 'sort_order' => 4]);
        $grupLaporan->menus()->create([
            'name' => 'Kartu Stok Perangkat', 'route' => 'asset.kartu_stok', 'icon' => 'fas fa-history text-warning', 'sort_order' => 1,
        ]);
        $grupLaporan->menus()->create([
            'name' => 'Laporan Inventory', 'route' => 'inventory_report.index', 'icon' => 'fas fa-file-csv text-success', 'sort_order' => 2,
        ]);
    }
}
