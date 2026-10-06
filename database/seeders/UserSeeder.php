<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::create([
            'name' => 'Bapak HR Admin',
            'email' => 'admin@perusahaan.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Akun Karyawan Kantor
        User::create([
            'name' => 'Siti (Kantor)',
            'email' => 'siti@perusahaan.com',
            'password' => Hash::make('password123'),
            'role' => 'karyawan_kantor',
        ]);

        // Akun Supir
        User::create([
            'name' => 'Budi (Supir)',
            'email' => 'budi@perusahaan.com',
            'password' => Hash::make('password123'),
            'role' => 'supir',
        ]);

        // Akun Karyawan Gudang (Hanya butuh PIN)
        User::create([
            'name' => 'Agus (Gudang)',
            'email' => 'agus@perusahaan.com',
            'password' => Hash::make('password123'),
            'role' => 'karyawan_gudang',
            'pin_gudang' => '123456', // PIN ini untuk dicoba di halaman Kiosk
        ]);
    }
}