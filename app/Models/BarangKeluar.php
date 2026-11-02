<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table = 'barang_keluars';
    protected $fillable = ['kode_transaksi', 'tanggal_keluar', 'droppoint_id', 'keterangan'];

    // Relasi ke Drop Point Tujuan
    public function droppoint()
    {
        return $this->belongsTo(Droppoint::class, 'droppoint_id');
    }

    // Relasi ke Barang Keluar Detail
    public function details()
    {
        return $this->hasMany(BarangKeluarDetail::class, 'barang_keluar_id');
    }
}