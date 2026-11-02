<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDroppointMutation extends Model
{
    protected $fillable = [
        'employee_id',
        'droppoint_lama_id',
        'tanggal_mutasi_dp',
        'droppoint_baru_id',
        'status_pemrosesan',
    ];

    protected function casts(): array
    {
        return ['tanggal_mutasi_dp' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'droppoint_lama_id');
    }

    public function newDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'droppoint_baru_id');
    }
}
