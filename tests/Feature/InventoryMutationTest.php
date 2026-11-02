<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\AssetReturn;
use App\Models\DroppointTransfer;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryMutationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'menus',
            'menu_groups',
            'asset_categories',
            'droppoint_transfer_details',
            'droppoint_transfers',
            'asset_return_details',
            'asset_returns',
            'stock_mutations',
            'barang_keluar_details',
            'barang_keluars',
            'barang_masuk_details',
            'barang_masuks',
            'asset_items',
            'droppoint',
            'suppliers',
            'assets',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('role')->default('User');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->string('kategori');
            $table->string('nama');
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->timestamps();
        });
        Schema::create('asset_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        DB::table('asset_categories')->insert([
            ['name' => 'PC/Komputer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CCTV', 'created_at' => now(), 'updated_at' => now()],
        ]);
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_supplier');
            $table->string('alamat_supplier')->nullable();
            $table->string('email')->nullable();
            $table->string('no_telepon')->nullable();
            $table->timestamps();
        });
        Schema::create('droppoint', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_dp');
            $table->string('nama_dp');
            $table->timestamps();
        });
        Schema::create('asset_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('sku')->unique();
            $table->string('serial_number')->nullable()->unique();
            $table->unsignedBigInteger('droppoint_id');
            $table->string('status')->default('Ready');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
        Schema::create('barang_masuks', function (Blueprint $table): void {
            $table->id();
            $table->date('tanggal_masuk');
            $table->string('no_barang_masuk')->unique();
            $table->string('no_po')->nullable();
            $table->string('no_invoice');
            $table->unsignedBigInteger('supplier_id');
            $table->timestamps();
        });
        Schema::create('barang_masuk_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('barang_masuk_id');
            $table->unsignedBigInteger('asset_item_id');
            $table->timestamps();
        });
        Schema::create('barang_keluars', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->date('tanggal_keluar');
            $table->unsignedBigInteger('droppoint_id');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
        Schema::create('barang_keluar_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('barang_keluar_id');
            $table->unsignedBigInteger('asset_item_id');
            $table->timestamps();
        });
        Schema::create('stock_mutations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_item_id');
            $table->string('type');
            $table->unsignedBigInteger('from_droppoint_id')->nullable();
            $table->unsignedBigInteger('to_droppoint_id')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
        });
        Schema::create('asset_returns', function (Blueprint $table): void {
            $table->id();
            $table->string('return_code')->unique();
            $table->date('return_date');
            $table->unsignedBigInteger('source_droppoint_id');
            $table->string('destination_status');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('asset_return_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_return_id');
            $table->unsignedBigInteger('asset_item_id');
            $table->timestamps();
        });
        Schema::create('droppoint_transfers', function (Blueprint $table): void {
            $table->id();
            $table->string('transfer_code')->unique();
            $table->date('transfer_date');
            $table->unsignedBigInteger('source_droppoint_id');
            $table->unsignedBigInteger('destination_droppoint_id');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('droppoint_transfer_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('droppoint_transfer_id');
            $table->unsignedBigInteger('asset_item_id');
            $table->timestamps();
        });
        Schema::create('menu_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('menu_group_id');
            $table->string('name');
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        config(['inventory.warehouse_droppoint_id' => 1]);
        DB::table('droppoint')->insert([
            ['id' => 1, 'kode_dp' => 'WH', 'nama_dp' => 'Gudang IT'],
            ['id' => 2, 'kode_dp' => 'DP-02', 'nama_dp' => 'Drop Point 02'],
            ['id' => 3, 'kode_dp' => 'DP-03', 'nama_dp' => 'Drop Point 03'],
        ]);
    }

    public function test_barang_masuk_places_unit_in_warehouse_and_records_receipt(): void
    {
        $this->actingAs($this->staff());
        $asset = $this->catalog();
        $supplierId = DB::table('suppliers')->insertGetId(['nama_supplier' => 'Supplier A']);

        $response = $this->post(route('barang_masuk.store'), [
            'tanggal_masuk' => '2026-10-29',
            'no_barang_masuk' => 'BM-001',
            'no_invoice' => 'INV-001',
            'supplier_id' => $supplierId,
            'asset_id' => [$asset->id],
            'sku' => ['SKU-001'],
            'serial_number' => ['SN-001'],
        ]);

        $response->assertRedirect(route('barang_masuk.index'));
        $item = AssetItem::where('sku', 'SKU-001')->firstOrFail();
        $this->assertSame(1, (int) $item->droppoint_id);
        $this->assertSame('Ready', $item->status);
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'receipt',
            'to_droppoint_id' => 1,
            'to_status' => 'Ready',
            'source_reference' => 'BM-001',
        ]);
    }

    public function test_barang_masuk_reports_duplicate_invoice_before_saving(): void
    {
        $this->actingAs($this->staff());
        $asset = $this->catalog();
        $supplierId = DB::table('suppliers')->insertGetId(['nama_supplier' => 'Supplier A']);
        DB::table('barang_masuks')->insert([
            'tanggal_masuk' => '2026-10-28',
            'no_barang_masuk' => 'BM-EXISTING',
            'no_invoice' => 'INV-DUPLICATE',
            'supplier_id' => $supplierId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('barang_masuk.create'))
            ->post(route('barang_masuk.store'), [
                'tanggal_masuk' => '2026-10-29',
                'no_barang_masuk' => 'BM-NEW',
                'no_invoice' => 'INV-DUPLICATE',
                'supplier_id' => $supplierId,
                'asset_id' => [$asset->id],
                'sku' => ['SKU-NEW'],
                'serial_number' => ['SN-NEW'],
            ]);

        $response->assertRedirect(route('barang_masuk.create'))
            ->assertSessionHasErrors('no_invoice');
        $this->assertDatabaseCount('barang_masuks', 1);
        $this->assertDatabaseMissing('asset_items', ['sku' => 'SKU-NEW']);
    }

    public function test_barang_masuk_reports_missing_warehouse_instead_of_failing_database_write(): void
    {
        $this->actingAs($this->staff());
        config(['inventory.warehouse_droppoint_id' => 999]);
        $asset = $this->catalog();
        $supplierId = DB::table('suppliers')->insertGetId(['nama_supplier' => 'Supplier A']);

        $response = $this->from(route('barang_masuk.create'))
            ->post(route('barang_masuk.store'), [
                'tanggal_masuk' => '2026-10-29',
                'no_barang_masuk' => 'BM-NO-WAREHOUSE',
                'no_invoice' => 'INV-NO-WAREHOUSE',
                'supplier_id' => $supplierId,
                'asset_id' => [$asset->id],
                'sku' => ['SKU-NO-WAREHOUSE'],
                'serial_number' => ['SN-NO-WAREHOUSE'],
            ]);

        $response->assertRedirect(route('barang_masuk.create'))
            ->assertSessionHasErrors('warehouse');
        $this->assertDatabaseCount('barang_masuks', 0);
        $this->assertDatabaseMissing('asset_items', ['sku' => 'SKU-NO-WAREHOUSE']);
    }

    public function test_staff_can_update_catalog_details(): void
    {
        $this->actingAs($this->staff());
        $asset = $this->catalog();

        $response = $this->put(route('asset.update', $asset), [
            'kategori' => 'CCTV',
            'nama' => 'Kamera Gudang',
            'merk' => 'Contoh',
            'tipe' => 'Dome',
            'spesifikasi' => 'Resolusi 4MP',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'kategori' => 'CCTV',
            'nama' => 'Kamera Gudang',
        ]);
    }

    public function test_catalog_with_units_cannot_be_deleted(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-CATALOG-001');

        $response = $this->delete(route('asset.destroy', $item->asset_id));

        $response->assertSessionHasErrors('asset');
        $this->assertDatabaseHas('assets', ['id' => $item->asset_id]);
    }

    public function test_unused_catalog_can_be_deleted(): void
    {
        $this->actingAs($this->staff());
        $asset = $this->catalog();

        $response = $this->delete(route('asset.destroy', $asset));

        $response->assertRedirect();
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_barang_keluar_updates_location_and_status_and_records_mutation(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-002');

        $response = $this->post(route('barang_keluar.store'), [
            'kode_transaksi' => 'OUT-001',
            'tanggal_keluar' => '2026-10-30',
            'droppoint_id' => 2,
            'asset_items' => [$item->id],
            'keterangan' => 'Dikirim untuk operasional',
        ]);

        $response->assertRedirect(route('barang_keluar.index'));
        $item->refresh();
        $this->assertSame(2, (int) $item->droppoint_id);
        $this->assertSame('Deployed', $item->status);
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'issue',
            'from_droppoint_id' => 1,
            'to_droppoint_id' => 2,
            'from_status' => 'Ready',
            'to_status' => 'Deployed',
            'source_reference' => 'OUT-001',
        ]);
    }

    public function test_barang_keluar_rejects_units_not_ready_in_warehouse(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-003', 2, 'Deployed');

        $response = $this->post(route('barang_keluar.store'), [
            'kode_transaksi' => 'OUT-002',
            'tanggal_keluar' => '2026-10-30',
            'droppoint_id' => 2,
            'asset_items' => [$item->id],
        ]);

        $response->assertSessionHasErrors('asset_items');
        $this->assertDatabaseCount('barang_keluars', 0);
        $this->assertDatabaseCount('stock_mutations', 0);
    }

    public function test_return_moves_deployed_unit_to_warehouse_and_records_transaction(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-RETURN-001', 2, 'Deployed');

        $response = $this->post(route('asset_returns.store'), [
            'return_code' => 'RT-001',
            'return_date' => '2026-10-30',
            'source_droppoint_id' => 2,
            'destination_status' => 'Maintenance',
            'asset_items' => [$item->id],
            'notes' => 'Perlu pemeriksaan teknis',
        ]);

        $response->assertRedirect(route('asset_returns.index'));
        $item->refresh();
        $this->assertSame(1, (int) $item->droppoint_id);
        $this->assertSame('Maintenance', $item->status);
        $this->assertDatabaseHas('asset_returns', [
            'return_code' => 'RT-001',
            'source_droppoint_id' => 2,
            'destination_status' => 'Maintenance',
        ]);
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'return',
            'from_droppoint_id' => 2,
            'to_droppoint_id' => 1,
            'from_status' => 'Deployed',
            'to_status' => 'Maintenance',
            'source_reference' => 'RT-001',
        ]);
        $this->get(route('asset_returns.show', AssetReturn::firstOrFail()))
            ->assertOk()
            ->assertSee('RT-001')
            ->assertSee('Perlu pemeriksaan teknis');
        $this->get(route('asset_returns.index'))
            ->assertOk()
            ->assertSee('RT-001');
    }

    public function test_return_rejects_unit_that_is_not_at_the_selected_drop_point(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-RETURN-002', 2, 'Deployed');

        $response = $this->post(route('asset_returns.store'), [
            'return_code' => 'RT-002',
            'return_date' => '2026-10-30',
            'source_droppoint_id' => 3,
            'destination_status' => 'Ready',
            'asset_items' => [$item->id],
        ]);

        $response->assertSessionHasErrors('asset_items');
        $this->assertSame(2, (int) $item->fresh()->droppoint_id);
        $this->assertDatabaseCount('asset_returns', 0);
    }

    public function test_transfer_moves_deployed_unit_between_drop_points_and_records_transaction(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-TRANSFER-001', 2, 'Deployed');

        $response = $this->post(route('droppoint_transfers.store'), [
            'transfer_code' => 'TF-001',
            'transfer_date' => '2026-10-30',
            'source_droppoint_id' => 2,
            'destination_droppoint_id' => 3,
            'asset_items' => [$item->id],
            'notes' => 'Perubahan penempatan',
        ]);

        $response->assertRedirect(route('droppoint_transfers.index'));
        $item->refresh();
        $this->assertSame(3, (int) $item->droppoint_id);
        $this->assertSame('Deployed', $item->status);
        $this->assertDatabaseHas('droppoint_transfers', [
            'transfer_code' => 'TF-001',
            'source_droppoint_id' => 2,
            'destination_droppoint_id' => 3,
        ]);
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'transfer',
            'from_droppoint_id' => 2,
            'to_droppoint_id' => 3,
            'from_status' => 'Deployed',
            'to_status' => 'Deployed',
            'source_reference' => 'TF-001',
        ]);
        $this->get(route('droppoint_transfers.show', DroppointTransfer::firstOrFail()))
            ->assertOk()
            ->assertSee('TF-001')
            ->assertSee('Perubahan penempatan');
        $this->get(route('droppoint_transfers.index'))
            ->assertOk()
            ->assertSee('TF-001');
    }

    public function test_return_and_transfer_creation_pages_are_available_to_staff(): void
    {
        $this->actingAs($this->staff());

        $this->get(route('asset_returns.create'))
            ->assertOk()
            ->assertSee('Transaksi Barang Kembali');

        $this->get(route('droppoint_transfers.create'))
            ->assertOk()
            ->assertSee('Transfer Antar Drop Point');
    }

    public function test_transfer_rejects_same_source_and_destination(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-TRANSFER-002', 2, 'Deployed');

        $response = $this->post(route('droppoint_transfers.store'), [
            'transfer_code' => 'TF-002',
            'transfer_date' => '2026-10-30',
            'source_droppoint_id' => 2,
            'destination_droppoint_id' => 2,
            'asset_items' => [$item->id],
        ]);

        $response->assertSessionHasErrors('destination_droppoint_id');
        $this->assertDatabaseCount('droppoint_transfers', 0);
    }

    public function test_manual_status_and_location_changes_are_recorded_and_consistent(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-004');

        $response = $this->put(route('asset_item.update', $item), [
            'asset_id' => $item->asset_id,
            'sku' => $item->sku,
            'serial_number' => $item->serial_number,
            'droppoint_id' => 2,
            'status' => 'Deployed',
            'keterangan' => 'Unit digunakan',
            'mutation_note' => 'Penempatan unit baru',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'adjustment',
            'from_droppoint_id' => 1,
            'to_droppoint_id' => 2,
            'from_status' => 'Ready',
            'to_status' => 'Deployed',
            'notes' => 'Penempatan unit baru',
        ]);
    }

    public function test_ready_status_cannot_be_set_outside_warehouse(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-005');

        $response = $this->put(route('asset_item.update', $item), [
            'asset_id' => $item->asset_id,
            'sku' => $item->sku,
            'droppoint_id' => 2,
            'status' => 'Ready',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(1, (int) $item->fresh()->droppoint_id);
        $this->assertDatabaseCount('stock_mutations', 0);
    }

    public function test_stock_card_displays_complete_location_and_status_history(): void
    {
        $this->actingAs($this->staff());
        $item = $this->unit('SKU-006', 2, 'Deployed');
        StockMutation::create([
            'asset_item_id' => $item->id,
            'type' => 'issue',
            'from_droppoint_id' => 1,
            'to_droppoint_id' => 2,
            'from_status' => 'Ready',
            'to_status' => 'Deployed',
            'source_type' => 'barang_keluar',
            'source_reference' => 'OUT-006',
            'user_id' => auth()->id(),
            'occurred_at' => now(),
        ]);

        $response = $this->get(route('asset.kartu_stok', $item->asset_id));

        $response->assertOk()
            ->assertSee('Riwayat Mutasi Unit')
            ->assertSee('OUT-006')
            ->assertSee('WH')
            ->assertSee('DP-02')
            ->assertSee('Deployed');
    }

    public function test_stock_mutation_migration_backfills_existing_inbound_and_outbound_documents(): void
    {
        $item = $this->unit('SKU-007', 2, 'Deployed');
        $receiptId = DB::table('barang_masuks')->insertGetId([
            'tanggal_masuk' => '2026-10-10',
            'no_barang_masuk' => 'BM-007',
            'no_invoice' => 'INV-007',
            'supplier_id' => 1,
        ]);
        DB::table('barang_masuk_details')->insert([
            'barang_masuk_id' => $receiptId,
            'asset_item_id' => $item->id,
        ]);
        $issueId = DB::table('barang_keluars')->insertGetId([
            'kode_transaksi' => 'OUT-007',
            'tanggal_keluar' => '2026-10-11',
            'droppoint_id' => 2,
        ]);
        DB::table('barang_keluar_details')->insert([
            'barang_keluar_id' => $issueId,
            'asset_item_id' => $item->id,
        ]);

        Schema::dropIfExists('stock_mutations');
        $migration = require base_path('database/migrations/2026_10_30_000002_create_stock_mutations_table.php');
        $migration->up();

        $this->assertDatabaseCount('stock_mutations', 2);
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'receipt',
            'source_reference' => 'BM-007',
        ]);
        $this->assertDatabaseHas('stock_mutations', [
            'asset_item_id' => $item->id,
            'type' => 'issue',
            'source_reference' => 'OUT-007',
            'to_droppoint_id' => 2,
        ]);
    }

    public function test_return_and_transfer_migration_creates_tables_and_navigation_entries(): void
    {
        foreach ([
            'droppoint_transfer_details',
            'droppoint_transfers',
            'asset_return_details',
            'asset_returns',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        $migration = require base_path('database/migrations/2026_10_30_000003_create_asset_return_and_droppoint_transfer_tables.php');
        $migration->up();

        $this->assertTrue(Schema::hasTable('asset_returns'));
        $this->assertTrue(Schema::hasTable('asset_return_details'));
        $this->assertTrue(Schema::hasTable('droppoint_transfers'));
        $this->assertTrue(Schema::hasTable('droppoint_transfer_details'));
        $this->assertTrue(Schema::hasIndex('droppoint_transfers', 'dp_transfers_source_dest_idx'));
        $this->assertDatabaseHas('menus', ['route' => 'asset_returns.index']);
        $this->assertDatabaseHas('menus', ['route' => 'droppoint_transfers.index']);
        $this->assertDatabaseHas('menu_groups', ['name' => 'Transaksi']);

        Schema::table('droppoint_transfers', function (Blueprint $table): void {
            $table->dropIndex('dp_transfers_source_dest_idx');
        });
        $migration->up();

        $this->assertTrue(Schema::hasIndex('droppoint_transfers', 'dp_transfers_source_dest_idx'));
    }

    public function test_dashboard_shows_live_inventory_counts_and_recent_mutations(): void
    {
        $this->actingAs($this->staff());
        $unit = $this->unit('DASH-001');

        StockMutation::create([
            'asset_item_id' => $unit->id,
            'type' => 'receipt',
            'to_droppoint_id' => 1,
            'to_status' => 'Ready',
            'source_type' => 'barang_masuk',
            'source_reference' => 'BM-DASH-001',
            'occurred_at' => now(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Selamat datang, Inventory Staff')
            ->assertSee('Model katalog')
            ->assertSee('Total unit fisik')
            ->assertSee('DASH-001')
            ->assertSee('BM-DASH-001')
            ->assertDontSee('Admin Ica')
            ->assertDontSee('148');
    }

    public function test_supplier_can_be_updated_and_unused_supplier_deleted(): void
    {
        $this->actingAs($this->staff());
        $supplier = Supplier::create(['nama_supplier' => 'Supplier Lama']);

        $this->put(route('supplier.update', $supplier), [
            'nama_supplier' => 'Supplier Baru',
            'no_telepon' => '021123456',
            'email' => 'vendor@example.test',
            'alamat_supplier' => 'Jakarta',
        ])->assertRedirect();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'nama_supplier' => 'Supplier Baru',
            'email' => 'vendor@example.test',
        ]);

        $this->delete(route('supplier.destroy', $supplier))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_used_by_inbound_transaction_cannot_be_deleted(): void
    {
        $this->actingAs($this->staff());
        $supplier = Supplier::create(['nama_supplier' => 'Supplier Bertransaksi']);
        DB::table('barang_masuks')->insert([
            'tanggal_masuk' => now()->toDateString(),
            'no_barang_masuk' => 'BM-SUPPLIER-PROTECT',
            'no_invoice' => 'INV-SUPPLIER-PROTECT',
            'supplier_id' => $supplier->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('supplier.destroy', $supplier))
            ->assertRedirect()
            ->assertSessionHasErrors('supplier');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseHas('barang_masuks', ['supplier_id' => $supplier->id]);
    }

    public function test_asset_category_can_be_added_renamed_and_deleted_when_unused(): void
    {
        $this->actingAs($this->staff());

        $this->post(route('categories.store'), ['name' => 'Laptop'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $category = DB::table('asset_categories')->where('name', 'Laptop')->first();
        $this->assertNotNull($category);

        $this->put(route('categories.update', $category->id), ['name' => 'Notebook'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('asset_categories', ['id' => $category->id, 'name' => 'Notebook']);

        $this->delete(route('categories.destroy', $category->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('asset_categories', ['id' => $category->id]);
    }

    public function test_used_asset_category_rename_updates_catalog_and_category_cannot_be_deleted(): void
    {
        $this->actingAs($this->staff());
        $asset = $this->catalog();
        $category = DB::table('asset_categories')->where('name', 'PC/Komputer')->first();

        $this->put(route('categories.update', $category->id), ['name' => 'Komputer'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'kategori' => 'Komputer']);
        $this->delete(route('categories.destroy', $category->id))
            ->assertRedirect()
            ->assertSessionHasErrors('category');
        $this->assertDatabaseHas('asset_categories', ['id' => $category->id, 'name' => 'Komputer']);
    }

    public function test_asset_category_migration_preserves_existing_catalog_categories_and_adds_navigation(): void
    {
        Asset::create([
            'kategori' => 'Legacy Category',
            'nama' => 'Legacy Catalog',
        ]);
        Schema::dropIfExists('asset_categories');

        $migration = require base_path('database/migrations/2026_10_30_000004_create_asset_categories_table.php');
        $migration->up();

        $this->assertDatabaseHas('asset_categories', ['name' => 'Legacy Category']);
        $this->assertDatabaseHas('asset_categories', ['name' => 'PC/Komputer']);
        $this->assertDatabaseHas('asset_categories', ['name' => 'CCTV']);
        $this->assertDatabaseHas('menus', ['route' => 'categories.index']);
    }

    public function test_inventory_report_filters_units_and_exports_csv(): void
    {
        $this->actingAs($this->staff());
        $readyUnit = $this->unit('REPORT-READY', 1, 'Ready');
        $deployedUnit = $this->unit('REPORT-DEPLOYED', 2, 'Deployed');

        $this->get(route('inventory_report.index', ['status' => 'Ready']))
            ->assertOk()
            ->assertSee('REPORT-READY')
            ->assertDontSee('REPORT-DEPLOYED');

        $response = $this->get(route('inventory_report.export', ['status' => 'Ready']))
            ->assertDownload();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('REPORT-READY', $csv);
        $this->assertStringNotContainsString('REPORT-DEPLOYED', $csv);
    }

    public function test_inventory_report_migration_adds_menu_to_reports_group(): void
    {
        $migration = require base_path('database/migrations/2026_10_30_000005_add_inventory_report_navigation.php');
        $migration->up();

        $this->assertDatabaseHas('menus', ['route' => 'inventory_report.index']);
        $this->assertDatabaseHas('menu_groups', ['name' => 'Laporan']);
    }

    private function staff(): User
    {
        return User::create([
            'name' => 'Inventory Staff',
            'username' => 'inventory-staff',
            'email' => 'inventory-staff@example.test',
            'password' => 'test-password',
            'role' => 'Staff',
        ]);
    }

    private function catalog(): Asset
    {
        return Asset::create([
            'kategori' => 'PC/Komputer',
            'nama' => 'PC Kantor',
        ]);
    }

    private function unit(string $sku, int $droppointId = 1, string $status = 'Ready'): AssetItem
    {
        $asset = $this->catalog();

        return AssetItem::create([
            'asset_id' => $asset->id,
            'sku' => $sku,
            'serial_number' => 'SN-'.$sku,
            'droppoint_id' => $droppointId,
            'status' => $status,
        ]);
    }
}
