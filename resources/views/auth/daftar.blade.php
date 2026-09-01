{{-- ini masih tes --}}
@extends('layouts.app')

@section('judul', 'Daftar')

@section('konten')
    <div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
        <h1 class="mb-1 text-xl font-bold">Daftar Akun</h1>
        <p class="mb-4 text-sm text-gray-600">Gratis, buat belanja di toko ini.</p>

        <form action="{{ route('daftar') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="block text-sm font-medium">
                    Nama <span class="text-red-600">*</span>
                </label>

                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       required minlength="2" maxlength="100" autofocus autocomplete="name"
                       class="mt-1 w-full rounded border px-3 py-2 @error('name') border-red-500 @enderror">

                <p class="mt-1 text-sm text-red-600">@error('name'){{ $message }}@enderror</p>
            </div>

            <div class="mb-3">
                <label for="email" class="block text-sm font-medium">
                    Email <span class="text-red-600">*</span>
                </label>

                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required maxlength="150" autocomplete="username"
                       class="mt-1 w-full rounded border px-3 py-2 @error('email') border-red-500 @enderror">

                <p class="mt-1 text-sm text-red-600">@error('email'){{ $message }}@enderror</p>
            </div>

            <div class="mb-3">
                <label for="password" class="block text-sm font-medium">
                    Password <span class="text-red-600">*</span>
                </label>

                <input type="password" id="password" name="password"
                       required minlength="8" maxlength="100" autocomplete="new-password"
                       class="mt-1 w-full rounded border px-3 py-2 @error('password') border-red-500 @enderror">

                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
                <p class="mt-1 text-sm text-red-600">@error('password'){{ $message }}@enderror</p>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium">
                    Ulangi password <span class="text-red-600">*</span>
                </label>

                <input type="password" id="password_confirmation" name="password_confirmation"
                       required minlength="8" maxlength="100" autocomplete="new-password"
                       class="mt-1 w-full rounded border px-3 py-2">
            </div>

            <button type="submit"
                    class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                Daftar
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk di sini</a>
        </p>

        <div class="mt-4 rounded bg-gray-50 p-3 text-xs text-gray-600">
            Akun yang dibuat lewat halaman ini selalu jadi <strong>pembeli</strong>.
            Akun pengelola toko gak bisa dibikin dari sini.
        </div>
    </div>
@endsection
{{-- xxxxxxxxxxx --}}
