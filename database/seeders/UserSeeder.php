<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Ica',
            'email' => 'admin@veloassets.com',
            'password' => Hash::make('password123'), // Password di-enkripsi demi keamanan
        ]);
    }
}