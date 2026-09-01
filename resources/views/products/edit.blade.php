@extends('layouts.app')

@section('judul', 'Ubah Produk')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-bold">Ubah Produk</h1>
            <a href="{{ route('products.show', $product) }}" class="text-sm text-blue-600 hover:underline">
                Lihat detail
            </a>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <form action="{{ route('products.update', $product) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label for="name" class="block text-sm font-medium">
                            Nama produk <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                               required minlength="2" maxlength="150" autofocus
                               class="mt-1 w-full rounded border px-3 py-2 @error('name') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('name'){{ $message }}@enderror</p>
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-medium">
                            SKU / kode produk <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                               required maxlength="50"
                               pattern="[A-Za-z0-9_\-]+"
                               title="SKU cuma boleh huruf, angka, - dan _ (tanpa spasi)."
                               placeholder="ELK-001"
                               oninput="this.value = this.value.toUpperCase()"
                               class="mt-1 w-full rounded border px-3 py-2 @error('sku') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('sku'){{ $message }}@enderror</p>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium">
                            Kategori <span class="text-red-600">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                                class="mt-1 w-full rounded border bg-white px-3 py-2 @error('category_id') border-red-500 @enderror">
                            <option value="">-- pilih kategori --</option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-red-600">@error('category_id'){{ $message }}@enderror</p>
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium">
                            Harga (Rp) <span class="text-red-600">*</span>
                        </label>
                        <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}"
                               required min="0" max="99999999" step="0.01"
                               class="mt-1 w-full rounded border px-3 py-2 @error('price') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('price'){{ $message }}@enderror</p>
                    </div>

                    <div>
                        <label for="stock" class="block text-sm font-medium">
                            Stok <span class="text-red-600">*</span>
                        </label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}"
                               required min="0" max="1000000" step="1"
                               class="mt-1 w-full rounded border px-3 py-2 @error('stock') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('stock'){{ $message }}@enderror</p>
                    </div>

                    <div class="flex items-end">
                        <div>
                            <input type="hidden" name="is_active" value="0">

                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_active" value="1"
                                       @checked(old('is_active', $product->is_active))
                                       class="h-4 w-4">
                                Produk aktif (tampil di etalase)
                            </label>
                            <p class="mt-1 text-sm text-red-600">@error('is_active'){{ $message }}@enderror</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000"
                              class="mt-1 w-full rounded border px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                    <p class="mt-1 text-sm text-red-600">@error('description'){{ $message }}@enderror</p>
                </div>

                <div class="mt-4 rounded border border-dashed bg-gray-50 p-4">
                    <label for="images" class="block text-sm font-medium">
                        Tambah gambar baru
                    </label>

                    <input type="file" id="images" name="images[]"
                           multiple
                           accept="image/jpeg,image/png,image/webp"
                           class="mt-2 block w-full text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700">

                    <p class="mt-2 text-xs text-gray-600">
                        Boleh pilih lebih dari satu file sekaligus (tahan <kbd>Ctrl</kbd> waktu memilih).
                        Maksimal <strong>5 gambar per produk</strong>, tiap file maksimal
                        <strong>2 MB</strong>, format jpg / jpeg / png / webp.

                        Produk ini sudah punya <strong>{{ $product->images->count() }}</strong> gambar,
                        sisa <strong>{{ max(0, 5 - $product->images->count()) }}</strong> slot lagi.
                        Gambar lama gak hilang - yang ini ditambahkan.
                    </p>

                    <p class="mt-1 text-sm text-red-600">@error('images'){{ $message }}@enderror</p>

                    @foreach ($errors->get('images.*') as $pesan)
                        <p class="mt-1 text-sm text-red-600">{{ $pesan[0] }}</p>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Simpan perubahan
                    </button>
                    <a href="{{ route('products.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h2 class="font-semibold">
                Gambar tersimpan
                <span class="ml-1 rounded bg-gray-100 px-2 py-0.5 text-xs">{{ $product->images->count() }}</span>
            </h2>

            @if ($product->images->isEmpty())
                <p class="mt-3 text-sm text-gray-500">Produk ini belum punya gambar.</p>
            @else
                <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach ($product->images as $image)
                        <div class="rounded border p-2 {{ $image->is_primary ? 'border-green-500 ring-1 ring-green-500' : '' }}">
                            <img src="{{ $image->url }}"
                                 alt="Gambar {{ $product->name }}"
                                 loading="lazy"
                                 class="h-28 w-full rounded object-cover">

                            @if ($image->is_primary)
                                <p class="mt-2 text-center text-xs font-semibold text-green-700">Gambar utama</p>
                            @else
                                <form action="{{ route('products.images.primary', [$product, $image]) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button class="w-full rounded border px-2 py-1 text-xs hover:bg-gray-50">
                                        Jadikan utama
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('products.images.destroy', [$product, $image]) }}" method="POST"
                                  class="mt-1"
                                  onsubmit="return confirm('Hapus gambar ini? File-nya ikut terhapus permanen.')">
                                @csrf
                                <button class="w-full rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
