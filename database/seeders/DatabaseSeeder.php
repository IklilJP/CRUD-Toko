<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@toko.test'],
            [
                'name'     => 'Admin Toko',
                'password' => 'password',
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@toko.test'],
            [
                'name'     => 'Budi Pembeli',
                'password' => 'password',
                'role'     => 'user',
            ]
        );

        foreach (['Elektronik', 'Pakaian', 'Makanan & Minuman'] as $nama) {
            Category::firstOrCreate(['name' => $nama]);
        }

        $elektronik = Category::where('name', 'Elektronik')->first();
        $pakaian    = Category::where('name', 'Pakaian')->first();

        $contoh = [
            [
                'category_id' => $elektronik->id,
                'name'        => 'Kipas Angin Meja',
                'sku'         => 'ELK-001',
                'price'       => 185000,
                'stock'       => 12,
                'is_active'   => true,
            ],
            [
                'category_id' => $elektronik->id,
                'name'        => 'Lampu Belajar LED',
                'sku'         => 'ELK-002',
                'price'       => 95000,
                'stock'       => 30,
                'is_active'   => true,
            ],
            [
                'category_id' => $pakaian->id,
                'name'        => 'Kemeja Flanel',
                'sku'         => 'PKN-001',
                'price'       => 120000,
                'stock'       => 8,
                'is_active'   => true,
            ],
            [
                'category_id' => $pakaian->id,
                'name'        => 'Kaos Polos Hitam',
                'sku'         => 'PKN-002',
                'price'       => 65000,
                'stock'       => 0,
                'is_active'   => false,
            ],
        ];

        foreach ($contoh as $data) {
            Product::firstOrCreate(['sku' => $data['sku']], $data);
        }
    }
}
