<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetReturn extends Model
{
    protected $fillable = [
        'return_code',
        'return_date',
        'source_droppoint_id',
        'destination_status',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return ['return_date' => 'date'];
    }

    public function sourceDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'source_droppoint_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(AssetReturnDetail::class);
    }
}
