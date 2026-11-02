<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePositionMutation extends Model
{
    protected $fillable = [
        'employee_id',
        'posisi_1_lama',
        'posisi_2_lama',
        'status_karyawan_lama',
        'droppoint_lama_id',
        'posisi_baru',
        'status_karyawan_baru',
        'tanggal_mutasi',
        'keterangan',
        'status_pemrosesan',
    ];

    protected function casts(): array
    {
        return ['tanggal_mutasi' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldDroppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class, 'droppoint_lama_id');
    }
}
