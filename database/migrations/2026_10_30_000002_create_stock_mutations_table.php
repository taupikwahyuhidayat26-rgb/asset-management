<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_item_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->foreignId('from_droppoint_id')->nullable()->constrained('droppoint')->nullOnDelete();
            $table->foreignId('to_droppoint_id')->nullable()->constrained('droppoint')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('occurred_at')->index();
            $table->timestamps();

            $table->index(['asset_item_id', 'occurred_at']);
        });

        $this->backfillBarangMasuk();
        $this->backfillBarangKeluar();
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }

    private function backfillBarangMasuk(): void
    {
        if (! Schema::hasTable('barang_masuk_details')
            || ! Schema::hasTable('barang_masuks')
            || ! Schema::hasColumns('barang_masuks', ['id', 'no_barang_masuk', 'tanggal_masuk'])
            || ! Schema::hasColumns('barang_masuk_details', ['id', 'barang_masuk_id', 'asset_item_id'])
            || ! Schema::hasTable('asset_items')) {
            return;
        }

        $warehouseId = config('inventory.warehouse_droppoint_id', 1);
        if (! Schema::hasTable('droppoint') || ! DB::table('droppoint')->where('id', $warehouseId)->exists()) {
            $warehouseId = null;
        }
        $details = DB::table('barang_masuk_details')
            ->join('barang_masuks', 'barang_masuk_details.barang_masuk_id', '=', 'barang_masuks.id')
            ->select([
                'barang_masuk_details.asset_item_id',
                'barang_masuks.id as source_id',
                'barang_masuks.no_barang_masuk as source_reference',
                'barang_masuks.tanggal_masuk as occurred_at',
            ])
            ->orderBy('barang_masuks.tanggal_masuk')
            ->orderBy('barang_masuk_details.id')
            ->get();

        foreach ($details as $detail) {
            if (! DB::table('asset_items')->where('id', $detail->asset_item_id)->exists()) {
                continue;
            }

            $this->insertLegacyMutation([
                'asset_item_id' => $detail->asset_item_id,
                'type' => 'receipt',
                'to_droppoint_id' => $warehouseId,
                'to_status' => 'Ready',
                'source_type' => 'barang_masuk',
                'source_id' => $detail->source_id,
                'source_reference' => $detail->source_reference,
                'notes' => 'Riwayat penerimaan sebelum pencatatan mutasi otomatis.',
                'occurred_at' => $detail->occurred_at,
            ]);
        }
    }

    private function backfillBarangKeluar(): void
    {
        if (! Schema::hasTable('barang_keluars')
            || ! Schema::hasTable('barang_keluar_details')
            || ! Schema::hasColumns('barang_keluars', ['id', 'kode_transaksi', 'droppoint_id', 'tanggal_keluar'])
            || ! Schema::hasColumns('barang_keluar_details', ['id', 'barang_keluar_id', 'asset_item_id'])
            || ! Schema::hasTable('asset_items')) {
            return;
        }

        $warehouseId = config('inventory.warehouse_droppoint_id', 1);
        if (! Schema::hasTable('droppoint') || ! DB::table('droppoint')->where('id', $warehouseId)->exists()) {
            $warehouseId = null;
        }
        $details = DB::table('barang_keluar_details')
            ->join('barang_keluars', 'barang_keluar_details.barang_keluar_id', '=', 'barang_keluars.id')
            ->select([
                'barang_keluar_details.asset_item_id',
                'barang_keluars.id as source_id',
                'barang_keluars.kode_transaksi as source_reference',
                'barang_keluars.droppoint_id',
                'barang_keluars.tanggal_keluar as occurred_at',
            ])
            ->orderBy('barang_keluars.tanggal_keluar')
            ->orderBy('barang_keluar_details.id')
            ->get();

        foreach ($details as $detail) {
            if (! DB::table('asset_items')->where('id', $detail->asset_item_id)->exists()) {
                continue;
            }

            $this->insertLegacyMutation([
                'asset_item_id' => $detail->asset_item_id,
                'type' => 'issue',
                'from_droppoint_id' => $warehouseId,
                'to_droppoint_id' => $detail->droppoint_id,
                'from_status' => 'Ready',
                'to_status' => 'Deployed',
                'source_type' => 'barang_keluar',
                'source_id' => $detail->source_id,
                'source_reference' => $detail->source_reference,
                'notes' => 'Riwayat pengeluaran sebelum pencatatan mutasi otomatis.',
                'occurred_at' => $detail->occurred_at,
            ]);
        }
    }

    private function insertLegacyMutation(array $mutation): void
    {
        $mutation['occurred_at'] = Carbon::parse($mutation['occurred_at'])->startOfDay();
        $mutation['created_at'] = now();
        $mutation['updated_at'] = now();

        DB::table('stock_mutations')->insert($mutation);
    }
};
