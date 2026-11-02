<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluarDetail extends Model
{
    use HasFactory;

    protected $table = 'barang_keluar_details';
    protected $fillable = ['barang_keluar_id', 'asset_item_id'];

    // Relasi balik ke Transaksi Induk
    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluar::class, 'barang_keluar_id');
    }

    // Relasi ke Fisik Barang
    public function assetItem()
    {
        return $this->belongsTo(AssetItem::class, 'asset_item_id');
    }
}