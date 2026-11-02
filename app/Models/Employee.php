<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'no_karyawan',
        'nama_karyawan',
        'posisi_1',
        'posisi_2',
        'status_karyawan',
        'tanggal_masuk',
        'masa_kerja',
        'no_hp',
        'email',
        'nik_ktp',
        'jenis_kelamin',
        'bca',
        'nomor_rekening',
        'nama_rekening',
        'status_kerja',
        'droppoint_id',
    ];

    protected function casts(): array
    {
        return ['tanggal_masuk' => 'date'];
    }

    public function droppoint(): BelongsTo
    {
        return $this->belongsTo(Droppoint::class);
    }

    public function system(): HasOne
    {
        return $this->hasOne(EmployeeSystem::class);
    }
}
