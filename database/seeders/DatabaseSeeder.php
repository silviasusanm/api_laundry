<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['username' => 'owner'], [
            'nama' => 'Pemilik Bersih Laundry',
            'password' => Hash::make('owner123'),
            'role' => 'pemilik',
            'is_active' => true,
        ]);
        User::firstOrCreate(['username' => 'kasir'], [
            'nama' => 'Kasir Bersih Laundry',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        foreach ([
            ['Cuci Kering', 6000, 'kg'],
            ['Cuci Setrika', 8000, 'kg'],
            ['Setrika Saja', 5000, 'kg'],
            ['Cuci Sepatu', 20000, 'pasang'],
        ] as [$name, $price, $unit]) {
            Layanan::firstOrCreate(['nama_layanan' => $name], [
                'harga_per_unit' => $price,
                'satuan' => $unit,
                'is_active' => true,
            ]);
        }
    }
}
