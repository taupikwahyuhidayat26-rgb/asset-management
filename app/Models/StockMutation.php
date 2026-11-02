<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    protected $fillable = [
        'asset_item_id',
        'type',
        'from_droppoint_id',
        'to_droppoint_id',
        'from_status',
        'to_status',
        'source_type',
        'source_id',
        'source_reference',
        'notes',
        'user_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }

    public function fromDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'from_droppoint_id');
    }

    public function toDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'to_droppoint_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
