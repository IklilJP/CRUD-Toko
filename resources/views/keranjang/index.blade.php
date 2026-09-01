@extends('layouts.app')

@section('judul', 'Keranjang')

@section('konten')
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-bold">
                Keranjang
                <span class="ml-1 text-sm font-normal text-gray-500">({{ $items->count() }} produk)</span>
            </h1>

            <a href="{{ route('products.index') }}" class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">
                &larr; Belanja lagi
            </a>
        </div>

        @if ($items->isEmpty())
            <div class="rounded-lg bg-white p-10 text-center shadow">
                <p class="text-sm text-gray-500">Keranjangmu masih kosong.</p>
                <a href="{{ route('products.index') }}"
                    class="mt-4 inline-block rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Lihat produk
                </a>
            </div>
        @else
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="w-full text-sm">
                    <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-center">Jumlah</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($item->product->primaryImage)
                                            <img src="{{ $item->product->primaryImage->url }}"
                                                alt="{{ $item->product->name }}" loading="lazy"
                                                class="h-12 w-12 rounded border object-cover">
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded border bg-gray-100 text-[10px] text-gray-400">
                                                no img
                                            </div>
                                        @endif

                                        <div>
                                            <a href="{{ route('products.show', $item->product) }}"
                                                class="font-medium text-blue-700 hover:underline">
                                                {{ $item->product->name }}
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                {{ $item->product->category->name }} &middot; stok {{ $item->product->stock }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3">

                                    <form action="{{ route('keranjang.ubah', $item) }}" method="POST"
                                        class="flex items-center justify-center gap-1">
                                        @csrf

                                        <input type="number" name="qty" value="{{ $item->qty }}"
                                            required min="1" max="{{ min(100, $item->product->stock) }}" step="1"
                                            class="w-20 rounded border px-2 py-1 text-center text-sm">

                                        <button class="rounded border px-2 py-1 text-xs hover:bg-gray-50">
                                            Simpan
                                        </button>
                                    </form>

                                    <p class="mt-1 text-center text-sm text-red-600">@error('qty'){{ $message }}@enderror</p>
                                </td>

                                <td class="px-4 py-3 text-right font-medium whitespace-nowrap">
                                    Rp {{ number_format($item->qty * $item->product->price, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <form action="{{ route('keranjang.hapus', $item) }}" method="POST"
                                        onsubmit="return confirm('Keluarkan produk ini dari keranjang?')">
                                        @csrf
                                        <button class="rounded-md border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="border-t bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-medium">Total</td>
                            <td class="px-4 py-3 text-right text-base font-bold text-blue-700 whitespace-nowrap">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <form action="{{ route('keranjang.kosongkan') }}" method="POST"
                    onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                    @csrf
                    <button class="rounded border border-red-200 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        Kosongkan keranjang
                    </button>
                </form>

                <a href="{{ route('transaksi.checkout') }}"
                    class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Lanjut ke checkout &rarr;
                </a>
            </div>
        @endif
    </div>
@endsection
