<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya buat Akun Admin Utama
        User::create([
            'name' => 'Admin Markasnack x Glytch',
            'email' => 'markasnack@gmail.com',
            'password' => Hash::make('MarkasnackXGlytch.co611'),
        ]);
    }
}