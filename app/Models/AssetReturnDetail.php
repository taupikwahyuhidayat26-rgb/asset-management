<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturnDetail extends Model
{
    protected $fillable = ['asset_return_id', 'asset_item_id'];

    public function assetReturn(): BelongsTo
    {
        return $this->belongsTo(AssetReturn::class);
    }

    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }
}
