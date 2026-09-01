@extends('layouts.app')

@section('judul', 'Ubah Kategori')

@section('konten')
    <div class="mx-auto max-w-xl">
        <h1 class="mb-4 text-xl font-bold">Ubah Kategori</h1>

        <div class="rounded-lg bg-white p-6 shadow">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium">
                        Nama kategori <span class="text-red-600">*</span>
                    </label>

                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                           required minlength="2" maxlength="100" autofocus
                           class="mt-1 w-full rounded border px-3 py-2 @error('name') border-red-500 @enderror">

                    <p class="mt-1 text-sm text-red-600">@error('name'){{ $message }}@enderror</p>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium">Deskripsi</label>

                    <textarea id="description" name="description" rows="3" maxlength="500"
                              class="mt-1 w-full rounded border px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description', $category->description) }}</textarea>

                    <p class="mt-1 text-sm text-red-600">@error('description'){{ $message }}@enderror</p>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Simpan perubahan
                    </button>
                    <a href="{{ route('categories.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
