<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdCreationRequest extends Model
{
    protected $fillable = ['employee_id', 'status_pemrosesan'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
