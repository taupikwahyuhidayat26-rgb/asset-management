<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DroppointTransferDetail extends Model
{
    protected $fillable = ['droppoint_transfer_id', 'asset_item_id'];

    public function droppointTransfer(): BelongsTo
    {
        return $this->belongsTo(DroppointTransfer::class);
    }

    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }
}
