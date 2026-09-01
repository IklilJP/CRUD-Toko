<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class TransaksiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('pembeli', only: ['checkout', 'store']),
            new Middleware('admin', only: ['ubahStatus']),
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $statusFilter = in_array($request->query('status'), Transaksi::SEMUA_STATUS, true)
            ? $request->query('status')
            : '';

        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $transaksi = Transaksi::query()
            ->with('user')
            ->withCount('details')

            ->when(! $user->isAdmin(), fn($query) => $query->where('user_id', $user->id))

            ->when($statusFilter !== '', fn($query) => $query->where('status', $statusFilter))
            ->when($q !== '', function ($query) use ($q, $user) {
                $query->where(function ($sub) use ($q, $user) {
                    $sub->where('kode', 'ilike', "%{$q}%");
                    if ($user->isAdmin()) {
                        $sub->orWhereHas('user', fn($u) => $u
                            ->where('name', 'ilike', "%{$q}%")
                            ->orWhere('email', 'ilike', "%{$q}%"));
                    }
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('transaksi.index', compact('transaksi', 'statusFilter', 'q'));
    }

    public function checkout(Request $request)
    {
        $items = Keranjang::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('keranjang.index')
                ->with('gagal', 'Keranjangmu masih kosong, gak ada yang bisa di-checkout.');
        }

        $total = $items->sum(fn($item) => $item->qty * $item->product->price);

        return view('transaksi.checkout', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_penerima' => ['required', 'string', 'min:2', 'max:100'],

            'telepon' => ['required', 'string', 'max:20', 'regex:/^[0-9+\- ]+$/'],

            'alamat'  => ['required', 'string', 'min:10', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'nama_penerima.min'      => 'Nama penerima minimal 2 karakter.',

            'telepon.required' => 'Nomor telepon wajib diisi.',
            'telepon.regex'    => 'Telepon cuma boleh angka, spasi, tanda + dan -.',

            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.min'      => 'Alamat kependekan - tulis yang lengkap biar barangnya nyampe.',
            'alamat.max'      => 'Alamat maksimal 500 karakter.',

            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        $userId = $request->user()->id;

        $items = Keranjang::where('user_id', $userId)->with('product')->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('keranjang.index')
                ->with('gagal', 'Keranjangmu kosong, checkout dibatalkan.');
        }

        try {

            $transaksi = DB::transaction(function () use ($request, $data, $items, $userId) {

                $kode = 'TRX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

                $transaksi = Transaksi::create([
                    'user_id'       => $userId,
                    'kode'          => $kode,

                    'total'         => 0,

                    'status'        => 'menunggu',
                    'nama_penerima' => $data['nama_penerima'],
                    'telepon'       => $data['telepon'],
                    'alamat'        => $data['alamat'],
                    'catatan'       => $data['catatan'] ?? null,
                ]);

                $total = 0;

                foreach ($items as $item) {

                    $produk = Product::where('id', $item->product_id)->lockForUpdate()->first();

                    if (! $produk || ! $produk->is_active) {
                        throw new RuntimeException(
                            "Produk \"{$item->product->name}\" sudah gak dijual lagi. "
                                . "Keluarkan dulu dari keranjang."
                        );
                    }

                    if ($produk->stock < $item->qty) {
                        throw new RuntimeException(
                            "Stok \"{$produk->name}\" tinggal {$produk->stock}, "
                                . "sedangkan di keranjangmu {$item->qty}. Kurangi dulu jumlahnya."
                        );
                    }

                    $subtotal = $produk->price * $item->qty;

                    $transaksi->details()->create([
                        'product_id'  => $produk->id,
                        'nama_produk' => $produk->name,
                        'harga'       => $produk->price,
                        'qty'         => $item->qty,
                        'subtotal'    => $subtotal,
                    ]);

                    $produk->decrement('stock', $item->qty);

                    $total += $subtotal;
                }

                $transaksi->update(['total' => $total]);

                Keranjang::where('user_id', $userId)->delete();

                return $transaksi;
            });
        } catch (RuntimeException $e) {

            return back()->withInput()->with('gagal', $e->getMessage());
        }

        return redirect()
            ->route('transaksi.show', $transaksi)
            ->with('sukses', "Pesanan {$transaksi->kode} berhasil dibuat.");
    }

    public function show(Request $request, Transaksi $transaksi)
    {
        $user = $request->user();

        abort_if(! $user->isAdmin() && $transaksi->user_id !== $user->id, 404);

        $transaksi->load(['details.product.primaryImage', 'user']);

        return view('transaksi.show', compact('transaksi'));
    }

    public function ubahStatus(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([

            'status' => ['required', Rule::in(Transaksi::STATUS_ADMIN)],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status yang dipilih gak dikenal.',
        ]);

        if ($transaksi->status === 'batal') {
            return back()->with('gagal', 'Transaksi yang sudah dibatalkan gak bisa diubah statusnya lagi.');
        }

        $transaksi->update(['status' => $data['status']]);

        return back()->with('sukses', "Status {$transaksi->kode} jadi \"{$data['status']}\".");
    }

    public function batal(Request $request, Transaksi $transaksi)
    {
        $user = $request->user();

        abort_if(! $user->isAdmin() && $transaksi->user_id !== $user->id, 404);
        if (! $transaksi->bisaDibatalkan()) {
            return back()->with(
                'gagal',
                "Pesanan {$transaksi->kode} statusnya sudah \"{$transaksi->status}\", "
                    . "jadi gak bisa dibatalkan lagi."
            );
        }

        DB::transaction(function () use ($transaksi) {
            foreach ($transaksi->details as $detail) {
                Product::where('id', $detail->product_id)->increment('stock', $detail->qty);
            }

            $transaksi->update(['status' => 'batal']);
        });

        return back()->with('sukses', "Pesanan {$transaksi->kode} dibatalkan, stoknya dikembalikan.");
    }
}
