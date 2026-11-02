<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAssetItemsAddDroppointId extends Migration
{
    public function up(): void
    {
        Schema::table('asset_items', function (Blueprint $table) {
            // 1. Hapus kolom lokasi lama yang bertipe varchar
            //$table->dropColumn('lokasi');

            // 2. Tambah kolom droppoint_id sebagai Foreign Key, ditaruh setelah kode_aset
            // Disetel default 1 (Gudang IT) sesuai skenario kita sebelumnya
            $table->foreignId('droppoint_id')->default(1)->after('serial_number')->constrained('droppoint')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('asset_items', function (Blueprint $table) {
            // Kembalikan seperti semula jika di-rollback
            $table->dropForeign(['droppoint_id']);
            $table->dropColumn('droppoint_id');
            $table->string('lokasi')->nullable();
        });
    }
}