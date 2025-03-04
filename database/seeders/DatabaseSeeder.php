<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::table('users')->insert([
            'nama' => 'Admin Utama',
            'tanggal_lahir' => '1990-01-01',
            'nomor_hp' => '081234567890',
            'alamat' => 'Jl. Contoh Alamat No. 123, Kota Contoh',
            'role' => 'admin',
            'daerah_id' => null, // Sesuaikan dengan ID daerah yang ada atau biarkan null
            'username' => 'admin',
            'password' => Hash::make('passwordadmin123'),
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
