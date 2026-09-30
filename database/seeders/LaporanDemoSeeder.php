<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaporanDemoSeeder extends Seeder
{
    private const PEMBELI = ['Andi', 'Bela', 'Citra', 'Dimas', 'Eka', 'Fajar', 'Gita', 'Hadi'];

    private const PELANGGAN_KHUSUS = [
        'grosir'   => ['email' => 'grosir@toko.test',   'nama' => 'Toko Grosir Sinar'],
        'reseller' => ['email' => 'reseller@toko.test', 'nama' => 'Warung Bu Sri'],
    ];

    private const PRODUK = [
        'DEMO-01' => ['nama' => 'Kipas Angin Portable', 'kategori' => 'Elektronik', 'harga' => 175000, 'stok' => 5,
            'pola' => [3, 3, 3, 3, 3, 3, 3, 3, 6, 6, 7, 6]],
        'DEMO-02' => ['nama' => 'Headset Bluetooth', 'kategori' => 'Elektronik', 'harga' => 250000, 'stok' => 0, 'habis' => 10,
            'pola' => [3, 3, 4, 3, 3, 3, 4, 3, 3, 3, 3, 3]],
        'DEMO-03' => ['nama' => 'Powerbank 10000mAh', 'kategori' => 'Elektronik', 'harga' => 195000, 'stok' => 0,
            'pola' => []],
        'DEMO-04' => ['nama' => 'Sepatu Lari', 'kategori' => 'Pakaian', 'harga' => 420000, 'stok' => 200,
            'pola' => [1, 0, 1, 1, 0, 1, 1, 0, 1, 1, 0, 1]],
        'DEMO-05' => ['nama' => 'Jaket Parasut', 'kategori' => 'Pakaian', 'harga' => 310000, 'stok' => 75,
            'pola' => []],
        'DEMO-06' => ['nama' => 'Tumbler Stainless', 'kategori' => 'Makanan & Minuman', 'harga' => 85000, 'stok' => 12,
            'pola' => [4, 4, 5, 4, 3, 4, 5, 4, 4, 4, 5, 4]],
        'DEMO-07' => ['nama' => 'Topi Bucket', 'kategori' => 'Pakaian', 'harga' => 55000, 'stok' => 25, 'arsip' => true,
            'pola' => [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1]],
        'DEMO-08' => ['nama' => 'Kaos Polos Putih', 'kategori' => 'Pakaian', 'harga' => 45000, 'stok' => 30,
            'pola' => [0, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 1]],
        'DEMO-09' => ['nama' => 'Tas Selempang', 'kategori' => 'Pakaian', 'harga' => 150000, 'stok' => 0, 'habis' => 20, 'pembeli' => 2,
            'pola' => [0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 1]],
        'DEMO-10' => ['nama' => 'Casing HP Magnetik', 'kategori' => 'Elektronik', 'harga' => 60000, 'stok' => 12, 'dibuat' => 10, 'pembeli' => 1,
            'pola' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2]],
        'DEMO-11' => ['nama' => 'Kopi Susu Botol', 'kategori' => 'Makanan & Minuman', 'harga' => 18000, 'stok' => 6,
            'pola' => [9, 10, 8, 10, 9, 11, 10, 9, 10, 8, 10, 9]],
        'DEMO-12' => ['nama' => 'Madu Hutan 500ml', 'kategori' => 'Makanan & Minuman', 'harga' => 95000, 'stok' => 1, 'pembeli' => 2,
            'pola' => [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2]],
        'DEMO-13' => ['nama' => 'Gula Aren 1kg', 'kategori' => 'Makanan & Minuman', 'harga' => 28000, 'stok' => 15,
            'pola' => [1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0]],
        'DEMO-14' => ['nama' => 'Botol Minum Anak', 'kategori' => 'Makanan & Minuman', 'harga' => 40000, 'stok' => 0, 'habis' => 6, 'diubah' => 2,
            'pola' => [3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3]],
        'DEMO-15' => ['nama' => 'Lampu Tidur LED', 'kategori' => 'Elektronik', 'harga' => 65000, 'stok' => 10, 'diubah' => 3,
            'pola' => []],
        'DEMO-16' => ['nama' => 'Kacamata Hitam', 'kategori' => 'Pakaian', 'harga' => 80000, 'stok' => 2, 'pembeli' => 2,
            'pola' => [0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1]],
        'DEMO-17' => ['nama' => 'Earphone TWS', 'kategori' => 'Elektronik', 'harga' => 150000, 'stok' => 60, 'dibuat' => 12,
            'pola' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 8]],
        'DEMO-18' => ['nama' => 'Kaos Oversize', 'kategori' => 'Pakaian', 'harga' => 90000, 'stok' => 3, 'dibuat' => 9,
            'pola' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10]],
        'DEMO-19' => ['nama' => 'Sandal Gunung', 'kategori' => 'Pakaian', 'harga' => 135000, 'stok' => 20, 'dibuat' => 40,
            'pola' => []],
        'DEMO-20' => ['nama' => 'Speaker Mini', 'kategori' => 'Elektronik', 'harga' => 120000, 'stok' => 0, 'habis' => 100, 'dibuat' => 250,
            'pola' => [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2]],
        'DEMO-21' => ['nama' => 'Kurma Ajwa 500g', 'kategori' => 'Makanan & Minuman', 'harga' => 75000, 'stok' => 150,
            'pola' => [10, 10, 9, 10, 11, 10, 9, 10, 2, 1, 2, 1]],
        'DEMO-22' => ['nama' => 'Teh Celup Isi 25', 'kategori' => 'Makanan & Minuman', 'harga' => 12000, 'stok' => 6,
            'pola' => [4, 4, 5, 4, 3, 4, 5, 4, 4, 4, 5, 4]],
        'DEMO-23' => ['nama' => 'Kaos Kaki Sport', 'kategori' => 'Pakaian', 'harga' => 25000, 'stok' => 50,
            'pola' => [4, 4, 5, 4, 3, 4, 5, 4, 4, 4, 5, 4]],
        'DEMO-24' => ['nama' => 'Sirup Markisa', 'kategori' => 'Makanan & Minuman', 'harga' => 35000, 'stok' => 40,
            'pola' => [10, 10, 9, 10, 11, 10, 9, 10, 2, 1, 2, 1]],
        'DEMO-25' => ['nama' => 'Masker Kain', 'kategori' => 'Pakaian', 'harga' => 15000, 'stok' => 30,
            'pola' => [3, 3, 3, 3, 3, 3, 3, 3, 6, 6, 7, 6]],
        'DEMO-26' => ['nama' => 'Kerupuk Udang 250g', 'kategori' => 'Makanan & Minuman', 'harga' => 22000, 'stok' => 15,
            'pola' => [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2]],
    ];

    private const PESANAN_KHUSUS = [
        ['pembeli' => 'grosir',   'hari' => 46, 'status' => 'selesai',  'item' => ['DEMO-08' => 40]],
        ['pembeli' => 'grosir',   'hari' => 29, 'status' => 'selesai',  'item' => ['DEMO-26' => 30]],
        ['pembeli' => 'reseller', 'hari' => 8,  'status' => 'selesai',  'item' => ['DEMO-13' => 20]],
        ['pembeli' => 'reseller', 'hari' => 36, 'status' => 'selesai',  'item' => ['DEMO-13' => 20]],
        ['pembeli' => 'reseller', 'hari' => 64, 'status' => 'selesai',  'item' => ['DEMO-13' => 20]],
        ['pembeli' => 'Andi',     'hari' => 5,  'status' => 'menunggu', 'item' => ['DEMO-01' => 20]],
        ['pembeli' => 'Andi',     'hari' => 9,  'status' => 'batal',    'item' => ['DEMO-04' => 50, 'DEMO-05' => 40]],
    ];

    public function run(): void
    {
        Transaksi::where('kode', 'like', 'DEMO-%')->delete();

        $akun = [];

        foreach (self::PEMBELI as $i => $nama) {
            $akun[$nama] = User::updateOrCreate(
                ['email' => 'demo' . ($i + 1) . '@toko.test'],
                ['name' => $nama, 'password' => 'password', 'role' => 'user']
            );
        }

        foreach (self::PELANGGAN_KHUSUS as $kunci => $data) {
            $akun[$kunci] = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['nama'], 'password' => 'password', 'role' => 'user']
            );
        }

        $produk = [];

        foreach (self::PRODUK as $sku => $data) {
            $kategori = Category::firstOrCreate(['name' => $data['kategori']]);

            $produk[$sku] = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $kategori->id,
                    'name'        => $data['nama'],
                    'price'       => $data['harga'],
                    'stock'       => $data['stok'],
                    'is_active'   => ! isset($data['arsip']),
                    'description' => 'Data contoh buat nyoba halaman laporan.',
                ]
            );

            $dibuat = now()->subDays($data['dibuat'] ?? 150);

            $produk[$sku]->created_at = $dibuat;
            $produk[$sku]->updated_at = isset($data['diubah']) ? now()->subDays($data['diubah']) : $dibuat;
            $produk[$sku]->save();
        }

        DB::transaction(function () use ($akun, $produk) {
            $nomor   = 0;
            $pembeli = array_map(fn ($nama) => $akun[$nama], self::PEMBELI);

            foreach (self::PRODUK as $sku => $data) {
                $jumlahPembeli = $data['pembeli'] ?? count($pembeli);
                $urutan        = 0;

                foreach ($data['pola'] as $minggu => $qtyMinggu) {
                    $hariLalu = ($data['habis'] ?? 1) + (11 - $minggu) * 7;
                    $pecahan  = array_merge(array_fill(0, intdiv($qtyMinggu, 2), 2), $qtyMinggu % 2 ? [1] : []);

                    foreach ($pecahan as $geser => $qty) {
                        $status = $hariLalu + $geser <= 7 ? 'dibayar' : 'selesai';

                        $this->buatTransaksi(++$nomor, $pembeli[$urutan++ % $jumlahPembeli], $hariLalu + $geser, $status, [$sku => $qty], $produk);
                    }
                }
            }

            foreach (self::PESANAN_KHUSUS as $data) {
                $this->buatTransaksi(++$nomor, $akun[$data['pembeli']], $data['hari'], $data['status'], $data['item'], $produk);
            }
        });
    }

    private function buatTransaksi(int $nomor, User $user, int $hariLalu, string $status, array $item, array $produk): void
    {
        $waktu = now('Asia/Jakarta')
            ->subDays($hariLalu)
            ->setTime(10 + ($nomor % 8), ($nomor * 7) % 60)
            ->utc();

        $transaksi = Transaksi::create([
            'user_id'       => $user->id,
            'kode'          => 'DEMO-' . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT) . '-' . $waktu->format('ymd'),
            'total'         => 0,
            'status'        => $status,
            'nama_penerima' => $user->name,
            'telepon'       => '081200000000',
            'alamat'        => 'Jl. Contoh No. ' . $nomor . ', Kota Contoh',
            'catatan'       => 'Data contoh dari LaporanDemoSeeder.',
        ]);

        $total = 0;

        foreach ($item as $sku => $qty) {
            $subtotal = $produk[$sku]->price * $qty;

            $detail = $transaksi->details()->create([
                'product_id'  => $produk[$sku]->id,
                'nama_produk' => $produk[$sku]->name,
                'harga'       => $produk[$sku]->price,
                'qty'         => $qty,
                'subtotal'    => $subtotal,
            ]);

            $detail->created_at = $waktu;
            $detail->updated_at = $waktu;
            $detail->save();

            $total += $subtotal;
        }

        $transaksi->total      = $total;
        $transaksi->created_at = $waktu;
        $transaksi->updated_at = $waktu;
        $transaksi->save();
    }
}
