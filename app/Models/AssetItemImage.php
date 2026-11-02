<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetItemImage extends Model
{
    protected $fillable = ['asset_item_id', 'path'];

    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }
}
