<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Mendaftarkan seeder agar otomatis berjalan saat perintah --seed diketik
        // $this->call([
        //    UserSeeder::class,
        //    NavigationSeeder::class,
        //]);
        $this->call([
            // Panggil seeder droppoint di sini
            DroppointSeeder::class,
        ]);
    }
}
