<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers'; // Memastikan Laravel menembak tabel suppliers

    // WAJIB RE-SESUAIKAN DENGAN NAMA KOLOM ASLI DATABASE
    protected $fillable = [
        'nama_supplier', 
        'no_telepon', 
        'email', 
        'alamat_supplier'
    ];
}