@extends('layouts.app')

@section('judul', 'Kategori')

@section('konten')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">Kategori</h1>

        @if (auth()->user()?->isAdmin())
            <a href="{{ route('categories.create') }}"
               class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Tambah Kategori
            </a>
        @endif
    </div>

    <form action="{{ route('categories.index') }}" method="GET" class="mb-4 flex gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama kategori..." maxlength="100"
               class="w-full max-w-sm rounded border px-3 py-2 text-sm">
        <button class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Cari</button>

        @if ($q !== '')
            <a href="{{ route('categories.index') }}"
               class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif
    </form>

    @if (auth()->user()?->isAdmin())
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Jumlah Produk</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $category->name }}</td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ Str::limit($category->description ?? '', 70) ?: '-' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs">
                                    {{ $category->products_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">Ubah</a>

                                    <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                          onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        <button class="inline-flex items-center rounded-md border border-orange-200 bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-700 hover:bg-orange-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                @if ($q !== '')
                                    Gak ada kategori yang cocok dengan "{{ $q }}".
                                @else
                                    Belum ada kategori.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        @forelse ($categories as $category)
        @empty
            <p class="rounded-lg bg-white p-10 text-center text-sm text-gray-500 shadow">
                @if ($q !== '')
                    Gak ada kategori yang cocok dengan "{{ $q }}".
                @else
                    Belum ada kategori.
                @endif
            </p>
        @endforelse

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($categories as $category)
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="font-medium text-gray-800">{{ $category->name }}</div>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ Str::limit($category->description ?? '', 70) ?: '-' }}
                    </p>

                    <span class="mt-2 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                        {{ $category->active_products_count }} produk
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
@endsection
