@extends('layouts.app')

@section('judul', 'Checkout')

@section('konten')
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-bold">Checkout</h1>
            <a href="{{ route('keranjang.index') }}" class="text-sm text-blue-600 hover:underline">
                &larr; Balik ke keranjang
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-5">

            <div class="md:col-span-3">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Data pengiriman</h2>

                    <form action="{{ route('transaksi.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="nama_penerima" class="block text-sm font-medium">
                                Nama penerima <span class="text-red-600">*</span>
                            </label>

                            <input type="text" id="nama_penerima" name="nama_penerima"
                                   value="{{ old('nama_penerima', auth()->user()->name) }}"
                                   required minlength="2" maxlength="100" autofocus
                                   class="mt-1 w-full rounded border px-3 py-2 @error('nama_penerima') border-red-500 @enderror">

                            <p class="mt-1 text-sm text-red-600">@error('nama_penerima'){{ $message }}@enderror</p>
                        </div>

                        <div class="mb-4">
                            <label for="telepon" class="block text-sm font-medium">
                                Nomor telepon <span class="text-red-600">*</span>
                            </label>

                            <input type="tel" id="telepon" name="telepon"
                                   value="{{ old('telepon') }}"
                                   required maxlength="20"
                                   pattern="[0-9+\- ]+"
                                   title="Cuma boleh angka, spasi, tanda + dan -."
                                   placeholder="08123456789"
                                   class="mt-1 w-full rounded border px-3 py-2 @error('telepon') border-red-500 @enderror">

                            <p class="mt-1 text-sm text-red-600">@error('telepon'){{ $message }}@enderror</p>
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="block text-sm font-medium">
                                Alamat lengkap <span class="text-red-600">*</span>
                            </label>

                            <textarea id="alamat" name="alamat" rows="3"
                                      required minlength="10" maxlength="500"
                                      placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"
                                      class="mt-1 w-full rounded border px-3 py-2 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>

                            <p class="mt-1 text-sm text-red-600">@error('alamat'){{ $message }}@enderror</p>
                        </div>

                        <div class="mb-4">
                            <label for="catatan" class="block text-sm font-medium">Catatan (opsional)</label>

                            <textarea id="catatan" name="catatan" rows="2" maxlength="500"
                                      placeholder="Titip di pos satpam, warna yang gelap, dll."
                                      class="mt-1 w-full rounded border px-3 py-2 @error('catatan') border-red-500 @enderror">{{ old('catatan') }}</textarea>

                            <p class="mt-1 text-sm text-red-600">@error('catatan'){{ $message }}@enderror</p>
                        </div>

                        <button type="submit"
                                class="w-full rounded bg-blue-600 px-4 py-3 font-medium text-white hover:bg-blue-700"
                                onclick="return confirm('Buat pesanan sekarang? Stok produknya langsung dikurangi.')">
                            Buat pesanan
                        </button>

                        <p class="mt-3 text-xs text-gray-500">
                            Setelah tombol ini diklik, keranjangmu dikosongkan dan stok produknya
                            langsung berkurang. Pesanan masih bisa dibatalkan selama statusnya
                            <strong>menunggu</strong>.
                        </p>
                    </form>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Ringkasan</h2>

                    <ul class="divide-y text-sm">
                        @foreach ($items as $item)
                            <li class="flex justify-between gap-3 py-2">
                                <span class="text-gray-700">
                                    {{ $item->product->name }}
                                    <span class="text-gray-400">&times;{{ $item->qty }}</span>
                                </span>

                                <span class="whitespace-nowrap font-medium">
                                    Rp {{ number_format($item->qty * $item->product->price, 0, ',', '.') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex justify-between border-t pt-4">
                        <span class="font-medium">Total</span>
                        <span class="text-lg font-bold text-blue-700 whitespace-nowrap">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        Harga yang dipakai adalah harga <strong>saat ini</strong>. Begitu pesanan
                        dibuat, harganya dikunci di nota dan gak ikut berubah walaupun admin
                        mengubah harga produknya besok.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
