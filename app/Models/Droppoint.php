<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Droppoint extends Model
{
    use HasFactory;

    // Kunci nama tabel agar tidak dibaca jamak (droppoints) oleh Laravel
    protected $table = 'droppoint';

    // Daftarkan kolom yang diizinkan untuk diinput
    protected $fillable = [
        'kode_dp',
        'nama_dp',
        'nama_cps',
        'posisi',
        'rm',
        'spv',
        'area_cover',
        'status_dp',
    ];
}