<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemIdChange extends Model
{
    protected $fillable = ['employee_id', 'id_aktif', 'id_sebelumnya', 'tanggal_update', 'status_pemrosesan'];

    protected function casts(): array
    {
        return ['tanggal_update' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
