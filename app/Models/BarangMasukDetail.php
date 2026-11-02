<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasukDetail extends Model
{
    protected $table = 'barang_masuk_details';

    protected $fillable = [
        'barang_masuk_id', 
        'asset_item_id'
    ];

    // KUNCI PERBAIKAN: Hubungkan detail ke Nota Induk Barang Masuk
    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'barang_masuk_id');
    }

    // Hubungkan detail ke Unit Fisik Asset (Sudah ada kemarin)
    public function assetItem()
    {
        return $this->belongsTo(AssetItem::class, 'asset_item_id');
    }
}