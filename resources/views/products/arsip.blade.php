@extends('layouts.app')

@section('judul', 'Arsip Produk')

@section('konten')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold">
                Arsip Produk
                <span class="ml-1 text-sm font-normal text-gray-500">({{ $products->total() }} produk)</span>
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Produk di sini <strong>gak muncul di etalase</strong>. Datanya masih utuh - bisa diaktifkan lagi kapan saja.
            </p>
        </div>
        <a href="{{ route('products.index') }}" class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">
            &larr; Balik ke daftar produk
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Gambar</th>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3 text-right">Harga</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if ($product->primaryImage)
                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}"
                                     loading="lazy"
                                     class="h-12 w-12 rounded border object-cover opacity-60">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded border bg-gray-100 text-[10px] text-gray-400">
                                    no img
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <a href="{{ route('products.show', $product) }}" class="font-medium text-blue-700 hover:underline">
                                {{ $product->name }}
                            </a>
                            <div class="text-xs text-gray-500">
                                {{ $product->sku }} &middot; {{ $product->images_count }} gambar
                            </div>
                        </td>

                        <td class="px-4 py-3">{{ $product->category->name }}</td>

                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <form action="{{ route('products.aktifkan', $product) }}" method="POST">
                                    @csrf
                                    <button class="inline-flex items-center rounded-md border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 hover:bg-green-100">Aktifkan lagi</button>
                                </form>

                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('HAPUS PERMANEN produk ini beserta {{ $product->images_count }} file gambarnya? Tindakan ini TIDAK BISA dibatalkan.')">
                                    @csrf
                                    <button class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-100">Hapus permanen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                            Arsip kosong - semua produk lagi aktif.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
