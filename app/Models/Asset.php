<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['kategori', 'nama', 'merk', 'tipe', 'spesifikasi'];

    // Relasi: Satu Katalog memiliki banyak Unit Fisik (PC-001, PC-002, dll)
    public function items()
    {
        return $this->hasMany(AssetItem::class, 'asset_id');
    }
}