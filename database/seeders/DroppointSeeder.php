<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Droppoint;
use Illuminate\Support\Facades\DB;

class DroppointSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kosongkan tabel droppoint terlebih dahulu agar tidak terjadi duplikasi saat di-seed ulang
        // Menggunakan DB::statement untuk menghindari masalah foreign key constraints jika nanti ada
        DB::table('droppoint')->truncate();

        // 2. Tentukan lokasi path file CSV yang sudah kita simpan tadi
        $csvFile = database_path('seeders/droppoint.csv');
        
        // 3. Buka file CSV untuk dibaca
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            
            // Lompat atau lewati baris pertama (karena baris pertama adalah nama kolom: id, kode_dp, dll)
            $header = fgetcsv($handle, 1000, ',');

            // 4. Lakukan looping untuk membaca baris demi baris isi datanya
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                
                // Pastikan baris data tidak kosong dan memiliki jumlah kolom yang sesuai
                if (isset($data[1]) && isset($data[2])) {
                    Droppoint::create([
                        'kode_dp'  => $data[1], // Mengambil isi kolom kode_dp
                        'nama_dp'  => $data[2], // Mengambil isi kolom nama_dp
                        'nama_cps' => $data[3] === '-' ? null : $data[3], // Jika strip '-', ubah jadi null di DB
                        'posisi'   => $data[4] ?? '-', // Mengambil isi kolom posisi
                    ]);
                }
            }
            
            // Close file handler setelah selesai membaca
            fclose($handle);
        }
    }
}