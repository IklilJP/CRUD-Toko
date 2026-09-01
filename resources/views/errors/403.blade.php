@extends('layouts.app')

@section('judul', 'Akses ditolak')

@section('konten')
    <div class="mx-auto max-w-md rounded-lg bg-white p-8 text-center shadow">
        <p class="text-5xl font-bold text-red-600">403</p>
        <h1 class="mt-3 text-lg font-semibold">Akses ditolak</h1>
        <p class="mt-2 text-sm text-gray-600">
            {{ $exception->getMessage() ?: 'Kamu gak punya izin buka halaman ini.' }}
        </p>
        <a href="{{ route('products.index') }}"
           class="mt-5 inline-block rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Balik ke daftar produk
        </a>
    </div>
@endsection
