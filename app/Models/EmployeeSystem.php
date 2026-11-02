<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSystem extends Model
{
    protected $fillable = ['employee_id', 'id_sebelumnya', 'nama_id_sebelumnya'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
