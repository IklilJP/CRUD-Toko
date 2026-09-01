<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class KeranjangController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['pembeli'];
    }

   public function index(Request $request)
    {
        $items = Keranjang::query()

            ->where('user_id', $request->user()->id)

            ->with(['product.primaryImage', 'product.category'])

            ->latest()
            ->get();

        $total = $items->sum(fn ($item) => $item->qty * $item->product->price);

        return view('keranjang.index', compact('items', 'total'));
    }

    public function tambah(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.integer'  => 'Jumlah harus bilangan bulat.',
            'qty.min'      => 'Jumlah minimal 1.',
            'qty.max'      => 'Maksimal 100 per sekali tambah.',
        ]);

        if (! $product->is_active) {
            return back()->with('gagal', "Produk \"{$product->name}\" lagi gak dijual.");
        }

        if ($product->stock < 1) {
            return back()->with('gagal', "Stok \"{$product->name}\" lagi habis.");
        }

        $item = Keranjang::firstOrNew([
            'user_id'    => $request->user()->id,
            'product_id' => $product->id,
        ]);

        $qtyBaru = ($item->qty ?? 0) + $data['qty'];

        if ($qtyBaru > $product->stock) {
            return back()->with(
                'gagal',
                "Stok \"{$product->name}\" cuma {$product->stock}, "
                . "sedangkan di keranjangmu sudah ada " . ($item->qty ?? 0) . "."
            );
        }

        $item->qty = $qtyBaru;
        $item->save();

        // return back()->with('sukses', "\"{$product->name}\" masuk keranjang ({$qtyBaru}).");
        return redirect()
            ->route('products.index')
            ->with('sukses', "\"{$product->name}\" masuk keranjang ({$qtyBaru}).");
    }

    public function ubah(Request $request, Keranjang $keranjang)
    {
        abort_if($keranjang->user_id !== $request->user()->id, 404);

        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.integer'  => 'Jumlah harus bilangan bulat.',
            'qty.min'      => 'Jumlah minimal 1. Kalau mau dihapus, pakai tombol Hapus.',
            'qty.max'      => 'Maksimal 100 per produk.',
        ]);

        $produk = $keranjang->product;

        if ($data['qty'] > $produk->stock) {
            return back()->with(
                'gagal',
                "Stok \"{$produk->name}\" cuma {$produk->stock}, gak bisa diisi {$data['qty']}."
            );
        }

        $keranjang->update(['qty' => $data['qty']]);

        return back()->with('sukses', "Jumlah \"{$produk->name}\" jadi {$data['qty']}.");
    }

    public function hapus(Request $request, Keranjang $keranjang)
    {
        abort_if($keranjang->user_id !== $request->user()->id, 404);

        $nama = $keranjang->product->name;

        $keranjang->delete();

        return back()->with('sukses', "\"{$nama}\" dikeluarkan dari keranjang.");
    }

    public function kosongkan(Request $request)
    {
        $jumlah = Keranjang::where('user_id', $request->user()->id)->delete();

        if ($jumlah === 0) {
            return back()->with('gagal', 'Keranjangnya memang sudah kosong.');
        }

        return back()->with('sukses', "Keranjang dikosongkan ({$jumlah} produk).");
    }
}
