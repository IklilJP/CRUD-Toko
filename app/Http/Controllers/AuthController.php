<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $kredensial = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email belum benar.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($kredensial)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('products.index'))
            ->with('sukses', 'Login berhasil ');
    }

    // ini masih tes //
    public function showRegister()
    {
        return view('auth.daftar');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'name.min'       => 'Nama minimal 2 karakter.',
            'name.max'       => 'Nama maksimal 100 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email belum benar.',
            'email.unique'   => 'Email itu sudah dipakai akun lain. Coba masuk aja.',

            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Ulangi password belum sama dengan password di atas.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('products.index')
            ->with('sukses', "Akun {$user->name} berhasil dibuat. Selamat belanja!");
    }
    //xxxxxx//

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Kamu sudah keluar.');
    }
}
