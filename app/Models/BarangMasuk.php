<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $fillable = ['tanggal_masuk', 'no_barang_masuk', 'no_po', 'no_invoice', 'supplier_id'];

    // Relasi: 1 transaksi barang masuk dimiliki oleh 1 supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Relasi: 1 transaksi barang masuk punya banyak detail barang
    public function details()
    {
        return $this->hasMany(BarangMasukDetail::class, 'barang_masuk_id');
    }
}