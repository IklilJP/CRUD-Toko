@extends('layouts.app')

@section('judul', 'Tambah Produk')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <h1 class="mb-4 text-xl font-bold">Tambah Produk</h1>

        <div class="rounded-lg bg-white p-6 shadow">
            <form action="{{ route('products.store') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label for="name" class="block text-sm font-medium">
                            Nama produk <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               required minlength="2" maxlength="150" autofocus
                               class="mt-1 w-full rounded border px-3 py-2 @error('name') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('name'){{ $message }}@enderror</p>
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-medium">
                            SKU / kode produk <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku') }}"
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
                                    @selected(old('category_id') == $category->id)>
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
                        <input type="number" id="price" name="price" value="{{ old('price') }}"
                               required min="0" max="99999999" step="0.01"
                               class="mt-1 w-full rounded border px-3 py-2 @error('price') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('price'){{ $message }}@enderror</p>
                    </div>

                    <div>
                        <label for="stock" class="block text-sm font-medium">
                            Stok <span class="text-red-600">*</span>
                        </label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}"
                               required min="0" max="1000000" step="1"
                               class="mt-1 w-full rounded border px-3 py-2 @error('stock') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-red-600">@error('stock'){{ $message }}@enderror</p>
                    </div>

                    <div class="flex items-end">
                        <div>
                            <input type="hidden" name="is_active" value="0">

                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_active" value="1"
                                       @checked(old('is_active', true))
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
                              class="mt-1 w-full rounded border px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-red-600">@error('description'){{ $message }}@enderror</p>
                </div>

                <div class="mt-4 rounded border border-dashed bg-gray-50 p-4">
                    <label for="images" class="block text-sm font-medium">
                        Gambar produk
                    </label>

                    <input type="file" id="images" name="images[]"
                           multiple
                           accept="image/jpeg,image/png,image/webp"
                           class="mt-2 block w-full text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700">

                    <p class="mt-2 text-xs text-gray-600">
                        Boleh pilih lebih dari satu file sekaligus (tahan <kbd>Ctrl</kbd> waktu memilih).
                        Maksimal <strong>5 gambar per produk</strong>, tiap file maksimal
                        <strong>2 MB</strong>, format jpg / jpeg / png / webp.
                    </p>

                    <p class="mt-1 text-sm text-red-600">@error('images'){{ $message }}@enderror</p>

                    @foreach ($errors->get('images.*') as $pesan)
                        <p class="mt-1 text-sm text-red-600">{{ $pesan[0] }}</p>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Simpan produk
                    </button>
                    <a href="{{ route('products.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
