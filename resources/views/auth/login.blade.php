@extends('layouts.app')

@section('judul', 'Masuk')

@section('konten')
    <div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
        <h1 class="mb-4 text-xl font-bold">Masuk</h1>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="block text-sm font-medium">Email</label>

                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username"
                       class="mt-1 w-full rounded border px-3 py-2 @error('email') border-red-500 @enderror">

                <p class="mt-1 text-sm text-red-600">@error('email'){{ $message }}@enderror</p>
            </div>

            <div class="mb-3">
                <label for="password" class="block text-sm font-medium">Password</label>

                <input type="password" id="password" name="password"
                       required autocomplete="current-password"
                       class="mt-1 w-full rounded border px-3 py-2 @error('password') border-red-500 @enderror">
                <p class="mt-1 text-sm text-red-600">@error('password'){{ $message }}@enderror</p>
            </div>

            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                Masuk
            </button>
        </form>

        {{-- ini masih tes --}}
        <p class="mt-4 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="{{ route('daftar') }}" class="text-blue-600 hover:underline">Daftar dulu</a>
        </p>

        <p class="mt-2 text-center text-sm text-gray-600">
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">&larr; Balik lihat produk</a>
        </p>

        <div class="mt-4 rounded bg-gray-50 p-3 text-xs text-gray-600">
            Akun dari seeder:<br>
            admin@toko.test / password &nbsp;(pengelola)<br>
            user@toko.test / password &nbsp;(pembeli)
        </div>
        {{-- xxxxxxxxxxx --}}
    </div>
@endsection
