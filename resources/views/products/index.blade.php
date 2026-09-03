@extends('layouts.app')

@section('judul', 'Produk')

@section('konten')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">
            Produk
            <span class="ml-1 text-sm font-normal text-gray-500">({{ $products->total() }} data)</span>
        </h1>

        @if (auth()->user()?->isAdmin())
            <div class="flex items-center gap-2">
                <a href="{{ route('products.arsip') }}" class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">
                    Arsip
                </a>
                <a href="{{ route('products.create') }}"
                    class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Tambah Produk
                </a>
            </div>
        @endif
    </div>

    <form action="{{ route('products.index') }}" method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama atau SKU..." maxlength="150"
            class="w-full max-w-xs rounded border px-3 py-2 text-sm">

        <select name="category_id" class="rounded border bg-white px-3 py-2 text-sm">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <select name="sort" class="rounded border bg-white px-3 py-2 text-sm">
        <option value="">Urutkan: Terbaru</option>
        <option value="termurah" @selected($sort === 'termurah')>Harga termurah</option>
        <option value="termahal" @selected($sort === 'termahal')>Harga termahal</option>
        </select>

        <button class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Cari</button>

        @if ($q !== '' || $categoryId || $sort)
            <a href="{{ route('products.index') }}"
                class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif

    </form>

    @if (auth()->user()?->isAdmin())
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Gambar</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if ($product->primaryImage)
                                    <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" loading="lazy"
                                        class="h-12 w-12 rounded border object-cover">
                                @else
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded border bg-gray-100 text-[10px] text-gray-400">
                                        Belum ada gambar
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <a href="{{ route('products.show', $product) }}"
                                    class="font-medium text-blue-700 hover:underline">
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

                            <td class="px-4 py-3 text-center">
                                <span class="{{ $product->stock == 0 ? 'font-semibold text-red-600' : '' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    <a href="{{ route('products.show', $product) }}"
                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50">
                                        Detail
                                    </a>

                                    <a href="{{ route('products.edit', $product) }}"
                                        class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                        Ubah
                                    </a>

                                    <form action="{{ route('products.nonaktifkan', $product) }}" method="POST"
                                        onsubmit="return confirm('Pindahkan produk ini ke Arsip? Datanya gak dihapus, cuma disingkirkan dari etalase.')">
                                        @csrf
                                        <button
                                            class="inline-flex items-center rounded-md border border-orange-200 bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-700 hover:bg-orange-100">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                @if ($q !== '' || $categoryId)
                                    Gak ada produk yang cocok dengan pencarianmu.
                                @else
                                    Belum ada produk aktif.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        @forelse ($products as $product)
        @empty
            <p class="rounded-lg bg-white p-10 text-center text-sm text-gray-500 shadow">
                @if ($q !== '' || $categoryId)
                    Gak ada produk yang cocok dengan pencarianmu.
                @else
                    Belum ada produk aktif.
                @endif
            </p>
        @endforelse

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
            @foreach ($products as $product)
                <a href="{{ route('products.show', $product) }}" class="rounded-lg bg-white p-3 shadow hover:shadow-md">
                    @if ($product->primaryImage)
                        <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" loading="lazy"
                            class="h-32 w-full rounded object-cover">
                    @else
                        <div class="flex h-32 w-full items-center justify-center rounded bg-gray-100 text-xs text-gray-400">
                            Belum ada gambar
                        </div>
                    @endif

                    <div class="mt-2 text-sm font-medium text-gray-800">{{ $product->name }}</div>
                    <div class="text-xs text-gray-500">{{ $product->category->name }}</div>
                    <div class="mt-1 text-sm font-semibold text-blue-700">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
