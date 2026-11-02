<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetItem extends Model
{
    protected $fillable = ['asset_id', 'sku', 'serial_number', 'droppoint_id', 'status', 'keterangan'];

    // Relasi: Unit fisik ini merujuk ke Katalog yang mana
    public function catalog()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // KUNCI RELASI: Hubungkan AssetItem ke Droppoint
    public function droppoint()
    {
        return $this->belongsTo(Droppoint::class, 'droppoint_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AssetItemImage::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }
}
