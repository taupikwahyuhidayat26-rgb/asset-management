<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_keluar_details', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel barang_keluar dan assets
            $table->foreignId('barang_keluar_id')->constrained('barang_keluars')->onDelete('cascade');
            // Ubah di tabel barang masuk & barang keluar agar mereferensikan ke unit fisik
            $table->foreignId('asset_item_id')->constrained('asset_items')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluar_details');
    }
};
