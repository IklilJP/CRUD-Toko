@extends('layouts.app')

@section('judul', $product->name)

@section('konten')
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:underline">
                &larr; Balik ke daftar
            </a>

            @if (auth()->user()?->isAdmin())
                <a href="{{ route('products.edit', $product) }}"
                   class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Ubah produk
                </a>
            @endif
        </div>

        <div class="grid gap-6 rounded-lg bg-white p-6 shadow sm:grid-cols-2">
            <div>
                @if ($product->images->isNotEmpty())
                    <img id="gambar-utama" src="{{ $product->images->first()->url }}" alt="{{ $product->name }}"
                         class="w-full rounded border object-cover">

                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach ($product->images as $image)
                            <img src="{{ $image->url }}" alt="{{ $product->name }}"
                                 loading="lazy"
                                 onclick="document.getElementById('gambar-utama').src = this.src"
                                 class="h-16 w-full cursor-pointer rounded border object-cover">
                        @endforeach
                    </div>
                @else
                    <div class="flex h-56 items-center justify-center rounded border bg-gray-100 text-sm text-gray-400">
                        Belum ada gambar
                    </div>
                @endif
            </div>

            <div>
                <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $product->sku }}</p>

                <p class="mt-4 text-2xl font-semibold text-blue-700">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </p>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <dt class="text-gray-500">Kategori</dt>
                        <dd class="font-medium">{{ $product->category->name }}</dd>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <dt class="text-gray-500">Stok</dt>
                        <dd class="font-medium">{{ $product->stock }}</dd>
                    </div>
                    @if (auth()->user()?->isAdmin())
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Ditambahkan</dt>
                        <dd class="font-medium">{{ $product->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                {{-- ini masih tes --}}

                @auth
                    @if (auth()->user()->isPembeli())
                        <div class="mt-5 rounded border bg-gray-50 p-4">
                            @if ($product->stock < 1)
                                <p class="text-sm font-medium text-red-600">Stok lagi habis.</p>
                            @else
                                <form action="{{ route('keranjang.tambah', $product) }}" method="POST"
                                      class="flex flex-wrap items-end gap-3">
                                    @csrf

                                    <div>
                                        <label for="qty" class="block text-sm font-medium">Jumlah</label>
                                        <input type="number" id="qty" name="qty" value="{{ old('qty', 1) }}"
                                               required min="1" max="{{ min(100, $product->stock) }}" step="1"
                                               class="mt-1 w-24 rounded border px-3 py-2 @error('qty') border-red-500 @enderror">
                                    </div>

                                    <button type="submit"
                                            class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                        Masukkan keranjang
                                    </button>
                                </form>

                                <p class="mt-1 text-sm text-red-600">@error('qty'){{ $message }}@enderror</p>
                            @endif
                        </div>
                    @endif
                @endauth

                @guest
                    <div class="mt-5 rounded border bg-gray-50 p-4 text-sm text-gray-600">
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">Masuk</a>
                        atau
                        <a href="{{ route('daftar') }}" class="font-medium text-blue-600 hover:underline">daftar</a>
                        dulu buat memasukkan produk ini ke keranjang.
                    </div>
                @endguest
                {{-- xxxxxxxxxxx --}}

                <div class="mt-5">
                    <h2 class="text-sm font-semibold">Deskripsi</h2>
                    <p class="mt-1 whitespace-pre-line text-sm {{ $product->description ? 'text-gray-700' : 'italic text-gray-400' }}">
                        {{ $product->description ?: 'Belum ada deskripsi.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
