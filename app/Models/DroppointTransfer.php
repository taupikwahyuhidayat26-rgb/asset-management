<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DroppointTransfer extends Model
{
    protected $fillable = [
        'transfer_code',
        'transfer_date',
        'source_droppoint_id',
        'destination_droppoint_id',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return ['transfer_date' => 'date'];
    }

    public function sourceDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'source_droppoint_id');
    }

    public function destinationDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'destination_droppoint_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DroppointTransferDetail::class);
    }
}
