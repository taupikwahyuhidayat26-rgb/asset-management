<?php

use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetItemController;
use App\Http\Controllers\AssetReturnController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DroppointController;
use App\Http\Controllers\DroppointTransferController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDroppointMutationController;
use App\Http\Controllers\EmployeePositionMutationController;
use App\Http\Controllers\EmployeeResignationController;
use App\Http\Controllers\EmployeeTransactionController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SystemIdChangeController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// --- RUTE OTENTIKASI (TERBUKA) ---
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- RUTE APLIKASI (DIKUNCI / HARUS LOGIN DULU) ---
Route::middleware('auth')->group(function () {

    // Rute Halaman Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware('admin')->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/download', [BackupController::class, 'download'])->name('backups.download');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    // Rute Halaman Asset
    Route::get('/asset', [AssetController::class, 'index'])->name('asset.index');
    Route::get('/asset-categories', [AssetCategoryController::class, 'index'])->name('categories.index');
    Route::post('/asset-categories', [AssetCategoryController::class, 'store'])->middleware('data-access:manage')->name('categories.store');
    Route::put('/asset-categories/{category}', [AssetCategoryController::class, 'update'])->middleware('data-access:manage')->name('categories.update');
    Route::delete('/asset-categories/{category}', [AssetCategoryController::class, 'destroy'])->middleware('data-access:manage')->name('categories.destroy');
    Route::get('/inventory-report', [InventoryReportController::class, 'index'])->middleware('data-access:view')->name('inventory_report.index');
    Route::get('/inventory-report/export', [InventoryReportController::class, 'export'])->middleware('data-access:view')->name('inventory_report.export');
    // Rute Baru Khusus Detail Unit Fisik Item
    Route::get('/asset-item', [AssetItemController::class, 'index'])->name('asset_item.index');
    Route::post('/asset-item', [AssetItemController::class, 'store'])->middleware('data-access:manage')->name('asset_item.store');
    Route::get('/asset-item/print', [AssetItemController::class, 'print'])->middleware('data-access:manage')->name('asset_item.print');
    Route::get('/asset-item/export', [AssetItemController::class, 'export'])->middleware('data-access:manage')->name('asset_item.export');
    Route::get('/asset-item/import/template', [AssetItemController::class, 'importTemplate'])->middleware('data-access:manage')->name('asset_item.import.template');
    Route::post('/asset-item/import', [AssetItemController::class, 'import'])->middleware('data-access:manage')->name('asset_item.import');
    Route::put('/asset-item/{assetItem}', [AssetItemController::class, 'update'])->middleware('data-access:manage')->name('asset_item.update');
    Route::delete('/asset-item/{assetItem}', [AssetItemController::class, 'destroy'])->middleware('data-access:manage')->name('asset_item.destroy');
    Route::post('/asset-item/{assetItem}/images', [AssetItemController::class, 'storeImages'])->middleware('data-access:manage')->name('asset_item.images.store');
    Route::get('/asset-item/{assetItem}/images/{image}', [AssetItemController::class, 'showImage'])->name('asset_item.images.show');
    Route::delete('/asset-item/{assetItem}/images/{image}', [AssetItemController::class, 'destroyImage'])->middleware('data-access:manage')->name('asset_item.images.destroy');
    // Rute untuk Cetak Barcode SKU AssetItem
    Route::get('/asset-items/{id}/print-barcode', [AssetItemController::class, 'printBarcode'])->middleware('data-access:manage')->name('asset_items.print_barcode');

    Route::post('/asset', [AssetController::class, 'store'])->middleware('data-access:manage')->name('asset.store');
    Route::put('/asset/{asset}', [AssetController::class, 'update'])->middleware('data-access:manage')->name('asset.update');
    Route::delete('/asset/{asset}', [AssetController::class, 'destroy'])->middleware('data-access:manage')->name('asset.destroy');
    // Berikan tanda tanya ({id?}) agar parameternya bersifat opsional (tidak wajib)
    Route::get('/asset/kartu-stok/{id?}', [AssetController::class, 'kartuStok'])->name('asset.kartu_stok');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('/supplier', [SupplierController::class, 'store'])->middleware('data-access:manage')->name('supplier.store');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->middleware('data-access:manage')->name('supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->middleware('data-access:manage')->name('supplier.destroy');

    // Rute Master Data Drop Point
    Route::get('/droppoint', [DroppointController::class, 'index'])->name('droppoint.index');
    Route::post('/droppoint', [DroppointController::class, 'store'])->middleware('data-access:manage')->name('droppoint.store');
    Route::put('/droppoint/{id}', [DroppointController::class, 'update'])->middleware('data-access:manage')->name('droppoint.update');
    Route::delete('/droppoint/{id}', [DroppointController::class, 'destroy'])->middleware('data-access:manage')->name('droppoint.destroy');

    Route::get('/employees/create', [EmployeeController::class, 'create'])->middleware('data-access:manage')->name('employees.create');
    Route::resource('employees', EmployeeController::class)->only(['index']);
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('data-access:manage')->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('data-access:manage')->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('data-access:manage')->name('employees.destroy');
    Route::get('/employee-transactions/id-creation', [EmployeeTransactionController::class, 'index'])->defaults('type', 'id-creation')->name('employee_transactions.id_creation');
    Route::put('/employee-transactions/id-creation/{id}/status', [EmployeeTransactionController::class, 'updateStatus'])->middleware('data-access:manage')->name('employee_transactions.id_creation.status');
    Route::delete('/employee-transactions/id-creation/{id}', [EmployeeTransactionController::class, 'destroy'])->middleware('data-access:manage')->name('employee_transactions.id_creation.destroy');
    Route::get('/employee-transactions/system-id', [SystemIdChangeController::class, 'index'])->name('employee_transactions.system_id');
    Route::get('/employee-transactions/system-id/create', [SystemIdChangeController::class, 'create'])->middleware('data-access:manage')->name('employee_transactions.system_id.create');
    Route::post('/employee-transactions/system-id', [SystemIdChangeController::class, 'store'])->middleware('data-access:manage')->name('employee_transactions.system_id.store');
    Route::put('/employee-transactions/system-id/{id}/status', [SystemIdChangeController::class, 'updateStatus'])->middleware('data-access:manage')->name('employee_transactions.system_id.status');
    Route::delete('/employee-transactions/system-id/{id}', [SystemIdChangeController::class, 'destroy'])->middleware('data-access:manage')->name('employee_transactions.system_id.destroy');

    Route::get('/employee-transactions/resignation', [EmployeeResignationController::class, 'index'])->name('employee_transactions.resignation');
    Route::get('/employee-transactions/resignation/create', [EmployeeResignationController::class, 'create'])->middleware('data-access:manage')->name('employee_transactions.resignation.create');
    Route::post('/employee-transactions/resignation', [EmployeeResignationController::class, 'store'])->middleware('data-access:manage')->name('employee_transactions.resignation.store');
    Route::put('/employee-transactions/resignation/{id}/status', [EmployeeResignationController::class, 'updateStatus'])->middleware('data-access:manage')->name('employee_transactions.resignation.status');
    Route::delete('/employee-transactions/resignation/{id}', [EmployeeResignationController::class, 'destroy'])->middleware('data-access:manage')->name('employee_transactions.resignation.destroy');

    Route::get('/employee-transactions/position-mutation', [EmployeePositionMutationController::class, 'index'])->name('employee_transactions.position_mutation');
    Route::get('/employee-transactions/position-mutation/create', [EmployeePositionMutationController::class, 'create'])->middleware('data-access:manage')->name('employee_transactions.position_mutation.create');
    Route::post('/employee-transactions/position-mutation', [EmployeePositionMutationController::class, 'store'])->middleware('data-access:manage')->name('employee_transactions.position_mutation.store');
    Route::put('/employee-transactions/position-mutation/{id}/status', [EmployeePositionMutationController::class, 'updateStatus'])->middleware('data-access:manage')->name('employee_transactions.position_mutation.status');
    Route::delete('/employee-transactions/position-mutation/{id}', [EmployeePositionMutationController::class, 'destroy'])->middleware('data-access:manage')->name('employee_transactions.position_mutation.destroy');

    Route::get('/employee-transactions/droppoint-mutation', [EmployeeDroppointMutationController::class, 'index'])->name('employee_transactions.droppoint_mutation');
    Route::get('/employee-transactions/droppoint-mutation/create', [EmployeeDroppointMutationController::class, 'create'])->middleware('data-access:manage')->name('employee_transactions.droppoint_mutation.create');
    Route::post('/employee-transactions/droppoint-mutation', [EmployeeDroppointMutationController::class, 'store'])->middleware('data-access:manage')->name('employee_transactions.droppoint_mutation.store');
    Route::put('/employee-transactions/droppoint-mutation/{id}/status', [EmployeeDroppointMutationController::class, 'updateStatus'])->middleware('data-access:manage')->name('employee_transactions.droppoint_mutation.status');
    Route::delete('/employee-transactions/droppoint-mutation/{id}', [EmployeeDroppointMutationController::class, 'destroy'])->middleware('data-access:manage')->name('employee_transactions.droppoint_mutation.destroy');

    // Transaksi Barang Masuk
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang_masuk.index');
    Route::get('/barang-masuk/create', [BarangMasukController::class, 'create'])->middleware('data-access:manage')->name('barang_masuk.create');
    Route::get('/barang-masuk/{id}', [BarangMasukController::class, 'show'])->name('barang_masuk.show'); // Rute Baru untuk Detail
    Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->middleware('data-access:manage')->name('barang_masuk.store');

    // --- TAMBAHKAN BARIS INI UNTUK BARANG KELUAR ---
    // 1. Halaman Utama Daftar Barang Keluar
    Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang_keluar.index');

    // 2. Halaman Form Input Transaksi Baru (Ini yang membuat error kemarin karena baris ini belum ada)
    Route::get('/barang-keluar/create', [BarangKeluarController::class, 'create'])->middleware('data-access:manage')->name('barang_keluar.create');

    // 3. Proses Simpan Data Transaksi Baru ke Database
    Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->middleware('data-access:manage')->name('barang_keluar.store');

    // 4. Halaman Detail Tampilan Per Transaksi
    Route::get('/barang-keluar/{id}', [BarangKeluarController::class, 'show'])->name('barang_keluar.show');

    Route::get('/asset-returns', [AssetReturnController::class, 'index'])->name('asset_returns.index');
    Route::get('/asset-returns/create', [AssetReturnController::class, 'create'])->middleware('data-access:manage')->name('asset_returns.create');
    Route::post('/asset-returns', [AssetReturnController::class, 'store'])->middleware('data-access:manage')->name('asset_returns.store');
    Route::get('/asset-returns/{assetReturn}', [AssetReturnController::class, 'show'])->name('asset_returns.show');

    Route::get('/droppoint-transfers', [DroppointTransferController::class, 'index'])->name('droppoint_transfers.index');
    Route::get('/droppoint-transfers/create', [DroppointTransferController::class, 'create'])->middleware('data-access:manage')->name('droppoint_transfers.create');
    Route::post('/droppoint-transfers', [DroppointTransferController::class, 'store'])->middleware('data-access:manage')->name('droppoint_transfers.store');
    Route::get('/droppoint-transfers/{droppointTransfer}', [DroppointTransferController::class, 'show'])->name('droppoint_transfers.show');

});
