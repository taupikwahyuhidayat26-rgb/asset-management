<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeResignation extends Model
{
    protected $fillable = ['employee_id', 'tanggal_resign', 'alasan', 'status_pemrosesan'];

    protected function casts(): array
    {
        return ['tanggal_resign' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
